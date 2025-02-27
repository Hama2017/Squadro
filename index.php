<?php
/**
 * Interface utilisateur du jeu Squadro
 *
 * Ce script affiche l'interface utilisateur adaptée à l'état du jeu.
 */

// Inclusion de l'autoload
require_once 'autoload.php';
require_once 'class/ui/autoload.php';

// Démarrage de la session
session_start();



// Si aucune partie n'est en cours, rediriger vers traiteActionSquadro.php pour initialisation
if (!isset($_SESSION['etat'])) {
    header('Location: traiteActionSquadro.php');
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

// Création du générateur d'interface avec le plateau
$uiGenerator = new SquadroUIGenerator($action, $joueurActif, $plateau);


// Ajouter dans index.php avant l'affichage de la page
if ($etat === 'ConfirmationPiece' && isset($_SESSION['pieceSelectionnee'])) {
    $x = $_SESSION['pieceSelectionnee']['x'];
    $y = $_SESSION['pieceSelectionnee']['y'];

    echo "<div style='background:#eee;padding:10px;margin:10px;'>";
    echo "<h3>Débogage déplacement</h3>";

    $piece = $plateau->getPiece($x, $y);
    echo "Pièce: Couleur=" . $piece->getCouleur() .
        ", Direction=" . $piece->getDirection() . "<br>";

    $vitesse = 0;
    if ($piece->getCouleur() === PieceSquadro::BLANC) {
        $vitesse = $piece->getDirection() === PieceSquadro::EST ?
            PlateauSquadro::BLANC_V_ALLER[$x] : PlateauSquadro::BLANC_V_RETOUR[$x];
    } else {
        $vitesse = $piece->getDirection() === PieceSquadro::NORD ?
            PlateauSquadro::NOIR_V_ALLER[$y] : PlateauSquadro::NOIR_V_RETOUR[$y];
    }

    echo "Vitesse calculée: " . $vitesse . "<br>";

    try {
        $destCoords = $plateau->getCoordDestination($x, $y);
        echo "Destination calculée: (" . $destCoords[0] . ", " . $destCoords[1] . ")<br>";
    } catch (Exception $e) {
        echo "Erreur de calcul de destination: " . $e->getMessage() . "<br>";
    }

    echo "</div>";
}

// Affichage de la page appropriée selon l'état
switch ($etat) {
    case 'Erreur':
        // Afficher la page d'erreur
        $message = $_SESSION['erreur'] ?? "Une erreur inconnue s'est produite.";
        echo $uiGenerator->generatePageErreur($message);
        break;

    case 'Victoire':
        // Afficher la page de victoire
        $vainqueur = $_SESSION['vainqueur'] ?? $joueurActif;
        echo $uiGenerator->generatePageVictoire($vainqueur);
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

// Débogage du plateau
echo "<div style='background:#eee;padding:10px;margin:10px;'>";
echo "<h3>État du plateau</h3>";
echo "<pre>";
for ($i = 0; $i < 7; $i++) {
    for ($j = 0; $j < 7; $j++) {
        $piece = $plateau->getPiece($i, $j);
        $symbol = '·'; // case vide par défaut

        if ($piece->getCouleur() === PieceSquadro::BLANC) {
            $symbol = ($piece->getDirection() === PieceSquadro::EST) ? '→' : '←';
        } elseif ($piece->getCouleur() === PieceSquadro::NOIR) {
            $symbol = ($piece->getDirection() === PieceSquadro::NORD) ? '↑' : '↓';
        } elseif ($piece->getCouleur() === PieceSquadro::NEUTRE) {
            $symbol = '×';
        }

        echo $symbol . ' ';
    }
    echo "\n";
}
echo "</pre>";
echo "</div>";