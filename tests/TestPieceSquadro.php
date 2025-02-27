<?php
require_once '../autoload.php';

class TestPieceSquadro {
    public function testInitialisation() {
        echo "=== Test d'initialisation des pièces ===\n";

        $pieceVide = PieceSquadro::initVide();
        echo "Pièce vide: " . $pieceVide . "\n";

        $pieceNoirNord = PieceSquadro::initNoirNord();
        echo "Pièce noire vers le nord: " . $pieceNoirNord . "\n";

        $pieceNoirSud = PieceSquadro::initNoirSud();
        echo "Pièce noire vers le sud: " . $pieceNoirSud . "\n";

        $pieceBlancEst = PieceSquadro::initBlancEst();
        echo "Pièce blanche vers l'est: " . $pieceBlancEst . "\n";

        $pieceBlancOuest = PieceSquadro::initBlancOuest();
        echo "Pièce blanche vers l'ouest: " . $pieceBlancOuest . "\n";

        $pieceNeutre = PieceSquadro::initNeutre();
        echo "Pièce neutre: " . $pieceNeutre . "\n";
    }

    public function testGetters() {
        echo "\n=== Test des getters ===\n";

        $pieceNoirNord = PieceSquadro::initNoirNord();
        echo "Couleur de la pièce noire: " . $pieceNoirNord->getCouleur() . " (attendu: " . PieceSquadro::NOIR . ")\n";
        echo "Direction de la pièce noire: " . $pieceNoirNord->getDirection() . " (attendu: " . PieceSquadro::NORD . ")\n";

        $pieceBlancEst = PieceSquadro::initBlancEst();
        echo "Couleur de la pièce blanche: " . $pieceBlancEst->getCouleur() . " (attendu: " . PieceSquadro::BLANC . ")\n";
        echo "Direction de la pièce blanche: " . $pieceBlancEst->getDirection() . " (attendu: " . PieceSquadro::EST . ")\n";
    }

    public function testInverseDirection() {
        echo "\n=== Test inversion de direction ===\n";

        $pieceNoirNord = PieceSquadro::initNoirNord();
        echo "Direction avant inversion: " . $pieceNoirNord->getDirection() . " (NORD)\n";
        $pieceNoirNord->inverseDirection();
        echo "Direction après inversion: " . $pieceNoirNord->getDirection() . " (attendu: " . PieceSquadro::SUD . " - SUD)\n";

        $pieceBlancEst = PieceSquadro::initBlancEst();
        echo "Direction avant inversion: " . $pieceBlancEst->getDirection() . " (EST)\n";
        $pieceBlancEst->inverseDirection();
        echo "Direction après inversion: " . $pieceBlancEst->getDirection() . " (attendu: " . PieceSquadro::OUEST . " - OUEST)\n";

        $pieceNoirSud = PieceSquadro::initNoirSud();
        echo "Direction avant inversion: " . $pieceNoirSud->getDirection() . " (SUD)\n";
        $pieceNoirSud->inverseDirection();
        echo "Direction après inversion: " . $pieceNoirSud->getDirection() . " (attendu: " . PieceSquadro::NORD . " - NORD)\n";

        $pieceBlancOuest = PieceSquadro::initBlancOuest();
        echo "Direction avant inversion: " . $pieceBlancOuest->getDirection() . " (OUEST)\n";
        $pieceBlancOuest->inverseDirection();
        echo "Direction après inversion: " . $pieceBlancOuest->getDirection() . " (attendu: " . PieceSquadro::EST . " - EST)\n";
    }

    public function testJsonSerialization() {
        echo "\n=== Test sérialisation/désérialisation JSON ===\n";

        $pieceNoirNord = PieceSquadro::initNoirNord();
        $json = $pieceNoirNord->toJson();
        echo "JSON de la pièce noire vers le nord: " . $json . "\n";

        $pieceReconstruite = PieceSquadro::fromJson($json);
        echo "Pièce reconstruite à partir du JSON: " . $pieceReconstruite . "\n";
        echo "La désérialisation a " . ($pieceReconstruite->getCouleur() === $pieceNoirNord->getCouleur() &&
            $pieceReconstruite->getDirection() === $pieceNoirNord->getDirection() ?
                "réussi" : "échoué") . "\n";
    }

    public function testToString() {
        echo "\n=== Test méthode __toString ===\n";

        $pieceNoirNord = PieceSquadro::initNoirNord();
        echo "Représentation string de la pièce noire vers le nord: " . $pieceNoirNord . "\n";

        $pieceBlancEst = PieceSquadro::initBlancEst();
        echo "Représentation string de la pièce blanche vers l'est: " . $pieceBlancEst . "\n";

        $pieceVide = PieceSquadro::initVide();
        echo "Représentation string de la pièce vide: " . $pieceVide . "\n";
    }

    public function executerTous() {
        $this->testInitialisation();
        $this->testGetters();
        $this->testInverseDirection();
        $this->testJsonSerialization();
        $this->testToString();
    }
}

// Exécution des tests
$test = new TestPieceSquadro();
$test->executerTous();