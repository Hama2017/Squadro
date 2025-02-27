<?php
require_once '../autoload.php';
require_once '../class/ui/autoload.php';

class TestSquadroUIGenerator {
    private function creerActionEtPlateauTest(): ActionSquadro {
        $plateau = new PlateauSquadro();
        // Modifier le plateau pour un scénario de test
        $plateau->setPiece(PieceSquadro::initBlancEst(), 2, 2);
        $plateau->setPiece(PieceSquadro::initNoirNord(), 4, 3);

        return new ActionSquadro($plateau);
    }

    public function testGenerationPages() {
        echo "=== Test de génération des pages HTML ===\n";

        // Création d'une instance de jeu pour les tests
        $action = $this->creerActionEtPlateauTest();
        $uiGenerator = new SquadroUIGenerator($action, PieceSquadro::BLANC);

        // Test de la page de choix de pièce
        echo "\n--- Test génération page de choix de pièce ---\n";
        $pageChoix = $uiGenerator->generatePageChoixPiece();
        $this->verifierContenuPageHTML($pageChoix, [
            '<!DOCTYPE html>',
            '<html lang="fr"',
            'Squadro - Jeu',
            'Les blancs jouent',
            'Sélectionnez une pièce à déplacer',
            'class="squadro-plateau"'
        ]);

        // Test de la page de confirmation de déplacement
        echo "\n--- Test génération page de confirmation ---\n";
        $pageConfirmation = $uiGenerator->generatePageConfirmationDeplacement(2, 2);
        $this->verifierContenuPageHTML($pageConfirmation, [
            'Squadro - Confirmation',
            'Les blancs jouent',
            'Vous avez sélectionné la pièce en position (2, 2)',
            'Confirmez-vous ce déplacement ?',
            'bouton-action bouton-confirmer',
            'bouton-action bouton-annuler'
        ]);

        // Test de la page de victoire
        echo "\n--- Test génération page de victoire ---\n";
        $pageVictoire = $uiGenerator->generatePageVictoire(PieceSquadro::BLANC);
        $this->verifierContenuPageHTML($pageVictoire, [
            'Squadro - Fin de partie',
            'Le joueur Blanc a remporté la partie',
            'Nouvelle partie',
            'message-victoire'
        ]);

        // Test de la page d'erreur
        echo "\n--- Test génération page d'erreur ---\n";
        $pageErreur = $uiGenerator->generatePageErreur("Test d'erreur");
        $this->verifierContenuPageHTML($pageErreur, [
            'Squadro - Erreur',
            'Erreur : Test d\'erreur',
            'message-erreur',
            'Retour au jeu'
        ]);
    }

    public function testMethodesSpecifiques() {
        echo "\n=== Test des méthodes spécifiques ===\n";

        $action = $this->creerActionEtPlateauTest();
        $uiGenerator = new SquadroUIGenerator($action, PieceSquadro::BLANC);

        // Test changement de joueur actif
        echo "\n--- Test changement de joueur actif ---\n";
        $uiGenerator->setJoueurActif(PieceSquadro::NOIR);
        $pageChoixNoir = $uiGenerator->generatePageChoixPiece();
        $this->verifierContenuPageHTML($pageChoixNoir, [
            'Les noirs jouent'
        ]);

        // Test changement de plateau
        echo "\n--- Test changement de plateau ---\n";
        $nouveauPlateau = new PlateauSquadro();
        $nouveauPlateau->setPiece(PieceSquadro::initBlancOuest(), 3, 3);
        $uiGenerator->setPlateau($nouveauPlateau);
        $pageChoixNouveau = $uiGenerator->generatePageChoixPiece();
        $this->verifierContenuPageHTML($pageChoixNouveau, [
            'Les blancs jouent'
        ]);
    }

    private function verifierContenuPageHTML(string $html, array $elementsAttendus) {
        foreach ($elementsAttendus as $element) {
            if (strpos($html, $element) === false) {
                echo "ERREUR : L'élément '$element' est manquant dans le HTML généré.\n";
            } else {
                echo "OK : Élément '$element' présent.\n";
            }
        }
    }

    public function executerTousLesTests() {
        echo "======= TESTS DE LA CLASSE SQUADROUI GENERATOR =======\n\n";
        $this->testGenerationPages();
        $this->testMethodesSpecifiques();
        echo "======= FIN DES TESTS DE SQUADROUI GENERATOR =======\n\n";
    }
}

// Exécution des tests
$testSquadroUI = new TestSquadroUIGenerator();
$testSquadroUI->executerTousLesTests();