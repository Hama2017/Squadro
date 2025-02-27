<?php


require_once '../class/PieceSquadro.php';
require_once '../class/ArrayPieceSquadro.php';
require_once '../class/PlateauSquadro.php';

echo "=== Test de la classe PlateauSquadro ===\n";

// Création du plateau
$plateau = new PlateauSquadro();
echo "Plateau créé avec succès.\n";

// Test d'accès aux cases
echo "Test d'accès aux pièces...\n";
try {
    $piece = $plateau->getPiece(1, 0);
    echo "Pièce en (1,0) : " . $piece . "\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}

// Test de modification d'une case
echo "Test de modification d'une case...\n";
try {
    $nouvellePiece = PieceSquadro::initBlancOuest();
    $plateau->setPiece($nouvellePiece, 3, 3);
    echo "Nouvelle pièce placée en (3,3) : " . $plateau->getPiece(3, 3) . "\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}

// Test des lignes et colonnes jouables
echo "Lignes jouables : " . implode(", ", $plateau->getLignesJouables()) . "\n";
echo "Colonnes jouables : " . implode(", ", $plateau->getColonnesJouables()) . "\n";

// Test suppression d'une ligne jouable
echo "Suppression de la ligne jouable 3...\n";
$plateau->retireLigneJouable(3);
echo "Lignes jouables après suppression : " . implode(", ", $plateau->getLignesJouables()) . "\n";

// Test suppression d'une colonne jouable
echo "Suppression de la colonne jouable 4...\n";
$plateau->retireColonneJouable(4);
echo "Colonnes jouables après suppression : " . implode(", ", $plateau->getColonnesJouables()) . "\n";

// Test de conversion JSON
$json = $plateau->toJson();
echo "JSON du plateau : " . $json . "\n";

// Test de reconstruction depuis JSON
echo "Reconstruction du plateau depuis JSON...\n";
$nouveauPlateau = PlateauSquadro::fromJson($json);
echo "Plateau reconstruit avec succès.\n";

// Test de la méthode __toString()
echo "Affichage du plateau :\n";
echo $plateau;

echo "=== Fin des tests ===\n";



?>