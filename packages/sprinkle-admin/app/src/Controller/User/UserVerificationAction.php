<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\Admin\Controller\User;

use Illuminate\Database\Connection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Log\ActivityRecorderInterface;
use UserFrosting\Sprinkle\Admin\Log\AdminAccountActivityTypes;
use UserFrosting\Support\Message\UserMessage;

/**
 * Verifies or un verifies an existing user account.
 */
class UserVerificationAction extends UserUpdateAction
{
    protected string $schema = 'schema://requests/user/verification.yaml';

    public function __construct(
        Translator $translator,
        Authenticator $authenticator,
        Config $config,
        protected Connection $db,
        protected ActivityRecorderInterface $logger,
        RequestDataTransformer $transformer,
        ServerSideValidator $validator,
    ) {
        parent::__construct($translator, $authenticator, $config, $transformer, $validator);
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param UserInterface $user
     * @param Request       $request
     * @param Response      $response
     *
     * @return Response
     */
    public function __invoke(UserInterface $user, Request $request, Response $response): Response
    {
        $user = $this->handle($user, $request);

        $message = new UserMessage(
            $user->flag_verified === true ? 'MANUALLY_ACTIVATED' : 'DETAILS_UPDATED',
            ['user_name' => $user->user_name]
        );

        return $this->respond($response, $message);
    }

    /**
     * Handle the request.
     *
     * @param UserInterface $user
     * @param Request       $request
     *
     * @return UserInterface
     */
    protected function handle(UserInterface $user, Request $request): UserInterface
    {
        $currentUser = $this->authorize($user, 'update_user_field');
        $data = $this->transform($this->getSchema(), $request);
        $verified = $data['flag_verified'];

        $this->db->transaction(function () use ($user, $currentUser, $verified): void {
            $user->flag_verified = $verified;
            $this->logger->record(
                user: $currentUser,
                type: $verified === '1'
                    ? AdminAccountActivityTypes::VERIFY
                    : AdminAccountActivityTypes::UNVERIFY,
                subject: $user,
            );
            $user->save();
        });

        return $user;
    }
}
