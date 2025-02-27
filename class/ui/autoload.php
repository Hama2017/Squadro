<?php
spl_autoload_register(function ($class) {
    // Vérifier si le nom de classe contient 'UI'
    if (strpos($class, 'UI') !== false) {
        include __DIR__ . '/' . $class . '.php';
    } else {
        // Si ce n'est pas une classe UI, utilisez l'autoload existant
        include_once __DIR__ . '/../' . $class . '.php';
    }
});
?>