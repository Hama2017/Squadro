<?php
/**
 * Gestionnaire de jeu Squadro
 *
 * Ce script gère le déroulement d'une partie de Squadro.
 * Il utilise les sessions pour stocker l'état du jeu.
 */

// Inclusion de l'autoload
require_once 'autoload.php';
require_once 'class/ui/autoload.php';

// Démarrage de la session
session_start();

/**
 * Initialisation d'une nouvelle partie
 */
function initialiserPartie(): void {
    $plateau = new PlateauSquadro();
    $action = new ActionSquadro($plateau);

    // Stockage de l'état du jeu dans la session
    $_SESSION['action'] = $action->toJson();
    // Stocker également le plateau pour l'interface
    $_SESSION['plateau'] = $plateau->toJson();
    $_SESSION['joueurActif'] = PieceSquadro::BLANC; // Le joueur blanc commence
}

/**
 * Traitement des actions du joueur
 *
 * @return string|null Message d'erreur ou null si tout va bien
 */
function traiterActions(): ?string {
    // Création d'une nouvelle partie si demandé
    if (isset($_POST['nouvelle_partie'])) {
        initialiserPartie();
        return null;
    }

    // Si aucune partie n'est en cours, en initialiser une
    if (!isset($_SESSION['action']) || !isset($_SESSION['plateau'])) {
        initialiserPartie();
        return null;
    }

    // Récupération de l'état du jeu
    $action = ActionSquadro::fromJson($_SESSION['action']);
    $plateau = PlateauSquadro::fromJson($_SESSION['plateau']);
    $joueurActif = $_SESSION['joueurActif'];

    // Traitement de la confirmation de déplacement
    if (isset($_POST['confirmer'], $_POST['x'], $_POST['y'])) {
        $x = (int)$_POST['x'];
        $y = (int)$_POST['y'];

        try {
            // Vérifier si la pièce est jouable
            if (!$action->estJouablePiece($x, $y)) {
                return "Cette pièce ne peut pas être jouée.";
            }

            // Effectuer le déplacement
            $action->jouePiece($x, $y);

            // Vérifier si le joueur a gagné
            if ($action->remporteVictoire($joueurActif)) {
                // Stocker le vainqueur dans la session
                $_SESSION['vainqueur'] = $joueurActif;
            } else {
                // Passer au joueur suivant
                $_SESSION['joueurActif'] = ($joueurActif === PieceSquadro::BLANC)
                    ? PieceSquadro::NOIR
                    : PieceSquadro::BLANC;
            }

            // Mettre à jour l'état du jeu dans la session
            $_SESSION['action'] = $action->toJson();
            // Mettre à jour le plateau car il a été modifié par le mouvement
            $_SESSION['plateau'] = $plateau->toJson();

        } catch (Exception $e) {
            return "Erreur lors du déplacement : " . $e->getMessage();
        }
    }

    return null;
}

// Traitement des actions du joueur
$erreur = traiterActions();

// Récupération de l'état du jeu pour l'affichage
$action = isset($_SESSION['action'])
    ? ActionSquadro::fromJson($_SESSION['action'])
    : null;
$plateau = isset($_SESSION['plateau'])
    ? PlateauSquadro::fromJson($_SESSION['plateau'])
    : null;
$joueurActif = $_SESSION['joueurActif'] ?? PieceSquadro::BLANC;

// Création du générateur d'interface avec le plateau
$uiGenerator = new SquadroUIGenerator($action, $joueurActif, $plateau);

// Affichage de la page appropriée
if ($erreur !== null) {
    // Afficher une page d'erreur
    echo $uiGenerator->generatePageErreur($erreur);
} elseif (isset($_SESSION['vainqueur'])) {
    // Afficher la page de victoire
    echo $uiGenerator->generatePageVictoire($_SESSION['vainqueur']);
} elseif (isset($_POST['piece'])) {
    // Traitement de la sélection d'une pièce
    list($x, $y) = explode(',', $_POST['piece']);
    echo $uiGenerator->generatePageConfirmationDeplacement((int)$x, (int)$y);
} else {
    // Afficher la page de choix de pièce
    echo $uiGenerator->generatePageChoixPiece();
}