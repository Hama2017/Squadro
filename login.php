<?php
/**
 * Page de connexion/inscription pour le jeu Squadro
 *
 * Ce script gère la connexion et l'inscription des joueurs
 */

// Inclure les classes nécessaires
require_once 'skel/JoueurSquadro.php';
require_once 'skel/PDOSquadro.php';

// Démarrer la session
session_start();

/**
 * Génère le formulaire de connexion
 *
 * @return string Code HTML du formulaire
 */
function getPageLogin(): string {
    $form = '<!DOCTYPE html>
    <html class="no-js" lang="fr" dir="ltr">
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="Author" content="Squadro Game" />
        <link rel="stylesheet" href="squadro.css" />
        <title>Accès à la salle de jeux</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                flex-direction: column;
                align-items: center;
                background-color: #f5f5f5;
                margin: 0;
                padding: 20px;
            }
            
            h1, h2 {
                color: #333;
                text-align: center;
            }
            
            .squadro {
                background-color: #fff;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 600px;
                margin-top: 20px;
            }
            
            fieldset {
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 3px;
            }
            
            legend {
                font-weight: bold;
                padding: 0 10px;
            }
            
            input[type="text"] {
                width: calc(100% - 120px);
                padding: 8px;
                margin-right: 10px;
                border: 1px solid #ccc;
                border-radius: 3px;
            }
            
            input[type="submit"] {
                background-color: #4CAF50;
                color: white;
                border: none;
                padding: 10px 15px;
                cursor: pointer;
                border-radius: 3px;
            }
            
            input[type="submit"]:hover {
                background-color: #45a049;
            }
        </style>
      </head>
      <body>
        <div class="squadro">
          <h1>Accès au salon Squadro</h1>
          <h2>Identification du joueur</h2>
          <form action="'.$_SERVER['PHP_SELF'].'" method="post">
            <fieldset>
              <legend>Nom</legend>
              <input type="text" name="playerName" required placeholder="Entrez votre nom" />
              <input type="submit" name="action" value="connecter">
            </fieldset>
          </form>
        </div>
      </body>
    </html>';
    return $form;
}

// Traitement du formulaire de connexion
if (isset($_REQUEST['playerName']) && !empty($_REQUEST['playerName'])) {
    // Connexion à la base de données
    require_once 'env/db.php';
    PDOSquadro::initPDO($_ENV['sgbd'], $_ENV['host'], $_ENV['database'], $_ENV['user'], $_ENV['password']);

    // Recherche du joueur dans la base
    $player = PDOSquadro::selectPlayerByName($_REQUEST['playerName']);

    // Si le joueur n'existe pas, le créer
    if (is_null($player)) {
        $player = PDOSquadro::createPlayer($_REQUEST['playerName']);
    }

    // Stocker le joueur en session
    $_SESSION['player'] = $player;
    $_SESSION['app_state'] = 'Home'; // État initial de l'application

    // Redirection vers la page d'accueil
    header('Location: home.php');
    exit;
} else {
    // Afficher le formulaire de connexion
    echo getPageLogin();
}