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
 */
return [
    'ERROR' => [
        '@TRANSLATION' => 'Erreur',

        '400' => [
            'TITLE'       => 'Erreur 400: Mauvaise requête',
            'DESCRIPTION' => "Ce n'est probablement pas de votre faute.",
        ],
        '401' => [
            'TITLE'       => 'Non autorisé',
            'DESCRIPTION' => 'La requête nécessite une authentification utilisateur valide.',
        ],
        '403' => [
            'TITLE'       => 'Interdit',
            'DESCRIPTION' => "Vous n'êtes pas autorisé à effectuer l'opération demandée.",
        ],
        '404' => [
            'TITLE'       => 'Non trouvé',
            'DESCRIPTION' => 'La ressource demandée est introuvable.',
        ],
        '405' => [
            'TITLE'       => 'Méthode non autorisée',
            'DESCRIPTION' => "La méthode de requête n'est pas supportée pour la ressource demandée.",
        ],
        '410' => [
            'TITLE'       => 'Parti',
            'DESCRIPTION' => "La ressource cible n'est plus disponible sur le serveur d'origine.",
        ],

        // Generic title and description for error code not handled above
        'TITLE'       => 'Nous avons détecté une grande perturbation dans la Force.',
        'DESCRIPTION' => 'Oups, il semble que notre serveur ait fait une erreur. Si vous êtes un administrateur, veuillez vérifier les logs PHP ou UserFrosting.',

        'MAIL' => "Erreur fatale lors de l'envoi du courriel. Contactez votre administrateur. Si vous êtes administrateur, consultez les logs.",
        'MISC' => "Une erreur s'est produite.",

        'RATE_LIMIT_EXCEEDED' => [
            'TITLE'       => 'Limite de taux dépassée',
            'DESCRIPTION' => 'La limite de taux pour cette action a été dépassée. Vous devez attendre encore {{delay}} secondes avant de pouvoir faire une autre tentative.',
        ],
    ],
];
