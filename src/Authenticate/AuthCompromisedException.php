<?php
 
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Authenticate;

use UserFrosting\Support\Exception\ForbiddenException;

/**
 * Compromised authentication exception.  Used when we suspect theft of the rememberMe cookie.
 *
 * @author Alexander Weissman
 */
class AuthCompromisedException extends ForbiddenException
{
    protected $default_message = 'Someone else has used your login information to acccess this page!';
}
