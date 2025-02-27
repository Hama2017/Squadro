<?php

require_once '../class/PieceSquadro.php';

echo "=== Test de la classe PieceSquadro ===\n\n";

// Test de création des différentes pièces
echo "1. Test de création des pièces:\n";
$pieceVide = PieceSquadro::initVide();
$pieceNoirNord = PieceSquadro::initNoirNord();
$pieceNoirSud = PieceSquadro::initNoirSud();
$pieceBlancEst = PieceSquadro::initBlancEst();
$pieceBlancOuest = PieceSquadro::initBlancOuest();
$pieceNeutre = PieceSquadro::initNeutre();

echo "Pièce Vide: " . $pieceVide->getCouleur() . ", " . $pieceVide->getDirection() . "\n";
echo "Pièce NoirNord: " . $pieceNoirNord->getCouleur() . ", " . $pieceNoirNord->getDirection() . "\n";
echo "Pièce NoirSud: " . $pieceNoirSud->getCouleur() . ", " . $pieceNoirSud->getDirection() . "\n";
echo "Pièce BlancEst: " . $pieceBlancEst->getCouleur() . ", " . $pieceBlancEst->getDirection() . "\n";
echo "Pièce BlancOuest: " . $pieceBlancOuest->getCouleur() . ", " . $pieceBlancOuest->getDirection() . "\n";
echo "Pièce Neutre: " . $pieceNeutre->getCouleur() . ", " . $pieceNeutre->getDirection() . "\n\n";

// Test d'inversion de direction
echo "2. Test d'inversion de direction:\n";
echo "Avant inversion - Pièce NoirNord: " . $pieceNoirNord->getDirection() . " (NORD)\n";
$pieceNoirNord->inverseDirection();
echo "Après inversion - Pièce NoirNord: " . $pieceNoirNord->getDirection() . " (SUD)\n\n";

echo "Avant inversion - Pièce BlancEst: " . $pieceBlancEst->getDirection() . " (EST)\n";
$pieceBlancEst->inverseDirection();
echo "Après inversion - Pièce BlancEst: " . $pieceBlancEst->getDirection() . " (OUEST)\n\n";

// Test de conversion JSON
echo "3. Test de conversion JSON:\n";
$pieceTest = PieceSquadro::initBlancEst();
$json = $pieceTest->toJson();
echo "JSON: " . $json . "\n";

$pieceReconstituee = PieceSquadro::fromJson($json);
echo "Pièce reconstituée: " . $pieceReconstituee->getCouleur() . ", " . $pieceReconstituee->getDirection() . "\n\n";

// Test de la méthode __toString (si elle existe - ça semble incomplet dans votre code)
echo "4. Test de la méthode __toString si disponible:\n";
if (method_exists($pieceTest, '__toString')) {
    echo $pieceTest . "\n\n";
} else {
    echo "La méthode __toString n'est pas disponible\n\n";
}

echo "=== Tests terminés pour PieceSquadro ===\n";