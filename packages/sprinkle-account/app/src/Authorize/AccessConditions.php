<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authorize;

use ArrayAccess;
use Exception;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\RoleUsers;
use UserFrosting\Sprinkle\Account\Database\Models\User;

/**
 * Default access condition callbacks.
 */
class AccessConditions implements AccessConditionsInterface
{
    protected UserInterface $user;

    public function __construct(
        protected Config $config,
        ?UserInterface $user = null,
    ) {
        $this->user = $user ?? new User();
    }

    /**
     * Unconditionally grant permission - use carefully!
     *
     * @return bool Returns true no matter what.
     */
    public function always(): bool
    {
        return true;
    }

    /**
     * Unconditionally deny permission.
     *
     * @return bool Returns false no matter what.
     */
    public function never(): bool
    {
        return false;
    }

    /**
     * Check if the specified values are identical to one another (strict comparison).
     *
     * @param mixed $val1 the first value to compare.
     * @param mixed $val2 the second value to compare.
     *
     * @return bool Return true if the values are strictly equal, false otherwise.
     */
    public function equals(mixed $val1, mixed $val2): bool
    {
        return $val1 === $val2;
    }

    /**
     * Check if the specified values are numeric, and if so, if they are equal to each other.
     *
     * @param mixed $val1 the first value to compare.
     * @param mixed $val2 the second value to compare.
     *
     * @return bool true if the values are numeric and equal, false otherwise.
     */
    public function equals_num(mixed $val1, mixed $val2): bool
    {
        if (!is_numeric($val1) || !is_numeric($val2)) {
            return false;
        }

        return $val1 === $val2;
    }

    /**
     * Check if the specified user (or user id) has a particular role (or role id).
     *
     * @param int|UserInterface $user The id or object of the user.
     * @param int|RoleInterface $role The id or object of the role.
     *
     * @return bool true if the user has the role, false otherwise.
     */
    public function has_role(int|UserInterface $user, int|RoleInterface $role): bool
    {
        $user_id = ($user instanceof UserInterface) ? $user->id : $user;
        $role_id = ($role instanceof RoleInterface) ? $role->id : $role;

        $count = RoleUsers::where('user_id', $user_id)
                          ->where('role_id', $role_id)
                          ->count();

        return $count > 0;
    }

    /**
     * Check if the specified value $needle is in the values of $haystack.
     *
     * @param mixed   $needle   the value to look for in $haystack
     * @param mixed[] $haystack the array of values to search.
     *
     * @return bool true if $needle is present in the values of $haystack, false otherwise.
     */
    public function in(mixed $needle, array $haystack): bool
    {
        return in_array($needle, $haystack, true);
    }

    /**
     * Check if the specified user (or user_id) is in a particular group (or role id).
     *
     * @param int|UserInterface  $user  The id or object of the user.
     * @param int|GroupInterface $group The id or object of the group.
     *
     * @return bool true if the user is in the group, false otherwise.
     */
    public function in_group(int|UserInterface $user, int|GroupInterface $group): bool
    {
        /** @var User|null */
        $user = ($user instanceof UserInterface) ? $user : $this->user::find($user);
        $group_id = ($group instanceof GroupInterface) ? $group->id : $group;

        // No user found, no role.
        if ($user === null) {
            return false;
        }

        return $user->group_id === $group_id;
    }

    /**
     * Check if the specified user (by user_id) is the master user.
     *
     * @param int|UserInterface $user the id or object of the user.
     *
     * @return bool true if the user id is equal to the id of the master account, false otherwise.
     */
    public function is_master(int|UserInterface $user): bool
    {
        $user_id = ($user instanceof UserInterface) ? $user->id : $user;

        // Need to use loose comparison for now, because some DBs return `id` as a string
        return $user_id === $this->config->get('reserved_user_ids.master');
    }

    /**
     * Check if all values in the array $needle are present in the values of $haystack.
     *
     * @param mixed[] $needle   the array whose values we should look for in $haystack
     * @param mixed[] $haystack the array of values to search.
     *
     * @return bool true if every value in $needle is present in the values of $haystack, false otherwise.
     */
    public function subset(array $needle, array $haystack): bool
    {
        return count($needle) === count(array_intersect($needle, $haystack));
    }

    /**
     * Check if all keys of the array $needle are present in the values of $haystack.
     *
     * This function is useful for whitelisting an array of key-value parameters.
     *
     * @param mixed[] $needle   the array whose keys we should look for in $haystack
     * @param mixed[] $haystack the array of values to search.
     *
     * @return bool true if every key in $needle is present in the values of $haystack, false otherwise.
     */
    public function subset_keys(array $needle, array $haystack): bool
    {
        return count($needle) === count(array_intersect(array_keys($needle), $haystack));
    }

    /**
     * Can't set a new condition as part of ArrayAccess, so an exception is thrown.
     *
     * @param string|null $condition
     * @param callable    $callback
     *
     * @throws Exception Since this is not implemented.
     */
    // TODO : This should be enabled, as it would be easier to decorate the
    //        service than replacing it. See commented test in
    //        `AccessConditionEvaluatorTest`
    public function offsetSet($condition, $callback): void
    {
        throw new Exception("Can't set new condition on this object.");
    }

    /**
     * Returns true if the condition exists.
     *
     * @param string $condition
     *
     * @return bool
     */
    public function offsetExists($condition): bool
    {
        return method_exists($this, $condition);
    }

    /**
     * Can't unset condition as part of ArrayAccess, so an exception is thrown.
     *
     * @param string $condition
     *
     * @throws Exception Since this is not implemented.
     */
    public function offsetUnset($condition): void
    {
        throw new Exception("Can't unset new condition on this object.");
    }

    /**
     * Returns the condition callback.
     *
     * @param string $condition
     *
     * @return callable
     */
    public function offsetGet($condition): callable
    {
        // @phpstan-ignore-next-line Phpstan doesn't understand array is callable.
        return [$this, $condition];
    }
}
