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
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Log\ActivityRecorderInterface;
use UserFrosting\Sprinkle\Admin\Log\AdminAccountActivityTypes;
use UserFrosting\Support\Message\UserMessage;

/**
 * Assigns roles to an existing user.
 */
class UserRolesAction extends UserUpdateAction
{
    protected string $schema = 'schema://requests/user/roles.yaml';

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
        $currentUser = $this->authorize($user, 'update_user_role');
        $data = $this->transform($this->getSchema(), $request);
        $roleIds = $data['roles'];

        $this->db->transaction(function () use ($user, $currentUser, $roleIds): void {
            $oldRoles = $user->roles()->get()->pluck('name', 'id')->all();
            $newRoles = Role::query()->whereKey($roleIds)->pluck('name', 'id')->all();
            $addedRoles = implode(', ', array_values(array_diff_key($newRoles, $oldRoles)));
            $removedRoles = implode(', ', array_values(array_diff_key($oldRoles, $newRoles)));
            $user->roles()->sync($roleIds);
            $user->forgetCache();

            $this->logger->record(
                user: $currentUser,
                type: AdminAccountActivityTypes::UPDATE_ROLES,
                subject: $user,
                metadata: [
                    'added_roles'   => $addedRoles !== '' ? $addedRoles : $this->translator->translate('ROLE.NONE'),
                    'removed_roles' => $removedRoles !== '' ? $removedRoles : $this->translator->translate('ROLE.NONE'),
                ],
                withProperties: false,
            );
        });

        return $this->respond(
            $response,
            new UserMessage('DETAILS_UPDATED', ['user_name' => $user->user_name])
        );
    }
}
