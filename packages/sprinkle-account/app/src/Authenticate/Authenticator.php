<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate;

use Birke\Rememberme\Authenticator as RememberMe;
use Birke\Rememberme\Storage\StorageInterface;
use Birke\Rememberme\Triplet as RememberMeTriplet;
use Illuminate\Cache\Repository as Cache;
use Psr\EventDispatcher\EventDispatcherInterface;
use UserFrosting\Config\Config;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Event\UserAuthenticatedEvent;
use UserFrosting\Sprinkle\Account\Event\UserLoggedInEvent;
use UserFrosting\Sprinkle\Account\Event\UserLoggedOutEvent;
use UserFrosting\Sprinkle\Account\Event\UserValidatedEvent;
use UserFrosting\Sprinkle\Account\Exceptions\AccountDisabledException;
use UserFrosting\Sprinkle\Account\Exceptions\AccountException;
use UserFrosting\Sprinkle\Account\Exceptions\AccountInvalidException;
use UserFrosting\Sprinkle\Account\Exceptions\AccountNotFoundException;
use UserFrosting\Sprinkle\Account\Exceptions\AccountNotVerifiedException;
use UserFrosting\Sprinkle\Account\Exceptions\AuthCompromisedException;
use UserFrosting\Sprinkle\Account\Exceptions\AuthExpiredException;
use UserFrosting\Sprinkle\Account\Exceptions\InvalidCredentialsException;
use UserFrosting\Sprinkle\Account\Exceptions\PasswordExpiredException;
use UserFrosting\Sprinkle\Account\Helpers\DynamicUserModel;
use UserFrosting\Sprinkle\Core\Csrf\CsrfGuard;

/**
 * Handles authentication tasks.
 */
class Authenticator
{
    use DynamicUserModel;

    /**
     * @var UserInterface|null The actual User object
     */
    protected ?UserInterface $user = null;

    /**
     * @var bool Indicates if the user was authenticated via a rememberMe cookie.
     */
    protected bool $viaRemember = false;

    /**
     * Create a new Authenticator object.
     *
     * @param Cache                               $cache
     * @param Config                              $config
     * @param \UserFrosting\Event\EventDispatcher $eventDispatcher
     * @param RememberMe                          $rememberMe
     * @param Session                             $session
     * @param StorageInterface                    $rememberMeStorage
     * @param UserInterface                       $userModel
     */
    public function __construct(
        protected AuthorizationManager $authorizationManager,
        protected Cache $cache,
        protected Config $config,
        protected EventDispatcherInterface $eventDispatcher,
        protected RememberMe $rememberMe,
        protected Session $session,
        protected StorageInterface $rememberMeStorage,
        protected UserInterface $userModel,
        protected CsrfGuard $csrf,
    ) {
        $this->setupCookie();
    }

    /**
     * Attempts to authenticate a user based on a supplied identity and password.
     *
     * @param string $identityColumn
     * @param string $identityValue
     * @param string $password
     *
     * @throws AccountException On invalid credentials.
     *
     * @return UserInterface
     */
    public function authenticate(
        string $identityColumn,
        string|int $identityValue,
        string $password,
    ): UserInterface {
        // Try to load the user, using the specified conditions
        /** @var UserInterface|null */
        $user = $this->userModel::where($identityColumn, $identityValue)->first();

        if ($user === null) {
            throw new AccountNotFoundException();
        }

        // Validate the user. Will throw exception on error.
        $user = $this->validateUserAccount($user);

        // Here is my password.  May I please assume the identify of this user now?
        // We know the password is at fault here (as opposed to the identity),
        // but lets not give away the combination in case of someone bruteforcing
        if (!$user->comparePassword($password)) {
            throw new InvalidCredentialsException();
        }

        // If the password is expired, throw exception
        // This check is intentionally placed here instead of in
        // validateUserAccount(), so that the exception is thrown only after
        // the password has been successfully validated.
        if ($user->isPasswordExpired()) {
            throw new PasswordExpiredException();
        }

        // Dispatch event. Listeners can throw exception to stop authentication
        $event = new UserAuthenticatedEvent($user, $identityColumn, $identityValue, $password);
        $event = $this->eventDispatcher->dispatch($event);

        return $event->user;
    }

    /**
     * Attempts to authenticate a user based on a supplied identity and password.
     * If successful, the user's id is stored in session and the user will be "logged in".
     *
     * @param string $identityColumn
     * @param string $identityValue
     * @param string $password
     *
     * @throws AccountException On invalid credentials.
     *
     * @return UserInterface
     */
    public function attempt(
        string $identityColumn,
        string|int $identityValue,
        string $password,
        bool $rememberMe = false,
    ): UserInterface {
        $user = $this->authenticate($identityColumn, $identityValue, $password);
        $this->login($user, $rememberMe);

        return $user;
    }

    /**
     * Process an account login request.
     *
     * This method logs in the specified user, allowing the client to assume the user's identity for the duration of the session.
     *
     * @param UserInterface $user       The user to log in.
     * @param bool          $rememberMe Set to true to make this a "persistent session", i.e. one that will re-login even after the session expires.
     */
    public function login(
        UserInterface $user,
        bool $rememberMe = false
    ): void {
        // Since regenerateId deletes the old session, we'll do the same in cache
        if (($oldId = $this->session->getId()) !== false) {
            $this->flushSessionCache($oldId);
        }

        // Get new session ID
        $this->session->regenerateId(true);

        // If the user wants to be remembered, create Rememberme cookie
        if ($rememberMe) {
            $this->rememberMe->createCookie($user->id);
        } else {
            $this->rememberMe->clearCookie();
        }

        // Assume identity
        $key = strval($this->config->get('session.keys.current_user_id'));
        $this->session->set($key, $user->id);
        $this->user = $user;

        // Generate new CSRF token for the new session
        $this->csrf->removeTokenFromStorage($this->csrf->getTokenName() ?? '');
        $this->csrf->generateToken();

        // Set auth mode
        $this->viaRemember = false;

        // Dispatch login event. Listeners can throw exception to interrupt login.
        $this->eventDispatcher->dispatch(new UserLoggedInEvent($user));
    }

    /**
     * Processes an account logout request for the currently active user.
     *
     * Logs the currently authenticated user out, destroying the PHP session and clearing the persistent session.
     * This can optionally remove persistent sessions across all browsers/devices, since there can be a "RememberMe" cookie
     * and corresponding database entries in multiple browsers/devices.  See http://jaspan.com/improved_persistent_login_cookie_best_practice.
     *
     * @param bool               $complete If set to true, rememberMe token will be removed from the database.
     * @param UserInterface|null $user     The user information. Used to avoid infinite loop when logout is called from user(). Should be the current user, can't be used to logout another user.
     */
    public function logout(
        bool $complete = false,
        ?UserInterface $user = null
    ): void {
        $currentUser = $user ?? $this->user();

        // No user, nothing to logout
        if ($currentUser === null) {
            return;
        }

        // This removes all of the user's persistent logins from the database
        if ($complete === true) {
            $this->rememberMeStorage->cleanAllTriplets($currentUser->id);
        }

        // Clear the rememberMe cookie
        $this->rememberMe->clearCookie();

        // User logout actions
        $currentUser->forgetCache();

        // User is no longer the active one
        $this->user = null;

        // Since regenerateId deletes the old session, we'll do the same in cache
        if (($oldId = $this->session->getId()) !== false) {
            $this->flushSessionCache($oldId);
        }

        // Completely destroy the session and restart the session, making sure
        // to regenerate the session id to use for the remaining execution.
        $this->session->destroy();
        $this->session->start();
        $this->session->regenerateId(true);

        // Generate new CSRF token for the new session
        $storage = null;
        $this->csrf->setStorage($storage);
        $this->csrf->generateToken();

        // Dispatch logged out event.
        $this->eventDispatcher->dispatch(new UserLoggedOutEvent($currentUser));
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return !$this->guest();
    }

    /**
     * Determine if the current user is a guest (unauthenticated).
     *
     * @return bool
     */
    public function guest(): bool
    {
        return is_null($this->user());
    }

    /**
     * Try to get the currently authenticated user, returning a null user if none was found.
     *
     * Tries to re-establish a session for "remember-me" users who have been logged out due to an expired session.
     *
     * @throws AccountException If any error is encountered.
     *
     * @return UserInterface|null
     */
    public function user(): ?UserInterface
    {
        // Return any cached user
        if (!is_null($this->user)) {
            return $this->user;
        }

        // If this throws a PDOException we catch it and return null than allowing the exception to propagate.
        // This is because the error handler relies on Twig, which relies on a Twig Extension, which relies on the global current_user variable.
        // So, we really don't want this method to throw any database exceptions.
        try {
            // Now, check to see if we have a user in session
            // If no user was found in the session, try to login via RememberMe cookie
            $this->user = $this->loginSessionUser() ?? $this->loginRememberedUser();
        } catch (\PDOException $e) { // @phpstan-ignore-line False negative. Can be thrown by Model.
            $this->user = null;
        }

        // TODO : Event dispatcher might be required here to add custom method, like JWT.

        return $this->user;
    }

    /**
     * Determine whether the current user was authenticated using a remember me cookie.
     *
     * This function is useful when users are performing sensitive operations, and you may want to force them to re-authenticate.
     *
     * @return bool
     */
    public function viaRemember(): bool
    {
        return $this->viaRemember;
    }

    /**
     * Flush the cache associated with a session id.
     *
     * @param string $id The session id
     *
     * @return bool
     */
    public function flushSessionCache(string $id): bool
    {
        return $this->cache->tags('_s' . $id)->flush();
    }

    /**
     * Checks user has access on a particular permission slug.
     *
     * Alias for authorizationManager->checkAccess using the current user.
     *
     * @param string  $slug   The permission slug to check for access.
     * @param mixed[] $params An array of field names => values. @deprecated
     *
     * @return bool True if the user has access, false otherwise.
     */
    public function checkAccess(string $slug, array $params = []): bool
    {
        return $this->authorizationManager->checkAccess($this->user(), $slug, $params);
    }

    /**
     * Attempt to log in the client from their rememberMe token (in their cookie).
     *
     * @throws AuthCompromisedException The client attempted to log in with an invalid rememberMe token.
     *
     * @return UserInterface|null If successful, the User object of the remembered user.  Otherwise, return false.
     */
    protected function loginRememberedUser(): ?UserInterface
    {
        $loginResult = $this->rememberMe->login();

        if ($loginResult->isSuccess()) {
            // Update in session
            $key = strval($this->config->get('session.keys.current_user_id'));
            $this->session->set($key, intval($loginResult->getCredential()));
            // There is a chance that an attacker has stolen the login token,
            // so we store the fact that the user was logged in via RememberMe (instead of login form)
            $this->viaRemember = true;
        } else {
            // If $rememberMe->login() was not successful, check if the token was invalid as well. This means the cookie was stolen.
            if ($loginResult->hasPossibleManipulation()) {
                throw new AuthCompromisedException();
            }
        }

        $userId = intval($loginResult->getCredential());

        // If the user id is 0, then the user was not found.
        if ($userId === 0) {
            return null;
        }

        // Get cached user model.
        $user = $this->userModel::findCached($userId);

        // Null model, then the user was not found.
        if ($user === null) {
            return null;
        }

        $user = $this->validateUserAccount($user);

        return $user;
    }

    /**
     * Attempt to log in the client from the session.
     *
     * @throws AuthExpiredException     The client attempted to use an expired rememberMe token.
     * @throws AccountNotFoundException The "id" in session is not valid
     *
     * @return UserInterface|null If successful, the User object of the user in session.  Otherwise, return null.
     */
    protected function loginSessionUser(): ?UserInterface
    {
        // If sessions is not started, then we can't do anything.
        if ($this->session->status() === PHP_SESSION_NONE) {
            return null;
        }

        $key = strval($this->config->get('session.keys.current_user_id'));
        $userId = $this->session->get($key);

        // No user id in session, no user.
        if (!is_int($userId)) {
            return null;
        }

        // Find user model using cached data if available and validate it.
        $user = $this->userModel::findCached($userId);

        // If the user doesn't exist, throw an exception.
        // We don't want to go through "rememberMe", as "session" id might be compromised.
        if ($user === null) {
            throw new AccountNotFoundException();
        }

        // If a user_id was found in the session, check any rememberMe cookie
        // that was submitted. If they submitted an expired rememberMe cookie,
        // then we need to log them out.
        if (!$this->validateRememberMeCookie()) {
            $this->logout(user: $user);

            throw new AuthExpiredException();
        }

        $user = $this->validateUserAccount($user);

        return $user;
    }

    /**
     * Determine if the cookie contains a valid rememberMe token.
     *
     * @return bool
     */
    protected function validateRememberMeCookie(): bool
    {
        $cookieValue = $this->rememberMe->getCookie()->getValue();

        // Empty cookie means nothing to validate.
        if ($cookieValue === '') {
            return true;
        }

        $triplet = RememberMeTriplet::fromString($cookieValue);

        return $triplet->isValid();
    }

    /**
     * Checks that the account is valid and enabled, throwing an exception if not.
     *
     * @param UserInterface $user
     *
     * @throws AccountNotFoundException
     * @throws AccountInvalidException
     * @throws AccountDisabledException
     * @throws AccountNotVerifiedException
     */
    protected function validateUserAccount(UserInterface $user): UserInterface
    {
        // Check that the user has a password set (so, rule out newly created accounts without a password)
        if ($user->password === '') {
            throw new PasswordExpiredException();
        }

        // Check that the user's account is enabled
        if (!$user->flag_enabled) {
            throw new AccountDisabledException();
        }

        // Check that the user's account is verified (if verification is required)
        if ($this->requireEmailVerification() && !$user->flag_verified) {
            throw new AccountNotVerifiedException();
        }

        // Dispatch event. Listeners can throw exception to stop validation
        $event = $this->eventDispatcher->dispatch(new UserValidatedEvent($user));

        return $event->user;
    }

    /**
     * Setup \Birke\Rememberme\Cookie\PHPCookie configuration.
     */
    protected function setupCookie(): void
    {
        /** @var \Birke\Rememberme\Cookie\PHPCookie */
        $cookie = $this->rememberMe->getCookie();

        // Set cookie name
        $cookieName = $this->config->get('session.name') . '-' . $this->config->get('remember_me.cookie.name');
        $cookie->setName($cookieName);

        // Change cookie path
        $cookie->setPath(strval($this->config->get('remember_me.session.path')));

        // Set expire time, if specified
        if ($this->config->has('remember_me.expire_time') && is_int($this->config->get('remember_me.expire_time'))) {
            $cookie->setExpireTime($this->config->get('remember_me.expire_time'));
        }

        // Set domain, if specified
        if ($this->config->has('remember_me.domain') && is_string($this->config->get('remember_me.domain'))) {
            $cookie->setDomain($this->config->get('remember_me.domain'));
        }
    }

    /**
     * Is email verification required ?
     *
     * @return bool
     */
    protected function requireEmailVerification(): bool
    {
        return boolval($this->config->get('site.registration.require_email_verification'));
    }
}
