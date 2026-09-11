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
 * Assigns or removes a user's group.
 */
class UserGroupAction extends UserUpdateAction
{
    protected string $schema = 'schema://requests/user/group.yaml';

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
        $groupId = $data['group_id'] === 0 ? null : $data['group_id'];

        $this->db->transaction(function () use ($user, $currentUser, $groupId): void {
            // Skip if no changes
            if ($user->group_id !== $groupId) {
                $oldGroup = $user->group;

                // Update group
                $user->group_id = $groupId;
                $user->unsetRelation('group');
                $newGroup = $user->group;

                if ($oldGroup !== null) {
                    $this->logger->record(
                        user: $currentUser,
                        type: AdminAccountActivityTypes::REMOVE_FROM_GROUP,
                        context: $oldGroup,
                        subject: $user,
                        withProperties: false,
                    );
                }

                if ($newGroup !== null) {
                    $this->logger->record(
                        user: $currentUser,
                        type: AdminAccountActivityTypes::ADD_TO_GROUP,
                        context: $newGroup,
                        subject: $user,
                        withProperties: false,
                    );
                }
            }

            $user->save();
        });

        return $this->respond(
            $response,
            new UserMessage('DETAILS_UPDATED', ['user_name' => $user->user_name])
        );
    }
}
