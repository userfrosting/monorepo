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
use UserFrosting\Sprinkle\Account\Exceptions\AccountException;
use UserFrosting\Sprinkle\Account\Log\ActivityRecorderInterface;
use UserFrosting\Sprinkle\Admin\Log\AdminAccountActivityTypes;
use UserFrosting\Support\Message\UserMessage;

/**
 * Enables or disables an existing user account.
 */
class UserStatusAction extends UserUpdateAction
{
    protected string $schema = 'schema://requests/user/status.yaml';

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

    public function __invoke(UserInterface $user, Request $request, Response $response): Response
    {
        $currentUser = $this->authorize($user, 'update_user_field');
        $data = $this->transform($this->getSchema(), $request);
        $enabled = $data['flag_enabled'];

        if ($user->id === $this->config->getInt('reserved_user_ids.master') && $enabled === '0') {
            $e = new AccountException();
            $e->setTitle('DISABLE_MASTER');

            throw $e;
        }

        if ($user->id === $currentUser->id && $enabled === '0') {
            $e = new AccountException();
            $e->setTitle('DISABLE_SELF');

            throw $e;
        }

        $this->db->transaction(function () use ($user, $currentUser, $enabled): void {
            $user->flag_enabled = $enabled;
            $this->logger->record(
                user: $currentUser,
                type: $enabled === '1'
                    ? AdminAccountActivityTypes::ENABLE
                    : AdminAccountActivityTypes::DISABLE,
                subject: $user,
            );
            $user->save();
        });

        $message = new UserMessage(
            $enabled === '1' ? 'ENABLE_SUCCESSFUL' : 'DISABLE_SUCCESSFUL',
            ['user_name' => $user->user_name]
        );

        return $this->respond($response, $message);
    }
}
