<?php

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

/**
 * French message token translations for the 'admin' sprinkle.
 *
 * @author Louis Charette
 */
return [
    'ACTIVITY' => [
        1 => 'Activité',
        2 => 'Activités',

        'LAST'             => 'Dernière activité',
        'LATEST'           => 'Activités récentes',
        'PAGE'             => 'Activités',
        'PAGE_DESCRIPTION' => 'Une liste des activités des utilisateurs',
        'TIME'             => 'Date de l\'activité',
    ],
    'ADMIN_PANEL' => "Panneau d'admin",

    'DASHBOARD'           => 'Tableau de bord',
    'DELETE_MASTER'       => 'Vous ne pouvez pas supprimer le compte principal !',
    'DELETION_SUCCESSFUL' => 'L\'utilisateur <strong>{{user_name}}</strong> a été supprimé avec succès.',
    'DETAILS_UPDATED'     => 'Les détails du compte de <strong>{{user_name}}</strong> ont été mis à jour',
    'DISABLE_MASTER'      => 'Vous ne pouvez pas désactiver le compte principal !',
    'DISABLE_SELF'        => 'Vous ne pouvez pas désactiver votre propre compte !',
    'DISABLE_SUCCESSFUL'  => 'Le compte de l\'utilisateur <strong>{{user_name}}</strong> a été désactivé avec succès.',

    'ENABLE_SUCCESSFUL'   => 'Le compte de l\'utilisateur <strong>{{user_name}}</strong> a été activé avec succès.',

    'GROUP'               => [
        1                     => 'Groupe',
        2                     => 'Groupes',

        'CREATE'              => 'Créer un groupe',
        'CREATION_SUCCESSFUL' => 'Groupe <strong>{{name}}</strong> créé avec succès',
        'DELETE'              => 'Supprimer le groupe',
        'DELETE_CONFIRM'      => 'Êtes-vous certain de vouloir supprimer le groupe <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => 'Vous ne pouvez pas supprimer le groupe <strong>{{name}}</strong> parce que c\'est le groupe par défaut pour les utilisateurs nouvellement enregistrés.',
        'DELETE_YES'          => 'Oui, supprimer le groupe',
        'DELETION_SUCCESSFUL' => 'Groupe <strong>{{name}}</strong> supprimé avec succès',
        'EDIT'                => 'Modifier le groupe',
        'EXCEPTION'           => 'Erreur de groupe',
        'ICON'                => 'Icône',
        'ICON_EXPLAIN'        => 'Icône des membres du groupe',
        'INFO_PAGE'           => 'Afficher et modifier les détails du groupe.',
        'NAME'                => 'Nom du groupe',
        'NAME_IN_USE'         => 'Un groupe nommé <strong>{{name}}</strong> existe déjà',
        'NAME_EXPLAIN'        => 'Spécifiez le nom du groupe',
        'NONE'                => 'Aucun groupe',
        'NOT_EMPTY'           => 'Vous ne pouvez pas le faire car il y a encore des utilisateurs associés au groupe <strong>{{name}}</strong>.',
        'NOT_FOUND'           => 'Groupe non trouvé',
        'PAGE'                => 'Groupes',
        'PAGE_DESCRIPTION'    => 'Une liste des groupes pour votre site. Fournit des outils de gestion pour éditer et supprimer des groupes.',
        'UPDATE'              => 'Les détails du groupe <strong>{{name}}</strong> ont été enregistrés',
        'USERS'               => 'Utilisateurs dans ce groupe',
    ],

    'MANUALLY_ACTIVATED'    => 'Le compte de {{user_name}} a été activé manuellement',

    'PERMISSION' => [
        1                  => 'Autorisation',
        2                  => 'Autorisations',

        'ASSIGN'           => [
            '@TRANSLATION' => 'Assigner des autorisations',
            'EXPLAIN'      => 'Sélectionnez les autorisations que vous souhaitez attribuer à ce rôle.',
        ],
        'HOOK_CONDITION'   => 'Hook/Conditions',
        'ID'               => 'ID de l\'autorisation',
        'INFO_PAGE'        => 'Afficher et modifier les détails des autorisations.',
        'NOT_FOUND'        => 'Autorisation non trouvée',
        'PAGE'             => 'Autorisations',
        'PAGE_DESCRIPTION' => 'Une liste des autorisations pour votre site. Fournit des outils de gestion pour modifier et supprimer des autorisations.',
        'UPDATE'           => 'Mettre à jour les autorisations',
        'USERS'            => 'Utilisateurs avec cette autorisation',
        'VIA_ROLES'        => 'A la permission via les rôles',
    ],

    'ROLE' => [
        1                     => 'Rôle',
        2                     => 'Rôles',

        'CREATE'              => 'Créer un rôle',
        'CREATION_SUCCESSFUL' => 'Rôle <strong>{{name}}</strong> créé avec succès',
        'DELETE'              => 'Supprimer le rôle',
        'DELETE_CONFIRM'      => 'Êtes-vous certain de vouloir supprimer le rôle <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => 'Vous ne pouvez pas supprimer le rôle <strong>{{name}}</strong> parce que c\'est un rôle par défaut pour les utilisateurs nouvellement enregistrés.',
        'DELETE_YES'          => 'Oui, supprimer le rôle',
        'DELETION_SUCCESSFUL' => 'Rôle <strong>{{name}}</strong> supprimé avec succès',
        'EDIT'                => 'Modifier le rôle',
        'EXCEPTION'           => 'Erreur de rôle',
        'HAS_USERS'           => 'Vous ne pouvez pas le faire parce qu\'il y a encore des utilisateurs qui ont le rôle <strong>{{name}}</strong>.',
        'INFO_PAGE'           => 'Afficher et modifier les détails du rôle.',
        'MANAGE'              => 'Gérer les rôles',
        'MANAGE_EXPLAIN'      => 'Les rôles sélectionnés seront attribués à l\'utilisateur.',
        'NAME'                => 'Nom du rôle',
        'NAME_EXPLAIN'        => 'Spécifiez le nom du rôle',
        'NAME_IN_USE'         => 'Un rôle nommé <strong>{{name}}</strong> existe déjà',
        'NOT_FOUND'           => 'Rôle non trouvé',
        'PAGE'                => 'Rôles',
        'PAGE_DESCRIPTION'    => 'Une liste des rôles de votre site. Fournit des outils de gestion pour modifier et supprimer des rôles.',
        'PERMISSIONS'         => 'Autorisations associés à ce rôle',
        'PERMISSIONS_UPDATED' => 'Autorisations mises à jour pour le rôle <strong>{{name}}</strong>',
        'UPDATE'              => 'Mettre à jour les rôles',
        'UPDATED'             => 'Détails mis à jour pour le rôle <strong>{{name}}</strong>',
        'USERS'               => 'Utilisateurs avec ce rôle',
    ],

    'SITE_CONFIG' => [
        '@TRANSLATION'      => 'Configuration du site',
        'CACHE'             => [
            '@TRANSLATION'      => 'Gestion du cache',
            'CLEAR'             => 'Vider le cache',
            'CLEAR_CONFIRM'     => 'Voulez-vous vraiment supprimer le cache du site?',
            'CLEAR_CONFIRM_YES' => 'Oui, vider le cache',
            'CLEARED'           => 'Cache effacé avec succès !',
        ],
        'PAGE_DESCRIPTION'  => 'Utilisez les formulaires ci-dessous pour mettre à jour les paramètres de configuration de votre site.',
        'SYSTEM_INFO'       => [
            '@TRANSLATION'  => 'Informations sur le système',
            'DB_NAME'       => 'Base de donnée',
            'DB_CONNECTION' => 'Connexion à la base de données',
            'DB_VERSION'    => 'Version base de données',
            'DIRECTORY'     => 'Répertoire du projet',
            'PHP_VERSION'   => 'Version de PHP',
            'SERVER'        => 'Logiciel serveur',
            'SPRINKLES'     => 'Sprinkles chargés',
            'UF_VERSION'    => 'Version de UserFrosting',
        ],
    ],

    'USER'           => [
        1       => 'Utilisateur',
        2       => 'Utilisateurs',

        'ADMIN' => [
            'CHANGE_PASSWORD'        => 'Changer le mot de passe de l\'utilisateur',
            'PASSWORD_RESET_SUCCESS' => 'Le mot de passer de <strong>{{full_name}}</strong> a été réinitialisé.',
            'PASSWORD_RESET'         => 'Réinitialiser le mot de passe de l\'utilisateur',
            'PASSWORD_RESET_CONFIRM' => 'Êtes-vous sûr de vouloir réinitialiser le mot de passe de <strong>{{full_name}}</strong> ? Son mot de passe actuel sera désactivé et il devra le réinitialiser via la fonctionnalité « Réinitialiser le mot de passe » lors de sa prochaine connexion.',
        ],
        'ACTIVATE'         => 'Autoriser l\'utilisateur',
        'ACTIVATE_CONFIRM' => 'Êtes-vous sûr de vouloir activer <strong>{{full_name}} ({{user_name}})</strong> ?',
        'CREATE'           => 'Créer un utilisateur',
        'CREATED'          => 'L\'utilisateur <strong>{{user_name}}</strong> a été créé avec succès',
        'DELETE'           => 'Supprimer l\'utilisateur',
        'DELETE_CONFIRM'   => 'Êtes-vous certain de vouloir supprimer l\'utilisateur <strong>{{full_name}} ({{user_name}})</strong>?',
        'DELETED'          => 'Utilisateur supprimé',
        'DISABLE'          => 'Désactiver l\'utilisateur',
        'DISABLE_CONFIRM'  => 'Êtes-vous sûr de vouloir désactiver <strong>{{full_name}} ({{user_name}})</strong> ?',
        'EDIT'             => 'Modifier l\'utilisateur',
        'ENABLE'           => 'Activer l\'utilisateur',
        'ENABLE_CONFIRM'   => 'Êtes-vous sûr de vouloir activer <strong>{{full_name}} ({{user_name}})</strong> ?',
        'INFO_PAGE'        => 'Afficher et modifier les détails de l\'utilisateur',
        'LATEST'           => 'Derniers utilisateurs',
        'PAGE'             => 'Utilisateurs',
        'PAGE_DESCRIPTION' => 'Une liste des utilisateurs de votre site. Fournit des outils de gestion incluant la possibilité de modifier les détails de l\'utilisateur, d\'activer manuellement les utilisateurs, d\'activer / désactiver les utilisateurs et plus.',
        'VIEW_ALL'         => 'Voir tous les utilisateurs',
    ],
];
