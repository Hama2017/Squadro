<?php
// Fichier: tests/TestArrayPieceSquadro.php

require_once '../class/PieceSquadro.php';
require_once '../class/ArrayPieceSquadro.php';

echo "=== Test de la classe ArrayPieceSquadro ===\n\n";

// Création de quelques pièces pour les tests
$pieceNoirNord = PieceSquadro::initNoirNord();
$pieceBlancEst = PieceSquadro::initBlancEst();
$pieceVide = PieceSquadro::initVide();

// 1. Test du constructeur et ajout de pièces
echo "1. Test du constructeur et ajout de pièces:\n";
$array = new ArrayPieceSquadro();
echo "Nombre initial d'éléments: " . count($array) . "\n";

$array->add($pieceNoirNord);
$array->add($pieceBlancEst);
$array->add($pieceVide);

echo "Nombre d'éléments après ajout: " . count($array) . "\n\n";

// 2. Test de l'accès aux éléments (ArrayAccess)
echo "2. Test d'accès aux éléments (ArrayAccess):\n";
echo "Élément à l'index 0: Couleur=" . $array[0]->getCouleur() . ", Direction=" . $array[0]->getDirection() . "\n";
echo "Élément à l'index 1: Couleur=" . $array[1]->getCouleur() . ", Direction=" . $array[1]->getDirection() . "\n";

// Test de la modification d'un élément
$array[1] = PieceSquadro::initNoirSud();
echo "Après modification - Élément à l'index 1: Couleur=" . $array[1]->getCouleur() . ", Direction=" . $array[1]->getDirection() . "\n\n";

// 3. Test de suppression d'éléments
echo "3. Test de suppression d'éléments:\n";
echo "Nombre d'éléments avant suppression: " . count($array) . "\n";
$array->remove(1);
echo "Nombre d'éléments après suppression: " . count($array) . "\n";
echo "Nouvel élément à l'index 1: Couleur=" . $array[1]->getCouleur() . ", Direction=" . $array[1]->getDirection() . "\n\n";

// 4. Test de conversion JSON
echo "4. Test de conversion JSON:\n";
$json = $array->toJson();
echo "JSON: " . $json . "\n";

$arrayReconstitue = ArrayPieceSquadro::fromJson($json);
echo "Array reconstitué, nombre d'éléments: " . count($arrayReconstitue) . "\n";
echo "Premier élément: Couleur=" . $arrayReconstitue[0]->getCouleur() . ", Direction=" . $arrayReconstitue[0]->getDirection() . "\n\n";

// 5. Test de la méthode __toString
echo "5. Test de la méthode __toString:\n";
echo $array . "\n\n";

// 6. Test des vérifications d'existence
echo "6. Test des vérifications d'existence:\n";
echo "Index 0 existe: " . (isset($array[0]) ? "oui" : "non") . "\n";
echo "Index 5 existe: " . (isset($array[5]) ? "oui" : "non") . "\n\n";

// 7. Test de l'ajout avec offsetSet sans index
echo "7. Test de l'ajout avec offsetSet sans index:\n";
$array[] = PieceSquadro::initBlancOuest();
echo "Nombre d'éléments après ajout: " . count($array) . "\n";
echo "Nouvel élément ajouté: Couleur=" . $array[count($array)-1]->getCouleur() . ", Direction=" . $array[count($array)-1]->getDirection() . "\n\n";

echo "=== Tests terminés pour ArrayPieceSquadro ===\n";