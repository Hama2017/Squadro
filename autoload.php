<?php
/**
 * Autoloader pour les classes du jeu Squadro
 *
 * Ce fichier définit la fonction d'autoloading pour charger automatiquement les classes
 */

// Fonction d'autoloading
function squadroAutoload($className) {
    $classMap = [
        // Classes de base du jeu
        'PlateauSquadro' => 'class/PlateauSquadro.php',
        'PieceSquadro' => 'class/PieceSquadro.php',
        'ActionSquadro' => 'class/ActionSquadro.php',
        'ArrayPieceSquadro' => 'class/ArrayPieceSquadro.php',

        // Classes d'interface utilisateur
        'SquadroUIGenerator' => 'class/ui/SquadroUIGenerator.php',
        'PieceSquadroUI' => 'class/ui/PieceSquadroUI.php',

        // Classes de modèle pour la persistance
        'JoueurSquadro' => 'skel/JoueurSquadro.php',
        'PartieSquadro' => 'skel/PartieSquadro.php',
        'PDOSquadro' => 'skel/PDOSquadro.php'
    ];

    if (isset($classMap[$className])) {
        $file = $classMap[$className];
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
}

// Enregistrement de la fonction d'autoloading
spl_autoload_register('squadroAutoload');