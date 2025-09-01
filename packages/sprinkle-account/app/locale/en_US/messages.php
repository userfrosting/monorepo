<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

/**
 * US English message token translations for the 'account' sprinkle.
 *
 * @author Alexander Weissman
 */
return [
    'ACCOUNT' => [
        '@TRANSLATION' => 'Account',

        'EXCEPTION' => [
            'TITLE'       => 'Account Exception',
            'DESCRIPTION' => 'An unspecified error with he account has been encountered.',

            'ACCESS_DENIED' => [
                'TITLE'       => 'Access Denied',
                'DESCRIPTION' => "Hmm, looks like you don't have permission to do that.",
            ],
            'DISABLED' => [
                'TITLE'       => 'Account Disabled',
                'DESCRIPTION' => 'This account has been disabled. Please contact us for more information.',
            ],
            'DEFAULT_GROUP' => [
                'TITLE'       => 'Default Group Not Found',
                'DESCRIPTION' => 'Account registration is not working because the default group {{slug}} does not exist.',
            ],
            'INVALID' => [
                'TITLE'       => 'Account Invalid',
                'DESCRIPTION' => 'This account is not configured properly. Please contact us for more information.',
            ],
            'NOT_FOUND' => [
                'TITLE'       => 'Account Not Found',
                'DESCRIPTION' => 'This account does not exist. It may have been deleted.',
            ],
            'COMPROMISED' => [
                'TITLE'       => 'Account Compromised',
                'DESCRIPTION' => 'Someone may have used your login information to access this page.  For your safety, all sessions were logged out. Please log in again and check your account for suspicious activity. You may also wish to change your password.',
            ],
            'UNVERIFIED' => [
                'TITLE'       => 'Account Unverified',
                'DESCRIPTION' => 'Your account has not yet been verified. Use the <i>{{&ACCOUNT.VERIFICATION}}</i> form to activate your account.',
            ],
            'EXPIRED' => [
                'TITLE'       => 'Session expired',
                'DESCRIPTION' => 'Your session has expired.  Please sign in again.',
            ],
            'INVALID_CREDENTIALS' => [
                'TITLE'       => 'Invalid Credentials',
                'DESCRIPTION' => 'User not found or password is invalid.',
            ],
            'LOGGEDIN' => [
                'TITLE'       => 'Already Logged-in',
                'DESCRIPTION' => "Can't access this resource, as you're already logged-in",
            ],
            'LOGIN_REQUIRED' => [
                'TITLE'       => 'Login Required',
                'DESCRIPTION' => 'Please login to continue',
            ],
            'PASSWORD_EXPIRED' => [
                'TITLE'       => 'Password Expired',
                'DESCRIPTION' => 'Your password has expired. Please reset your password.',
            ],
            'PASSWORD_RESET' => [
                'TITLE'       => 'Invalid Password Reset Token',
                'DESCRIPTION' => 'This password reset request could not be found, or has expired.',
            ],
            'VERIFICATION_DISABLED' => [
                'TITLE'       => 'Verification Disabled',
                'DESCRIPTION' => 'Account verification is disabled. Please contact us for more information.',
            ],
            'VERIFICATION_FAILED' => [
                'TITLE'       => 'Verification Exception',
                'DESCRIPTION' => 'This verification code is not valid, or the account is already verified.',
            ],
        ],
        'ERROR'         => [
            '@TRANSLATION'  => 'Error validating account',
            'MISSING_PARAM' => "Account can't be registered as '{{param}}' is required.",
        ],

        'HAVE_ONE'          => 'Already have an account ?',

        'MASTER_NOT_EXISTS' => 'You cannot register an account until the master account has been created!',
        'MY'                => 'My Account',

        'SETTINGS' => [
            '@TRANSLATION'  => 'Account settings',
            'DESCRIPTION'   => 'Update your account settings, including email, name, and password.',
            'EMAIL'         => 'Update Email',
            'PASSWORD'      => 'Update Password',
            'PERSONAL'      => 'Personal Information',
            'UPDATED'       => 'Account settings updated',
        ],

        'VERIFICATION' => [
            '@TRANSLATION'      => 'Account Verification',
            'CODE'              => [
                '@TRANSLATION' => 'Verification code',
                'ENTER'        => 'Enter verification code',
                'EXPLAIN'      => 'Enter the verification code you received by email.',
                'IDENTIFY'     => 'Identify yourself',
                'SEND'         => 'Send code to email',
                'SENT'         => 'If an unactivated account was found, an email with a verification code was sent to <strong>{{email}}</strong>. Please check your inbox and spam folders for this email.',
                'VERIFY'       => 'Verify code',
            ],
            'COMPLETE'            => 'You have successfully verified your account. You can now login.',
            'EXPLAIN'             => 'Please enter the email address you used to sign up, and a verification code will be sent to your email.',
        ],
    ],

    'EMAIL' => [
        'INVALID'               => 'Invalid email',
        'IN_USE'                => 'Email <strong>{{email}}</strong> is already in use.',
        'VERIFICATION_REQUIRED' => 'Email (verification required - use a real address!)',
    ],

    'FIRST_NAME' => 'First name',

    'GUEST' => 'Guest',

    'HEADER_MESSAGE_ROOT' => 'YOU ARE SIGNED IN AS THE ROOT USER', // TODO

    'LAST_NAME' => 'Last name',
    'LOCALE'    => [
        'ACCOUNT' => 'The language and locale to use for your account',
        'INVALID' => '{{locale}} is not a valid locale.',
    ],
    'LOGIN'  => [
        '@TRANSLATION' => 'Log in',
        'PAGE'         => 'Log in to your account',
    ],
    'LOGOUT'     => 'Logout',
    'LOGGED_OUT' => 'You have been logged out successfully.',

    'NAME_AND_EMAIL' => 'Name and email',

    'PASSWORD' => [
        '@TRANSLATION' => 'Password',

        'BETWEEN'   => 'Between {{min}}-{{max}} characters',

        'CONFIRM'               => 'Confirm password',
        'CONFIRM_CURRENT'       => 'Please confirm your current password',
        'CONFIRM_NEW'           => 'Confirm New Password',
        'CONFIRM_NEW_EXPLAIN'   => 'Re-enter your new password',
        // 'CREATE'                => [
        //     '@TRANSLATION'  => 'Create Password',
        //     'PAGE'          => 'Choose a password for your new account.',
        //     'SET'           => 'Set Password and Sign In',
        // ],
        'CURRENT'               => 'Current Password',
        'CURRENT_EXPLAIN'       => 'You must confirm your current password to make changes',

        'INVALID'           => "Current password doesn't match the one we have on record",

        'NEW'               => [
            '@TRANSLATION' => 'New Password',
            'EXPLAIN'      => 'Choose a new password',
            'SET'          => 'Set New Password',
        ],

        'RESET' => [
            '@TRANSLATION'  => 'Reset your password',
            'EMAIL'         => 'Please enter the email address you used to sign up. A verification code will be sent to your email.',
            'INVALID'       => 'This password reset request could not be found or has expired. Please try submitting your request again.',
            'PAGE'          => 'Use this form to reset your password in case you don\'t have access to your account.',
            'REQUEST_SENT'  => 'If the email <strong>{{email}}</strong> matches an account in our system, a verification code will be sent to <strong>{{email}}</strong>.',
            'SUCCESS'       => 'Your new password is set. You can now log in with your new password.',
        ],

        'UPDATED'           => 'Account password updated',
    ],

    'PROFILE'       => [
        'UPDATED'   => 'Profile settings updated',
    ],

    'RATE_LIMIT_EXCEEDED'       => 'The rate limit for this action has been exceeded.  You must wait another {{delay}} seconds before you will be allowed to make another attempt.',
    'REGISTER'                  => [
        '@TRANSLATION'  => 'Register',
        'PAGE'          => 'Create a new account',
    ],
    'REGISTER_ME'               => 'Sign me up',
    'REGISTRATION'              => [
        'COMPLETE'                 => 'You have successfully registered. You can now sign in.',
        'COMPLETE_VERIFICATION'    => 'You have successfully registered. A link to activate your account has been sent to <strong>{{email}}</strong>. You will not be able to sign in until you complete this step.',
        'DISABLED'                 => "We're sorry, account registration has been disabled.",
        'ERROR'                    => 'Registration error',
        'QUESTION'                 => "You don't have an account yet?",
        'MAIL_ERROR'               => 'An error occurred while sending the verification email. Please contact your administrator.',
        'UNKNOWN'                  => 'A problem was encountered during the account registration process.',
    ],
    'REMEMBER_ME'               => 'Remember me',

    'TOS_AGREEMENT' => 'By registering an account with {{site_title}}, you accept the terms and conditions.',

    'USERNAME' => [
        '@TRANSLATION'  => 'Username',
        'CHOOSE'        => 'Choose a unique username',
        'INVALID'       => 'Invalid username',
        'IN_USE'        => 'Username <strong>{{user_name}}</strong> is already in use.',
        'NOT_AVAILABLE' => "Username <strong>{{user_name}}</strong> is not available. Choose a different name, or click 'suggest'.",
        'UNMODIFIABLE'  => 'Username should not be changed. Click to unlock, and use with caution.',
    ],

    'WELCOME' => 'Welcome back, {{full_name}}!',
];
