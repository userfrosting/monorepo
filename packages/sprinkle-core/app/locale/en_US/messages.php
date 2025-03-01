<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

/**
 * US English message token translations for the 'core' sprinkle.
 *
 * @author Alexander Weissman
 */
return [
    'BUILT_WITH_UF' => 'Created with <a href="http://www.userfrosting.com">UserFrosting</a>',

    'CAPTCHA' => [
        '@TRANSLATION' => 'Captcha',
        'FAIL'         => 'You did not enter the captcha code correctly.',
        'SPECIFY'      => 'Enter the captcha',
        'VERIFY'       => 'Verify the captcha',
    ],
    'COPYRIGHT'     => 'Copyright {{year}}',
    'CSRF_MISSING'  => 'Missing CSRF token. Try refreshing the page and then submitting again?',

    // TODO - Implement
    // 'DOWNLOAD'      => [
    //     '@TRANSLATION' => 'Download',
    //     'CSV'          => 'Download CSV',
    // ],

    'EMAIL' => [
        '@TRANSLATION' => 'Email',
        'YOUR'         => 'Your email address',
    ],

    // TODO - Implement
    // 'LEGAL' => [
    //     '@TRANSLATION' => 'Legal Policy',
    //     'DESCRIPTION'  => 'Our legal policy applies to your usage of this website and our services.',
    // ],
    'LOCALE' => [
        '@TRANSLATION' => 'Locale',
    ],

    // TODO - Implement
    'PAGINATION' => [
        // 'GOTO' => 'Jump to Page',
        // 'SHOW' => 'Show',
        'OUTPUT'   => 'Showing {{first}} - {{last}} of {{count}}',
        // 'NEXT'     => 'Next page',
        'PAGE_X_OF_Y' => 'Page {{current}} of {{last}}',
        'PER_PAGE'    => '{{count}} per page',
        // 'PREVIOUS' => 'Previous page',
        // 'FIRST'    => 'First page',
        // 'LAST'     => 'Last page',
    ],
    // TODO - Implement
    'PRIVACY' => [
        '@TRANSLATION' => 'Privacy Policy',
        'DESCRIPTION'  => 'Our privacy policy outlines what kind of information we collect from you and how we will use it.',
    ],

    'SLUG'           => 'Slug',
    'SLUG_IN_USE'    => 'A <strong>{{slug}}</strong> slug already exists',
    'SPRUNJE'        => [
        'FILTERS'      => 'Filters',
        'FILTER_CLEAR' => 'Clear filters',
        'NO_RESULTS'   => "Sorry, we've got nothing here.", // TODO : Use with Sprunje pagination -- Move to Sprune
        'SEARCH'       => 'Search {{term}}...',
    ],
    'STATUS'         => 'Status',
    'SUGGEST'        => 'Suggest',

    'THEME_BY'      => 'Theme built with',

    // Actions words
    'ACTIONS'                  => 'Actions',
    'ACTIVATE'                 => 'Activate',
    'ACTIVE'                   => 'Active',
    'ADD'                      => 'Add',
    'CANCEL'                   => 'Cancel',
    'CONFIRM'                  => 'Confirm',
    'CONFIRM_ACTION'           => 'Please confirm to proceed.',
    'CONFIRMATION'             => 'Confirmation',
    'CREATE'                   => 'Create',
    'CREATED_ON'               => 'Created on',
    'DELETE'                   => 'Delete',
    'DELETE_CONFIRM'           => 'Are you sure you want to delete this?',
    'DELETE_CONFIRM_YES'       => 'Yes, delete',
    'DELETE_CONFIRM_NAMED'     => 'Are you sure you want to delete {{user_name}}?',
    'DELETE_CONFIRM_YES_NAMED' => 'Yes, delete {{name}}',
    'DELETE_NAMED'             => 'Delete {{name}}',
    'DENY'                     => 'Deny',
    'DESCRIPTION'              => 'Description',
    'DISABLE'                  => 'Disable',
    'DISABLED'                 => 'Disabled',
    'EDIT'                     => 'Edit',
    'ENABLE'                   => 'Enable',
    'ENABLED'                  => 'Enabled',
    'NO'                       => 'No',
    'NONE'                     => 'None',
    'OPTIONAL'                 => 'Optional',
    'OVERRIDE'                 => 'Override',
    'RESET'                    => 'Reset',
    'SAVE'                     => 'Save',
    'SEARCH'                   => 'Search',
    'SORT'                     => 'Sort',
    'SUBMIT'                   => 'Submit',
    'PRINT'                    => 'Print',
    'REMOVE'                   => 'Remove',
    'UNACTIVATED'              => 'Unactivated',
    'UNKNOWN'                  => 'Unknown',
    'UPDATE'                   => 'Update',
    'VIEW'                     => 'View',
    'WARNING_CANNOT_UNDONE'    => 'This action cannot be undone.',
    'YES'                      => 'Yes',
];
