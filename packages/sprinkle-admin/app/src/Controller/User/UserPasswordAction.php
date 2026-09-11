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
use UserFrosting\Sprinkle\Account\Log\AccountActivityTypes;
use UserFrosting\Sprinkle\Account\Log\ActivityRecorderInterface;
use UserFrosting\Support\Message\UserMessage;

/**
 * Changes an existing user's password.
 */
class UserPasswordAction extends UserUpdateAction
{
    protected string $schema = 'schema://requests/user/edit-password.yaml';

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
        $schema = $this->getSchema();
        $schema->set('password.validators.length.min', $this->config->get('site.password.length.min'));
        $schema->set('password.validators.length.max', $this->config->get('site.password.length.max'));
        $schema->set('passwordc.validators.length.min', $this->config->get('site.password.length.min'));
        $schema->set('passwordc.validators.length.max', $this->config->get('site.password.length.max'));
        $data = $this->transform($schema, $request);

        $this->db->transaction(function () use ($user, $currentUser, $data): void {
            $user->password = $data['password'];
            $this->logger->record(
                user: $currentUser,
                type: AccountActivityTypes::UPDATE_PASSWORD,
                subject: $user,
                withProperties: false,
            );
            $user->save();
        });

        return $this->respond(
            $response,
            new UserMessage('DETAILS_UPDATED', ['user_name' => $user->user_name])
        );
    }
}
