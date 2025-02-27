<?php

require_once '../autoload.php';

class TestPlateauSquadro {

    public function testConstruction() {
        echo "=== Test de construction du plateau ===\n";

        $plateau = new PlateauSquadro();
        echo "Plateau initialisé :\n";
        echo $plateau . "\n";

        echo "Test de construction réussi.\n\n";
    }

    public function testGetPlateau() {
        echo "=== Test de getPlateau ===\n";

        $plateau = new PlateauSquadro();
        $tab = $plateau->getPlateau();

        echo "Dimension du tableau : " . count($tab) . "x" . count($tab[0]) . " (attendu: 7x7)\n";
        echo "Test de getPlateau réussi.\n\n";
    }

    public function testGetSetPiece() {
        echo "=== Test de getPiece et setPiece ===\n";

        $plateau = new PlateauSquadro();

        // Test de getPiece
        echo "Pièce à (0, 0) : " . $plateau->getPiece(0, 0) . " (attendu: Neutre)\n";
        echo "Pièce à (1, 0) : " . $plateau->getPiece(1, 0) . " (attendu: Blanc Ouest)\n";
        echo "Pièce à (6, 1) : " . $plateau->getPiece(6, 1) . " (attendu: Noir Sud)\n";
        echo "Pièce à (3, 3) : " . $plateau->getPiece(3, 3) . " (attendu: Vide)\n";

        // Test de setPiece
        $nouvellesPieces = [
            [2, 2, PieceSquadro::initBlancEst()],
            [3, 3, PieceSquadro::initNoirNord()],
            [4, 4, PieceSquadro::initVide()]
        ];

        foreach ($nouvellesPieces as $info) {
            list($x, $y, $piece) = $info;
            $plateau->setPiece($piece, $x, $y);
            echo "Nouvelle pièce placée à ($x, $y) : " . $plateau->getPiece($x, $y) . "\n";
        }

        try {
            $plateau->getPiece(7, 7);
            echo "ERREUR: Devrait lever une exception pour des coordonnées invalides\n";
        } catch (InvalidArgumentException $e) {
            echo "Exception bien levée pour coordonnées invalides : " . $e->getMessage() . "\n";
        }

        echo "Tests de getPiece et setPiece réussis.\n\n";
    }

    public function testLignesColonnesJouables() {
        echo "=== Test des lignes et colonnes jouables ===\n";

        $plateau = new PlateauSquadro();

        echo "Lignes jouables : [" . implode(", ", $plateau->getLignesJouables()) . "] (attendu: 1, 2, 3, 4, 5)\n";
        echo "Colonnes jouables : [" . implode(", ", $plateau->getColonnesJouables()) . "] (attendu: 1, 2, 3, 4, 5)\n";

        $plateau->retireLigneJouable(3);
        echo "Après retrait de la ligne 3, lignes jouables : [" . implode(", ", $plateau->getLignesJouables()) . "] (attendu: 1, 2, 4, 5)\n";

        $plateau->retireColonneJouable(2);
        echo "Après retrait de la colonne 2, colonnes jouables : [" . implode(", ", $plateau->getColonnesJouables()) . "] (attendu: 1, 3, 4, 5)\n";

        echo "Tests des lignes et colonnes jouables réussis.\n\n";
    }

    public function testGetCoordDestination() {
        echo "=== Test de getCoordDestination ===\n";

        $plateau = new PlateauSquadro();

        // Pièce blanche à (1, 0) (direction Ouest)
        $coords = $plateau->getCoordDestination(1, 0);
        echo "Destination de la pièce à (1, 0) : (" . $coords[0] . ", " . $coords[1] . ") (attendu: (1, -1))\n";

        // Plaçons une pièce blanche en direction Est
        $plateau->setPiece(PieceSquadro::initBlancEst(), 2, 2);
        $coords = $plateau->getCoordDestination(2, 2);
        echo "Destination de la pièce à (2, 2) : (" . $coords[0] . ", " . $coords[1] . ") (attendu: (2, 5))\n";

        // Pièce noire à (6, 1) (direction Sud)
        $coords = $plateau->getCoordDestination(6, 1);
        echo "Destination de la pièce à (6, 1) : (" . $coords[0] . ", " . $coords[1] . ") (attendu: (9, 1))\n";

        // Plaçons une pièce noire en direction Nord
        $plateau->setPiece(PieceSquadro::initNoirNord(), 4, 3);
        $coords = $plateau->getCoordDestination(4, 3);
        echo "Destination de la pièce à (4, 3) : (" . $coords[0] . ", " . $coords[1] . ") (attendu: (2, 3))\n";

        echo "Tests de getCoordDestination réussis.\n\n";
    }

    public function testGetDestination() {
        echo "=== Test de getDestination ===\n";

        $plateau = new PlateauSquadro();

        // Plaçons quelques pièces pour tester
        $plateau->setPiece(PieceSquadro::initBlancEst(), 2, 2);
        $plateau->setPiece(PieceSquadro::initNoirNord(), 4, 3);

        try {
            $pieceDestination = $plateau->getDestination(2, 2);
            echo "Pièce à la destination de (2, 2) : " . $pieceDestination . "\n";
        } catch (InvalidArgumentException $e) {
            echo "Exception levée pour coordonnées de destination hors limites : " . $e->getMessage() . "\n";
        }

        try {
            $pieceDestination = $plateau->getDestination(4, 3);
            echo "Pièce à la destination de (4, 3) : " . $pieceDestination . "\n";
        } catch (InvalidArgumentException $e) {
            echo "Exception levée pour coordonnées de destination hors limites : " . $e->getMessage() . "\n";
        }

        echo "Tests de getDestination réussis.\n\n";
    }

    public function testToJsonFromJson() {
        echo "=== Test de sérialisation/désérialisation JSON ===\n";

        $plateau = new PlateauSquadro();

        // Modification du plateau
        $plateau->setPiece(PieceSquadro::initBlancEst(), 2, 2);
        $plateau->setPiece(PieceSquadro::initNoirNord(), 4, 3);
        $plateau->retireLigneJouable(3);
        $plateau->retireColonneJouable(2);

        echo "Plateau original :\n" . $plateau . "\n";

        $json = $plateau->toJson();
        echo "JSON : " . substr($json, 0, 100) . "... (tronqué)\n";

        $plateauReconstruit = PlateauSquadro::fromJson($json);
        echo "Plateau reconstruit :\n" . $plateauReconstruit . "\n";

        echo "Lignes jouables du plateau reconstruit : [" . implode(", ", $plateauReconstruit->getLignesJouables()) . "]\n";
        echo "Colonnes jouables du plateau reconstruit : [" . implode(", ", $plateauReconstruit->getColonnesJouables()) . "]\n";

        echo "Sérialisation/désérialisation JSON réussie.\n\n";
    }

    public function testToString() {
        echo "=== Test de la méthode __toString ===\n";

        $plateau = new PlateauSquadro();
        echo "Représentation en chaîne de caractères du plateau :\n" . $plateau . "\n";

        echo "Test de __toString réussi.\n\n";
    }

    public function executerTousLesTests() {
        echo "======= TESTS DE LA CLASSE PLATEAUSQUADRO =======\n\n";
        $this->testConstruction();
        $this->testGetPlateau();
        $this->testGetSetPiece();
        $this->testLignesColonnesJouables();
        $this->testGetCoordDestination();
        $this->testGetDestination();
        $this->testToJsonFromJson();
        $this->testToString();
        echo "======= FIN DES TESTS DE PLATEAUSQUADRO =======\n\n";
    }
}

// Exécution des tests
$testPlateau = new TestPlateauSquadro();
$testPlateau->executerTousLesTests();