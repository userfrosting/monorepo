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
        $this->handle($user, $request);

        return $this->respond(
            $response,
            new UserMessage('DETAILS_UPDATED', ['user_name' => $user->user_name])
        );
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
        $currentUser = $this->authorize($user, 'update_user_role');
        $data = $this->transform($this->getSchema(), $request);
        $roleIds = $data['roles'];

        $this->db->transaction(function () use ($user, $currentUser, $roleIds): void {
            /** @var \Illuminate\Database\Eloquent\Collection<int, Role> $oldRoles */
            $oldRoles = $user->roles()->get();
            $oldRoles = $oldRoles->keyBy('id');

            /** @var \Illuminate\Database\Eloquent\Collection<int, Role> $newRoles */
            $newRoles = Role::query()->whereKey($roleIds)->get();
            $newRoles = $newRoles->keyBy('id');

            // Determine which roles have been added and which have been removed.
            $addedRoles = $newRoles->diffKeys($oldRoles);
            $removedRoles = $oldRoles->diffKeys($newRoles);

            $user->roles()->sync($roleIds);
            $user->forgetCache();

            foreach ($addedRoles as $role) {
                $this->logger->record(
                    user: $currentUser,
                    type: AdminAccountActivityTypes::ADD_ROLE,
                    context: $role,
                    subject: $user,
                );
            }

            foreach ($removedRoles as $role) {
                $this->logger->record(
                    user: $currentUser,
                    type: AdminAccountActivityTypes::REMOVE_ROLE,
                    context: $role,
                    subject: $user,
                );
            }
        });

        return $user;
    }
}
