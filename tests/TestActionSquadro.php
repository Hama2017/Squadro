<?php
require_once '../autoload.php';


class TestActionSquadro {

    public function testConstruction() {
        echo "=== Test de construction d'ActionSquadro ===\n";

        $plateau = new PlateauSquadro();
        $action = new ActionSquadro($plateau);

        echo "Action initialisée.\n";
        echo "Test de construction réussi.\n\n";
    }

    public function testEstJouablePiece() {
        echo "=== Test de estJouablePiece ===\n";

        $plateau = new PlateauSquadro();
        $action = new ActionSquadro($plateau);

        // Test sur une pièce blanche à la position initiale (1, 0)
        $estJouable = $action->estJouablePiece(1, 0);
        echo "La pièce à (1, 0) est jouable : " . ($estJouable ? "oui" : "non") . " (attendu: non - hors limites)\n";

        // Placement d'une pièce blanche dans une position jouable
        $plateau->setPiece(PieceSquadro::initBlancEst(), 2, 2);
        $estJouable = $action->estJouablePiece(2, 2);
        echo "La pièce à (2, 2) est jouable : " . ($estJouable ? "oui" : "non") . " (attendu: oui)\n";

        // Test sur une case vide
        $estJouable = $action->estJouablePiece(3, 3);
        echo "La case vide à (3, 3) est jouable : " . ($estJouable ? "oui" : "non") . " (attendu: non)\n";

        // Placement d'une pièce avec destination déjà occupée
        $plateau->setPiece(PieceSquadro::initBlancEst(), 2, 3);
        $plateau->setPiece(PieceSquadro::initNoirNord(), 2, 6);
        $estJouable = $action->estJouablePiece(2, 3);
        echo "La pièce à (2, 3) avec destination occupée est jouable : " . ($estJouable ? "oui" : "non") . " (attendu: non)\n";

        echo "Tests de estJouablePiece réussis.\n\n";
    }

    public function testJouePiece() {
        echo "=== Test de jouePiece ===\n";

        $plateau = new PlateauSquadro();
        $action = new ActionSquadro($plateau);

        // Préparation d'une situation où une pièce peut être jouée
        $plateau->setPiece(PieceSquadro::initBlancEst(), 2, 2);

        try {
            echo "État du plateau avant le mouvement :\n" . $plateau . "\n";
            $action->jouePiece(2, 2);
            echo "État du plateau après le mouvement :\n" . $plateau . "\n";
            echo "Test de jouePiece réussi - pièce déplacée avec succès.\n";
        } catch (Exception $e) {
            echo "ERREUR lors du déplacement de la pièce : " . $e->getMessage() . "\n";
        }

        // Test avec une pièce non jouable
        try {
            $action->jouePiece(3, 3);
            echo "ERREUR: Devrait lever une exception pour une pièce non jouable\n";
        } catch (Exception $e) {
            echo "Exception bien levée pour une pièce non jouable : " . $e->getMessage() . "\n";
        }

        echo "Tests de jouePiece réussis.\n\n";
    }

    public function testReculePiece() {
        echo "=== Test de reculePiece ===\n";

        $plateau = new PlateauSquadro();
        $action = new ActionSquadro($plateau);

        // Placement de pièces à reculer
        $plateau->setPiece(PieceSquadro::initBlancEst(), 3, 3);
        $plateau->setPiece(PieceSquadro::initNoirNord(), 4, 4);

        echo "État du plateau avant le recul des pièces :\n" . $plateau . "\n";

        // Recul d'une pièce blanche
        $action->reculePiece(3, 3);
        echo "Après recul de la pièce blanche à (3, 3) :\n" . $plateau . "\n";

        // Recul d'une pièce noire
        $action->reculePiece(4, 4);
        echo "Après recul de la pièce noire à (4, 4) :\n" . $plateau . "\n";

        echo "Tests de reculePiece réussis.\n\n";
    }

    public function testSortPiece() {
        echo "=== Test de sortPiece ===\n";

        $plateau = new PlateauSquadro();
        $action = new ActionSquadro($plateau);

        echo "Lignes jouables avant sortie : [" . implode(", ", $plateau->getLignesJouables()) . "]\n";
        echo "Colonnes jouables avant sortie : [" . implode(", ", $plateau->getColonnesJouables()) . "]\n";

        // Sortie d'une pièce blanche (ligne)
        $action->sortPiece(PieceSquadro::BLANC, 3);
        echo "Après sortie d'une pièce blanche (ligne 3), lignes jouables : [" . implode(", ", $plateau->getLignesJouables()) . "]\n";

        // Sortie d'une pièce noire (colonne)
        $action->sortPiece(PieceSquadro::NOIR, 2);
        echo "Après sortie d'une pièce noire (colonne 2), colonnes jouables : [" . implode(", ", $plateau->getColonnesJouables()) . "]\n";

        echo "Tests de sortPiece réussis.\n\n";
    }

    public function testRemporteVictoire() {
        echo "=== Test de remporteVictoire ===\n";

        $plateau = new PlateauSquadro();
        $action = new ActionSquadro($plateau);

        // Test initial - aucun joueur n'a gagné
        $victoireBlanc = $action->remporteVictoire(PieceSquadro::BLANC);
        $victoireNoir = $action->remporteVictoire(PieceSquadro::NOIR);
        echo "Victoire blanc au départ : " . ($victoireBlanc ? "oui" : "non") . " (attendu: non)\n";
        echo "Victoire noir au départ : " . ($victoireNoir ? "oui" : "non") . " (attendu: non)\n";

        // Simulation d'une situation où les blancs ont presque gagné (il ne reste qu'une ligne)
        $plateau->retireLigneJouable(1);
        $plateau->retireLigneJouable(2);
        $plateau->retireLigneJouable(4);
        $plateau->retireLigneJouable(5);

        $victoireBlanc = $action->remporteVictoire(PieceSquadro::BLANC);
        echo "Victoire blanc avec une seule ligne restante : " . ($victoireBlanc ? "oui" : "non") . " (attendu: oui)\n";

        // Simulation d'une situation où les noirs ont gagné (aucune colonne restante)
        $plateau->retireColonneJouable(1);
        $plateau->retireColonneJouable(2);
        $plateau->retireColonneJouable(3);
        $plateau->retireColonneJouable(4);
        $plateau->retireColonneJouable(5);

        $victoireNoir = $action->remporteVictoire(PieceSquadro::NOIR);
        echo "Victoire noir avec aucune colonne restante : " . ($victoireNoir ? "oui" : "non") . " (attendu: oui)\n";

        echo "Tests de remporteVictoire réussis.\n\n";
    }

    public function testGererCaptures() {
        echo "=== Test de gererCaptures (via jouePiece) ===\n";

        $plateau = new PlateauSquadro();
        $action = new ActionSquadro($plateau);

        // Plaçons des pièces pour une capture
        // Une pièce blanche qui va capturer une pièce noire
        $plateau->setPiece(PieceSquadro::initBlancEst(), 3, 1);
        $plateau->setPiece(PieceSquadro::initNoirSud(), 3, 3);

        echo "État du plateau avant capture :\n" . $plateau . "\n";

        try {
            // La pièce blanche va se déplacer et capturer la pièce noire
            $action->jouePiece(3, 1);
            echo "État du plateau après capture :\n" . $plateau . "\n";
            echo "La pièce noire devrait avoir été reculée à sa position initiale.\n";
        } catch (Exception $e) {
            echo "ERREUR lors de la capture : " . $e->getMessage() . "\n";
        }

        // Maintenant testons la capture par une pièce noire
        $plateau = new PlateauSquadro(); // Réinitialisation du plateau
        $action = new ActionSquadro($plateau);

        // Une pièce noire qui va capturer une pièce blanche
        $plateau->setPiece(PieceSquadro::initNoirNord(), 5, 2);
        $plateau->setPiece(PieceSquadro::initBlancEst(), 3, 2);

        echo "État du plateau avant capture :\n" . $plateau . "\n";

        try {
            // La pièce noire va se déplacer et capturer la pièce blanche
            $action->jouePiece(5, 2);
            echo "État du plateau après capture :\n" . $plateau . "\n";
            echo "La pièce blanche devrait avoir été reculée à sa position initiale.\n";
        } catch (Exception $e) {
            echo "ERREUR lors de la capture : " . $e->getMessage() . "\n";
        }

        echo "Tests de capture réussis.\n\n";
    }

    public function testCasesRetournementFinParcours() {
        echo "=== Test de retournement et fin de parcours (via jouePiece) ===\n";

        $plateau = new PlateauSquadro();
        $action = new ActionSquadro($plateau);

        // Plaçons une pièce blanche près d'un point de retournement
        $pieceBlanche = PieceSquadro::initBlancEst();
        $plateau->setPiece($pieceBlanche, 2, 5);

        echo "Direction de la pièce blanche avant mouvement : " . $pieceBlanche->getDirection() . " (Est)\n";
        echo "État du plateau avant mouvement :\n" . $plateau . "\n";

        try {
            // La pièce va se déplacer et atteindre un point de retournement
            $action->jouePiece(2, 5);
            echo "État du plateau après mouvement :\n" . $plateau . "\n";
            echo "Direction de la pièce blanche après mouvement : " . $plateau->getPiece(2, 6)->getDirection() . " (devrait être Ouest)\n";
        } catch (Exception $e) {
            echo "ERREUR lors du mouvement vers point de retournement : " . $e->getMessage() . "\n";
        }

        // Test pour une pièce atteignant la fin du parcours
        $plateau = new PlateauSquadro(); // Réinitialisation du plateau
        $action = new ActionSquadro($plateau);

        // Plaçons une pièce noire qui va finir son parcours
        $pieceNoire = PieceSquadro::initNoirNord();
        $plateau->setPiece($pieceNoire, 1, 3);

        echo "État du plateau avant mouvement de fin de parcours :\n" . $plateau . "\n";
        echo "Colonnes jouables avant fin de parcours : [" . implode(", ", $plateau->getColonnesJouables()) . "]\n";

        try {
            // La pièce va se déplacer et atteindre la fin du parcours
            $action->jouePiece(1, 3);
            echo "État du plateau après mouvement de fin de parcours :\n" . $plateau . "\n";
            echo "Colonnes jouables après fin de parcours : [" . implode(", ", $plateau->getColonnesJouables()) . "]\n";
        } catch (Exception $e) {
            echo "ERREUR lors du mouvement de fin de parcours : " . $e->getMessage() . "\n";
        }

        echo "Tests de retournement et fin de parcours réussis.\n\n";
    }

    public function testToJsonFromJson() {
        echo "=== Test de sérialisation/désérialisation JSON ===\n";

        $plateau = new PlateauSquadro();
        // Modification du plateau pour le test
        $plateau->setPiece(PieceSquadro::initBlancEst(), 2, 3);
        $plateau->setPiece(PieceSquadro::initNoirNord(), 4, 2);
        $plateau->retireLigneJouable(5);

        $action = new ActionSquadro($plateau);

        $json = $action->toJson();
        echo "JSON : " . substr($json, 0, 100) . "... (tronqué)\n";

        $actionReconstruit = ActionSquadro::fromJson($json);
        echo "Action reconstruite.\n";

        // Test en déplaçant une pièce avec l'action reconstruite
        try {
            $actionReconstruit->jouePiece(2, 3);
            echo "Pièce déplacée avec succès après reconstruction.\n";
        } catch (Exception $e) {
            echo "ERREUR après reconstruction : " . $e->getMessage() . "\n";
        }

        echo "Tests de sérialisation/désérialisation JSON réussis.\n\n";
    }

    public function testToString() {
        echo "=== Test de la méthode __toString ===\n";

        $plateau = new PlateauSquadro();
        $action = new ActionSquadro($plateau);

        echo "Représentation en chaîne de caractères de l'action :\n" . $action . "\n";

        echo "Test de __toString réussi.\n\n";
    }

    public function executerTousLesTests() {
        echo "======= TESTS DE LA CLASSE ACTIONSQUADRO =======\n\n";
        $this->testConstruction();
        $this->testEstJouablePiece();
        $this->testJouePiece();
        $this->testReculePiece();
        $this->testSortPiece();
        $this->testRemporteVictoire();
        $this->testGererCaptures();
        $this->testCasesRetournementFinParcours();
        $this->testToJsonFromJson();
        $this->testToString();
        echo "======= FIN DES TESTS DE ACTIONSQUADRO =======\n\n";
    }
}

// Exécution des tests
$testAction = new TestActionSquadro();
$testAction->executerTousLesTests();