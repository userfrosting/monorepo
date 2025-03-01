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
                'DESCRIPTION' => 'Your account has not yet been verified. Check your emails / spam folder for account activation instructions.',
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
            'PASSWORD_RESET' => [
                'TITLE'       => 'Invalid Password Reset Token',
                'DESCRIPTION' => 'This password reset request could not be found, or has expired.',
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
            'NEW_LINK_SENT'     => 'We have emailed a new verification link to {{email}}.  Please check your inbox and spam folders for this email.',
            'RESEND'            => 'Resend verification email',
            'COMPLETE'          => 'You have successfully verified your account. You can now login.',
            'EMAIL'             => 'Please enter the email address you used to sign up, and your verification email will be resent.',
            // 'PAGE'              => 'Resend the verification email for your new account.',
            'SEND'              => 'Email the verification link for my account',
            'TOKEN_NOT_FOUND'   => 'Verification token does not exist / Account is already verified',
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
    'LOGIN'  => 'Login',
    'LOGOUT' => 'Logout',

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

        'FORGOTTEN' => 'Forgotten Password',
        'FORGET'    => [
            '@TRANSLATION'      => 'Forgot your password?',
            'EMAIL'             => 'Please enter the email address you used to sign up. A link with instructions to reset your password will be emailed to you.',
            'EMAIL_SEND'        => 'Email Password Reset Link',
            // 'INVALID'           => 'This password reset request could not be found or has expired. Please try <a href="{{url}}">submitting your request</a> again.',
            // 'PAGE'              => 'Get a link to reset your password.',
            'REQUEST_CANNED'    => 'Lost password request cancelled.',
            'REQUEST_SENT'      => 'If the email <strong>{{email}}</strong> matches an account in our system, a password reset link will be sent to <strong>{{email}}</strong>.',
        ],

        'INVALID'           => "Current password doesn't match the one we have on record",

        'NEW'               => 'New Password',

        'RESET' => [
            '@TRANSLATION'      => 'Reset Password',
            // 'CHOOSE'            => 'Please choose a new password to continue.',
            // 'CONFIRM'           => ' Are you sure you want to send <strong>{{full_name}} ({{ user_name }})</strong> a link that will allow them to reset their password ?',
            // 'PAGE'              => 'Choose a new password for your account.',
            // 'SEND'              => 'Set New Password and Sign In',
        ],

        'UPDATED'           => 'Account password updated',
    ],

    'PROFILE'       => [
        'UPDATED'   => 'Profile settings updated',
    ],

    'RATE_LIMIT_EXCEEDED'       => 'The rate limit for this action has been exceeded.  You must wait another {{delay}} seconds before you will be allowed to make another attempt.',
    'REGISTER'                  => 'Register',
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
    'REMEMBER_ME'               => 'Keep me signed in',

    'TOS'           => 'Terms and Conditions',
    'TOS_AGREEMENT' => 'By registering an account with {{site_title}}, you accept the <a {{link_attributes | raw}}>terms and conditions</a>.',
    // 'TOS_FOR'       => 'Terms and Conditions for {{title}}',

    'USERNAME' => [
        '@TRANSLATION'  => 'Username',
        'CHOOSE'        => 'Choose a unique username',
        'INVALID'       => 'Invalid username',
        'IN_USE'        => 'Username <strong>{{user_name}}</strong> is already in use.',
        'NOT_AVAILABLE' => "Username <strong>{{user_name}}</strong> is not available. Choose a different name, or click 'suggest'.",
    ],

    'WELCOME' => 'Welcome back, {{full_name}}!',
];
