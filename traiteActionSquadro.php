<?php
/**
 * Gestionnaire d'actions pour le jeu Squadro
 *
 * Ce script traite les actions des joueurs et met à jour l'état du jeu
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

/**
 * Initialisation d'une nouvelle partie
 */
function initialiserPartie(): void {
    $plateau = new PlateauSquadro();
    $action = new ActionSquadro($plateau);

    // Stockage de l'état du jeu dans la session
    $_SESSION['action'] = $action->toJson();
    $_SESSION['plateau'] = $plateau->toJson();
    $_SESSION['joueurActif'] = PieceSquadro::BLANC; // Le joueur blanc commence
    $_SESSION['etat'] = 'ChoixPiece'; // État initial
}

/**
 * Sauvegarde l'état actuel de la partie dans la base de données
 */
function sauvegarderEtatPartie(): void {
    if (isset($_SESSION['partieId']) && $_SESSION['partieId'] > 0) {
        $partieId = $_SESSION['partieId'];
        $actionJson = $_SESSION['action'];
        $gameStatus = 'waitingForPlayer';

        // Si la partie est terminée, mettre à jour le statut
        if ($_SESSION['etat'] === 'Victoire') {
            $gameStatus = 'finished';
        }

        // Initialiser la connexion à la base de données
        PDOSquadro::initPDO($_ENV['sgbd'], $_ENV['host'], $_ENV['database'], $_ENV['user'], $_ENV['password']);

        // Sauvegarder l'état
        PDOSquadro::savePartieSquadro($gameStatus, $actionJson, $partieId);
    }
}

/**
 * Traitement de l'action ChoisirPiece
 *
 * @param array $postData Données du formulaire
 * @return string|null Message d'erreur ou null si tout va bien
 */
function traiterChoisirPiece(array $postData): ?string {
    if (!isset($postData['piece'])) {
        return "Aucune pièce sélectionnée.";
    }

    // Récupération des coordonnées de la pièce
    list($x, $y) = explode(',', $postData['piece']);
    $x = (int)$x;
    $y = (int)$y;

    // Récupérer l'état du jeu
    $action = ActionSquadro::fromJson($_SESSION['action']);
    $plateau = PlateauSquadro::fromJson($_SESSION['plateau']);

    // Vérifier que la pièce appartient au joueur actif
    $piece = $plateau->getPiece($x, $y);
    if ($piece->getCouleur() !== $_SESSION['joueurActif']) {
        return "Cette pièce n'appartient pas au joueur actif.";
    }

    // Vérifier si la pièce est jouable
    if (!$action->estJouablePiece($x, $y)) {
        return "Cette pièce ne peut pas être jouée.";
    }

    // Mémoriser la position de la pièce sélectionnée
    $_SESSION['pieceSelectionnee'] = ['x' => $x, 'y' => $y];

    // Changer l'état de la partie
    $_SESSION['etat'] = 'ConfirmationPiece';

    return null;
}

/**
 * Traitement de l'action ConfirmerChoix
 *
 * @return string|null Message d'erreur ou null si tout va bien
 */
function traiterConfirmerChoix(): ?string {
    if (!isset($_SESSION['pieceSelectionnee'])) {
        return "Aucune pièce n'a été sélectionnée.";
    }

    $x = $_SESSION['pieceSelectionnee']['x'];
    $y = $_SESSION['pieceSelectionnee']['y'];

    // Récupérer l'état du jeu
    $action = ActionSquadro::fromJson($_SESSION['action']);
    $joueurActif = $_SESSION['joueurActif'];

    try {
        // Effectuer le déplacement avec la méthode jouePiece
        $action->jouePiece($x, $y);

        // Oublier les anciennes coordonnées
        unset($_SESSION['pieceSelectionnee']);

        // Vérifier si le joueur a gagné
        if ($action->remporteVictoire($joueurActif)) {
            // Stocker le vainqueur dans la session
            $_SESSION['vainqueur'] = $joueurActif;
            // Changer l'état
            $_SESSION['etat'] = 'Victoire';
        } else {
            // Passer au joueur suivant
            $_SESSION['joueurActif'] = ($joueurActif === PieceSquadro::BLANC)
                ? PieceSquadro::NOIR
                : PieceSquadro::BLANC;
            // Retourner à l'état de choix
            $_SESSION['etat'] = 'ChoixPiece';
        }

        // Mettre à jour l'état du jeu dans la session
        $_SESSION['action'] = $action->toJson();
        $_SESSION['plateau'] = $action->getPlateau()->toJson();

        // Sauvegarder l'état dans la base de données
        sauvegarderEtatPartie();

    } catch (Exception $e) {
        return "Erreur lors du déplacement : " . $e->getMessage();
    }

    return null;
}

/**
 * Traitement de l'action AnnulerChoix
 *
 * @return string|null Message d'erreur ou null si tout va bien
 */
function traiterAnnulerChoix(): ?string {
    // Oublier la pièce sélectionnée
    unset($_SESSION['pieceSelectionnee']);

    // Revenir à l'état de choix de pièce
    $_SESSION['etat'] = 'ChoixPiece';

    return null;
}

/**
 * Traitement de l'action Rejouer
 *
 * @return string|null Message d'erreur ou null si tout va bien
 */
function traiterRejouer(): ?string {
    // Rediriger vers la page d'accueil pour créer une nouvelle partie
    header('Location: home.php');
    exit;

    return null;
}

/**
 * Traitement de l'action RetournerHome
 *
 * @return string|null Message d'erreur ou null si tout va bien
 */
function traiterRetournerHome(): ?string {
    // Rediriger vers la page d'accueil
    header('Location: home.php');
    exit;

    return null;
}

// Traitement principal
$erreur = null;

// Traitement des différentes actions selon l'état
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['piece']) && $_SESSION['etat'] === 'ChoixPiece') {
        // Transition ChoisirPiece
        $erreur = traiterChoisirPiece($_POST);
    } elseif (isset($_POST['confirmer']) && $_SESSION['etat'] === 'ConfirmationPiece') {
        // Transition ConfirmerChoix
        $erreur = traiterConfirmerChoix();
    } elseif (isset($_POST['annuler']) && $_SESSION['etat'] === 'ConfirmationPiece') {
        // Transition AnnulerChoix
        $erreur = traiterAnnulerChoix();
    } elseif (isset($_POST['nouvelle_partie'])) {
        // Transition Rejouer (depuis Victoire ou Erreur)
        $erreur = traiterRejouer();
    } elseif (isset($_POST['retourner_home'])) {
        // Transition RetournerHome
        $erreur = traiterRetournerHome();
    } else {
        $erreur = "Action non reconnue ou non permise dans l'état actuel.";
    }

    // En cas d'erreur, définir l'état sur Erreur
    if ($erreur !== null) {
        $_SESSION['erreur'] = $erreur;
        $_SESSION['etat'] = 'Erreur';
    }
}

// Redirection vers index.php pour afficher l'état actuel
header('Location: index.php');
exit;