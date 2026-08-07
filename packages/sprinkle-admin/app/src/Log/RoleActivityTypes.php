<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Log;

enum RoleActivityTypes: string
{
    case CREATE = 'role_create';
    case DELETE = 'role_delete';
    case UPDATE_INFO = 'role_update_info';
    case UPDATE_FIELD = 'role_update_field';
}
