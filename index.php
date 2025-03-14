<?php
/**
 * Contrôleur principal du jeu Squadro
 *
 * Ce script gère l'affichage et la navigation entre les différents états du jeu
 */

// Inclusion de l'autoload
require_once 'autoload.php';
require_once 'env/db.php';

// Démarrage de la session
session_start();

// Vérifier si le joueur est connecté
if (!isset($_SESSION['player'])) {
    header('Location: login.php');
    exit;
}

// Initialiser la connexion à la base de données
PDOSquadro::initPDO($_ENV['sgbd'], $_ENV['host'], $_ENV['database'], $_ENV['user'], $_ENV['password']);

// Traitement des requêtes GET pour charger une partie spécifique
if (isset($_GET['partieId']) && is_numeric($_GET['partieId'])) {
    $partieId = (int)$_GET['partieId'];
    $partie = PDOSquadro::getPartieSquadroById($partieId);

    // Ajouter ce code après avoir chargé la partie depuis la BD
    if (isset($_GET['partieId']) && is_numeric($_GET['partieId'])) {
        $partieId = (int)$_GET['partieId'];
        $partie = PDOSquadro::getPartieSquadroById($partieId);

        if ($partie) {
            // Charger l'état de la partie depuis la base de données
            $plateau = PlateauSquadro::fromJson($partie->getJson());
            $action = new ActionSquadro($plateau);

            // Déterminer quelle couleur de pièce est associée au joueur connecté
            $currentPlayer = $_SESSION['player'];
            $playerColor = null;

            if ($partie->getJoueurs()[0]->getId() === $currentPlayer->getId()) {
                // Le joueur connecté est le joueur 1 (blancs ou premiers à rejoindre)
                $playerColor = PieceSquadro::BLANC;
            } elseif (isset($partie->getJoueurs()[1]) &&
                $partie->getJoueurs()[1]->getId() === $currentPlayer->getId()) {
                // Le joueur connecté est le joueur 2 (noirs ou deuxième à rejoindre)
                $playerColor = PieceSquadro::NOIR;
            } else {
                // Le joueur connecté n'est pas un participant de cette partie
                $_SESSION['erreur'] = "Vous n'êtes pas un participant de cette partie.";
                header('Location: home.php');
                exit;
            }

// Déterminer le joueur actif en fonction de l'état de la partie
            $joueurActif = $partie->getJoueurActif() === PartieSquadro::PLAYER_ONE ?
                PieceSquadro::BLANC : PieceSquadro::NOIR;

// Vérifier si c'est le tour du joueur connecté
            $isPlayerTurn = ($playerColor === $joueurActif);

            // Stocker ces informations en session
            $_SESSION['playerColor'] = $playerColor;
            $_SESSION['joueurActif'] = $joueurActif;
            $_SESSION['isPlayerTurn'] = $isPlayerTurn;
            $_SESSION['action'] = $action->toJson();
            $_SESSION['plateau'] = $plateau->toJson();
            $_SESSION['partieId'] = $partieId;
            $_SESSION['etat'] = 'ChoixPiece';

            if (!$isPlayerTurn && $partie->getGameStatus() === 'waitingForPlayer') {
                // Afficher un message d'attente si ce n'est pas le tour du joueur
                $_SESSION['erreur'] = "C'est le tour de votre adversaire. Veuillez patienter.";
                $_SESSION['etat'] = 'AttenteTour';
            }

            // Vérifier si c'est une partie terminée
            if ($partie->getGameStatus() === 'finished') {
                $_SESSION['etat'] = 'ConsulterPartieVictoire';
            }
        }
    }


} else if (!isset($_SESSION['etat'])) {
    // Si aucune partie n'est spécifiée et qu'il n'y a pas d'état en session, rediriger vers la page d'accueil
    header('Location: home.php');
    exit;
}

// Récupération de l'état du jeu pour l'affichage
$action = isset($_SESSION['action'])
    ? ActionSquadro::fromJson($_SESSION['action'])
    : null;
$plateau = isset($_SESSION['plateau'])
    ? PlateauSquadro::fromJson($_SESSION['plateau'])
    : null;
$joueurActif = $_SESSION['joueurActif'] ?? PieceSquadro::BLANC;
$etat = $_SESSION['etat'] ?? 'ChoixPiece';
$partieId = $_SESSION['partieId'] ?? 0;

// Création du générateur d'interface avec le plateau
$uiGenerator = new SquadroUIGenerator($action, $joueurActif, $plateau, 'traiteActionSquadro.php');

// Affichage de la page appropriée selon l'état
switch ($etat) {
    case 'ConsulterPartieVictoire':
        // Afficher la page de consultation de partie terminée
        $vainqueur = $_SESSION['vainqueur'] ?? $joueurActif;
        echo $uiGenerator->generatePageVictoire($vainqueur);
        break;

    case 'ConsulterPartieEnCours':
        // Afficher la page de consultation de partie en cours
        echo $uiGenerator->generatePageChoixPiece();
        break;

    case 'Erreur':
        // Afficher la page d'erreur
        $message = $_SESSION['erreur'] ?? "Une erreur inconnue s'est produite.";
        echo $uiGenerator->generatePageErreur($message);
        break;

    case 'Victoire':
        // Afficher la page de victoire
        $vainqueur = $_SESSION['vainqueur'] ?? $joueurActif;

        // Sauvegarder l'état de la partie en base de données
        if ($partieId > 0) {
            PDOSquadro::savePartieSquadro('finished', $_SESSION['action'], $partieId);
        }

        echo $uiGenerator->generatePageVictoire($vainqueur);
        break;

    case 'AttenteTour':
        // Afficher la page d'attente du tour de l'adversaire
        echo $uiGenerator->generatePageAttenteTour();
        break;

    case 'ConfirmationPiece':
        // Afficher la page de confirmation de déplacement
        if (isset($_SESSION['pieceSelectionnee'])) {
            $x = $_SESSION['pieceSelectionnee']['x'];
            $y = $_SESSION['pieceSelectionnee']['y'];
            echo $uiGenerator->generatePageConfirmationDeplacement($x, $y);
        } else {
            // Si aucune pièce n'est sélectionnée (ne devrait pas arriver normalement)
            echo $uiGenerator->generatePageErreur("Aucune pièce n'a été sélectionnée.");
        }
        break;

    case 'ChoixPiece':
    default:
        // Afficher la page de choix de pièce
        echo $uiGenerator->generatePageChoixPiece();
        break;
}

// Affichage du lien de retour à l'accueil
echo '<div style="margin-top: 20px; text-align: center;">
    <a href="home.php" style="padding: 10px 15px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 3px;">Retour à l\'accueil</a>
</div>';