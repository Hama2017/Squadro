<?php
/**
 * Gestion des actions du jeu Squadro
 *
 * Ce script traite les actions des joueurs et met à jour l'état du jeu.
 * Il implémente l'automate décrit dans l'énoncé pour la gestion des transitions.
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
    $_SESSION['plateau'] = $plateau->toJson();
    $_SESSION['joueurActif'] = PieceSquadro::BLANC; // Le joueur blanc commence
    $_SESSION['etat'] = 'ChoixPiece'; // État initial
}

/**
 * Effectue le déplacement manuel d'une pièce
 * Cette fonction est utilisée comme solution de secours si jouePiece() ne fonctionne pas
 *
 * @param PlateauSquadro $plateau Le plateau de jeu
 * @param int $x Coordonnée X de départ
 * @param int $y Coordonnée Y de départ
 * @param int $destX Coordonnée X de destination
 * @param int $destY Coordonnée Y de destination
 */
function deplacerPieceManuel(PlateauSquadro $plateau, int $x, int $y, int $destX, int $destY): void {
    // Récupérer la pièce à déplacer
    $piece = $plateau->getPiece($x, $y);
    $couleur = $piece->getCouleur();
    $direction = $piece->getDirection();

    // Vider la case d'origine
    $plateau->setPiece(PieceSquadro::initVide(), $x, $y);

    // Créer une nouvelle pièce avec les mêmes propriétés
    $nouvellePiece = null;
    if ($couleur === PieceSquadro::BLANC) {
        $nouvellePiece = ($direction === PieceSquadro::EST) ?
            PieceSquadro::initBlancEst() : PieceSquadro::initBlancOuest();
    } else {
        $nouvellePiece = ($direction === PieceSquadro::NORD) ?
            PieceSquadro::initNoirNord() : PieceSquadro::initNoirSud();
    }

    // Placer la pièce à la destination
    $plateau->setPiece($nouvellePiece, $destX, $destY);

    // Vérifier si c'est une case de retournement
    if (($destX === 0 || $destX === 6 || $destY === 0 || $destY === 6) &&
        $plateau->getPiece($destX, $destY)->getCouleur() !== PieceSquadro::NEUTRE) {

        // Inverser la direction de la pièce
        $nouvelleDirection = null;
        if ($direction === PieceSquadro::EST) {
            $nouvelleDirection = PieceSquadro::OUEST;
        } else if ($direction === PieceSquadro::OUEST) {
            $nouvelleDirection = PieceSquadro::EST;
        } else if ($direction === PieceSquadro::NORD) {
            $nouvelleDirection = PieceSquadro::SUD;
        } else {
            $nouvelleDirection = PieceSquadro::NORD;
        }

        // Créer une nouvelle pièce avec la direction inversée
        if ($couleur === PieceSquadro::BLANC) {
            $pieceRetournee = ($nouvelleDirection === PieceSquadro::EST) ?
                PieceSquadro::initBlancEst() : PieceSquadro::initBlancOuest();
        } else {
            $pieceRetournee = ($nouvelleDirection === PieceSquadro::NORD) ?
                PieceSquadro::initNoirNord() : PieceSquadro::initNoirSud();
        }

        // Remplacer la pièce sur le plateau
        $plateau->setPiece($pieceRetournee, $destX, $destY);
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

    $action = ActionSquadro::fromJson($_SESSION['action']);
    $plateau = PlateauSquadro::fromJson($_SESSION['plateau']);

    // Vérifier que la pièce appartient au joueur actif
    $piece = $plateau->getPiece($x, $y);
    if ($piece->getCouleur() !== $_SESSION['joueurActif']) {
        return "Cette pièce n'appartient pas au joueur actif.";
    }

    // Calculer la destination pour vérification
    try {
        $destCoords = $plateau->getCoordDestination($x, $y);
        $destX = $destCoords[0];
        $destY = $destCoords[1];

        // Vérifier si la destination est valide
        if ($destX < 0 || $destX >= 7 || $destY < 0 || $destY >= 7) {
            return "Destination invalide : hors plateau.";
        }

        // Vérifier si la destination est libre
        $pieceDestination = $plateau->getPiece($destX, $destY);
        if ($pieceDestination->getCouleur() !== PieceSquadro::VIDE) {
            return "Destination invalide : case occupée.";
        }
    } catch (Exception $e) {
        return "Erreur lors du calcul de la destination : " . $e->getMessage();
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

    $action = ActionSquadro::fromJson($_SESSION['action']);
    $plateau = PlateauSquadro::fromJson($_SESSION['plateau']);
    $joueurActif = $_SESSION['joueurActif'];

    try {
        // Récupérer les informations avant déplacement
        $piece = $plateau->getPiece($x, $y);
        $destCoords = $plateau->getCoordDestination($x, $y);
        $destX = $destCoords[0];
        $destY = $destCoords[1];

        // Essayer de jouer la pièce avec la méthode ActionSquadro
        try {
            $action->jouePiece($x, $y);

            // Vérifier si le déplacement a été effectué
            $caseOrigineEstVide = ($plateau->getPiece($x, $y)->getCouleur() === PieceSquadro::VIDE);
            $caseDestinationOccupee = ($plateau->getPiece($destX, $destY)->getCouleur() === $piece->getCouleur());

            // Si le déplacement n'a pas été effectué, le faire manuellement
            if (!$caseOrigineEstVide || !$caseDestinationOccupee) {
                error_log("Déplacement manuel nécessaire");
                deplacerPieceManuel($plateau, $x, $y, $destX, $destY);

                // Recréer l'action avec le plateau mis à jour
                $action = new ActionSquadro($plateau);
            }
        } catch (Exception $e) {
            // Si jouePiece échoue, utiliser le déplacement manuel
            error_log("jouePiece a échoué: " . $e->getMessage());
            deplacerPieceManuel($plateau, $x, $y, $destX, $destY);

            // Recréer l'action avec le plateau mis à jour
            $action = new ActionSquadro($plateau);
        }

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
        $_SESSION['plateau'] = $plateau->toJson();

        // Debug - État après traitement
        error_log("État après traitement: Case origine ($x,$y) = " .
            $plateau->getPiece($x, $y)->getCouleur() . ", Case dest ($destX,$destY) = " .
            $plateau->getPiece($destX, $destY)->getCouleur());

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
    // Initialiser une nouvelle partie
    initialiserPartie();
    return null;
}

// Traitement principal
$erreur = null;

// Si aucune partie n'est en cours, initialiser une nouvelle partie
if (!isset($_SESSION['etat'])) {
    initialiserPartie();
}

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
    } else {
        $erreur = "Action non reconnue ou non permise dans l'état actuel.";
    }

    // En cas d'erreur, définir l'état sur Erreur
    if ($erreur !== null) {
        $_SESSION['erreur'] = $erreur;
        $_SESSION['etat'] = 'Erreur';
    }
}

// Redirection vers index.php
header('Location: index.php');
exit;