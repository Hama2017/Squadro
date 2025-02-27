<?php
require_once '../autoload.php';

class TestArrayPieceSquadro {

    public function testConstructionVide() {
        echo "=== Test de construction d'un tableau vide ===\n";

        $array = new ArrayPieceSquadro();
        echo "Nombre d'éléments initial : " . count($array) . " (attendu: 0)\n";

        echo "Test de construction vide réussi.\n\n";
    }

    public function testConstructionAvecPieces() {
        echo "=== Test de construction avec des pièces ===\n";

        $pieces = [
            PieceSquadro::initBlancEst(),
            PieceSquadro::initNoirNord(),
            PieceSquadro::initVide()
        ];

        $array = new ArrayPieceSquadro($pieces);
        echo "Nombre d'éléments : " . count($array) . " (attendu: 3)\n";

        echo "Premier élément : " . $array[0] . "\n";
        echo "Deuxième élément : " . $array[1] . "\n";
        echo "Troisième élément : " . $array[2] . "\n";

        echo "Test de construction avec des pièces réussi.\n\n";
    }

    public function testAjoutEtAccesElements() {
        echo "=== Test d'ajout et d'accès aux éléments ===\n";

        $array = new ArrayPieceSquadro();

        // Test d'ajout avec add()
        $array->add(PieceSquadro::initBlancEst());
        echo "Après ajout avec add(), count = " . count($array) . " (attendu: 1)\n";

        // Test d'ajout avec offsetSet
        $array[] = PieceSquadro::initNoirNord();
        echo "Après ajout avec [], count = " . count($array) . " (attendu: 2)\n";

        // Test d'ajout avec index spécifique
        $array[5] = PieceSquadro::initBlancOuest();
        echo "Après ajout à l'index 5, count = " . count($array) . " (attendu: 3)\n";

        // Test d'accès
        echo "Élément à l'index 0 : " . $array[0] . "\n";
        echo "Élément à l'index 1 : " . $array[1] . "\n";
        echo "Élément à l'index 5 : " . $array[5] . "\n";

        // Test offsetExists
        echo "Existe à l'index 0 : " . (isset($array[0]) ? "oui" : "non") . " (attendu: oui)\n";
        echo "Existe à l'index 2 : " . (isset($array[2]) ? "oui" : "non") . " (attendu: non)\n";

        echo "Tests d'ajout et d'accès réussis.\n\n";
    }

    public function testSuppression() {
        echo "=== Test de suppression d'éléments ===\n";

        $array = new ArrayPieceSquadro([
            PieceSquadro::initBlancEst(),
            PieceSquadro::initNoirNord(),
            PieceSquadro::initBlancOuest()
        ]);

        echo "Nombre d'éléments initial : " . count($array) . " (attendu: 3)\n";

        // Test offsetUnset
        unset($array[1]);
        echo "Après unset de l'index 1, count = " . count($array) . " (attendu: 2)\n";
        echo "Nouvel élément à l'index 1 : " . $array[1] . "\n";

        // Test remove
        $array->remove(0);
        echo "Après remove de l'index 0, count = " . count($array) . " (attendu: 1)\n";
        echo "Nouvel élément à l'index 0 : " . $array[0] . "\n";

        echo "Tests de suppression réussis.\n\n";
    }

    public function testToJsonFromJson() {
        echo "=== Test de sérialisation/désérialisation JSON ===\n";

        $array = new ArrayPieceSquadro([
            PieceSquadro::initBlancEst(),
            PieceSquadro::initNoirNord(),
            PieceSquadro::initVide()
        ]);

        echo "Tableau original : " . $array . "\n";

        $json = $array->toJson();
        echo "JSON : " . $json . "\n";

        $arrayReconstruit = ArrayPieceSquadro::fromJson($json);
        echo "Tableau reconstruit : " . $arrayReconstruit . "\n";
        echo "Nombre d'éléments dans le tableau reconstruit : " . count($arrayReconstruit) . " (attendu: 3)\n";

        echo "Sérialisation/désérialisation JSON réussie.\n\n";
    }

    public function testToString() {
        echo "=== Test de la méthode __toString ===\n";

        $array = new ArrayPieceSquadro([
            PieceSquadro::initBlancEst(),
            PieceSquadro::initNoirNord()
        ]);

        echo "Représentation en chaîne de caractères : " . $array . "\n";

        echo "Test de __toString réussi.\n\n";
    }

    public function executerTousLesTests() {
        echo "======= TESTS DE LA CLASSE ARRAYPIECESQUADRO =======\n\n";
        $this->testConstructionVide();
        $this->testConstructionAvecPieces();
        $this->testAjoutEtAccesElements();
        $this->testSuppression();
        $this->testToJsonFromJson();
        $this->testToString();
        echo "======= FIN DES TESTS DE ARRAYPIECESQUADRO =======\n\n";
    }
}

// Exécution des tests
$testArray = new TestArrayPieceSquadro();
$testArray->executerTousLesTests();