<?php
// Autoload function for Squadro classes
function squadroAutoload($className) {
    $classMap = [
        'SquadroUIGenerator' => 'class/SquadroUIGenerator.php',
        'PieceSquadroUI' => 'class/ui/PieceSquadroUI.php',
        'ActionSquadro' => 'class/ActionSquadro.php',
        'PlateauSquadro' => 'class/PlateauSquadro.php',
        'PieceSquadro' => 'class/PieceSquadro.php'
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

spl_autoload_register('squadroAutoload');