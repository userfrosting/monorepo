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
 * French message token translations for the 'core' sprinkle.
 *
 * @author Louis Charette
 */
return [
    'BUILT_WITH_UF' => 'Créé avec <a href="http://www.userfrosting.com">UserFrosting</a>',

    'CAPTCHA'      => [
        '@TRANSLATION' => 'Captcha',
        'FAIL'         => 'La valeur du captcha n\'a pas été entrée correctement.',
        'SPECIFY'      => 'Entrer la valeur du captcha',
        'VERIFY'       => 'Vérification du captcha',
    ],
    'COPYRIGHT'     => 'Copyright {{year}}',
    'CSRF_MISSING'  => 'Jeton CSRF manquant. Essayez de rafraîchir la page et de soumettre de nouveau?',

    // TODO - Implement
    // 'DOWNLOAD'     => [
    //     '@TRANSLATION' => 'Télécharger',
    //     'CSV'          => 'Télécharger CSV',
    // ],

    'EMAIL' => [
        '@TRANSLATION' => 'Courriel',
        'YOUR'         => 'Votre adresse courriel',
    ],

    // TODO - Implement
    // 'LEGAL' => [
    //     '@TRANSLATION' => 'Politique légale',
    //     'DESCRIPTION'  => 'Notre politique légale s\'applique à votre utilisation de ce site et de nos services.',
    // ],
    'LOCALE' => [
        '@TRANSLATION' => 'Langue',
    ],

    // TODO - Implement
    'PAGINATION' => [
        // 'GOTO'     => 'Aller à la page',
        // 'SHOW'     => 'Afficher',
        'OUTPUT'   => 'Affichage de {{first}} à {{last}} sur {{count}}',
        // 'NEXT'     => 'Prochaine page',
        'PAGE_X_OF_Y' => 'Page {{current}} de {{last}}',
        'PER_PAGE'    => '{{count}} par page',
        // 'PREVIOUS' => 'Page précédente',
        // 'FIRST'    => 'Première page',
        // 'LAST'     => 'Dernière page',
    ],
    'PRIVACY_POLICY' => 'Politique de confidentialité',

    'SLUG'                     => 'Jeton',
    'SLUG_IN_USE'              => 'Un jeton <strong>{{slug}}</strong> existe déjà',
    'SPRUNJE'                  => [
        'FILTERS'      => 'Filtres',
        'FILTER_CLEAR' => 'Effacer filtres',
        'NO_RESULTS'   => 'Aucun résultat trouvé.',
        'SEARCH'       => 'Rechercher {{term}}...',
    ],
    'STATUS'                   => 'État',
    'STEP_X'                   => 'Étape {{step}}',
    'SUGGEST'                  => 'Suggérer',

    'THEME_BY'      => 'Thème créé avec',
    'TOS'           => 'Termes et conditions',

    // Actions words
    'ACTIONS'                  => 'Actions',
    'ACTIVATE'                 => 'Autoriser',
    'ACTIVE'                   => 'Activé',
    'ADD'                      => 'Ajouter',
    'CANCEL'                   => 'Annuler',
    'CONFIRM'                  => 'Confirmer',
    'CONFIRM_ACTION'           => 'Veuillez confirmer pour continuer.',
    'CONFIRMATION'             => 'Confirmation',
    'CREATE'                   => 'Créer',
    'CREATED_ON'               => 'Créé le',
    'DELETE'                   => 'Supprimer',
    'DELETE_CONFIRM'           => 'Êtes-vous sûr de vouloir supprimer ceci?',
    'DELETE_CONFIRM_YES'       => 'Oui, supprimer',
    'DELETE_CONFIRM_NAMED'     => 'Êtes-vous sûr de vouloir supprimer {{name}}?',
    'DELETE_CONFIRM_YES_NAMED' => 'Oui, supprimer {{name}}',
    'DELETE_NAMED'             => 'Supprimer {{name}}',
    'DENY'                     => 'Refuser',
    'DESCRIPTION'              => 'Description',
    'DISABLE'                  => 'Désactiver',
    'DISABLED'                 => 'Désactivé',
    'EDIT'                     => 'Modifier',
    'ENABLE'                   => 'Activer',
    'ENABLED'                  => 'Activé',
    'MORE_OPTIONS'             => 'Plus d\'options',
    'NO'                       => 'Non',
    'NONE'                     => 'Aucun',
    'OPTIONAL'                 => 'Facultatif',
    'OVERRIDE'                 => 'Forcer',
    'RESET'                    => 'Réinitialiser',
    'SAVE'                     => 'Sauvegarder',
    'SEARCH'                   => 'Rechercher',
    'SORT'                     => 'Trier',
    'SUCCESS'                  => 'Succès',
    'SUBMIT'                   => 'Envoyer',
    'PRINT'                    => 'Imprimer',
    'REMOVE'                   => 'Supprimer',
    'TRY_AGAIN'                => 'Réessayer',
    'UNACTIVATED'              => 'Non activé',
    'UNKNOWN'                  => 'Inconnu',
    'UNVERIFIED'               => 'Non vérifié',
    'UPDATE'                   => 'Mettre à jour',
    'VERIFIED'                 => 'Vérifié',
    'VIEW'                     => 'Voir',
    'WARNING_CANNOT_UNDONE'    => 'Cette action ne peut être annulée.',
    'YES'                      => 'Oui',
];
