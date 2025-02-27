<?php
require_once '../autoload.php';
require_once '../class/ui/autoload.php';


class TestPieceSquadroUI {
    public function testGenerationBoutons() {
        echo "=== Test de génération des boutons ===\n";

        // Test pour joueur blanc actif
        $uiBlanc = new PieceSquadroUI(PieceSquadro::BLANC);

        // Test case vide
        $boutonVide = $uiBlanc->generateCaseVide();
        echo "Génération case vide :\n";
        echo $boutonVide . "\n";
        $this->verifierContientElementHTML($boutonVide, 'button', 'case-vide', 'disabled');

        // Test case neutre
        $boutonNeutre = $uiBlanc->generateCaseNeutre();
        echo "\nGénération case neutre :\n";
        echo $boutonNeutre . "\n";
        $this->verifierContientElementHTML($boutonNeutre, 'button', 'case-neutre', 'disabled');

        // Test pièce blanche jouable
        $boutonBlancJouable = $uiBlanc->generatePieceBlanche(2, 3, PieceSquadro::EST, true);
        echo "\nGénération pièce blanche jouable :\n";
        echo $boutonBlancJouable . "\n";
        $this->verifierContientElementHTML($boutonBlancJouable, 'button', 'piece-blanche', 'piece-blanche-est', 'submit', 'name="piece"');

        // Test pièce blanche non jouable
        $boutonBlancNonJouable = $uiBlanc->generatePieceBlanche(2, 3, PieceSquadro::EST, false);
        echo "\nGénération pièce blanche non jouable :\n";
        echo $boutonBlancNonJouable . "\n";
        $this->verifierContientElementHTML($boutonBlancNonJouable, 'button', 'piece-blanche', 'piece-blanche-est', 'button', 'disabled');

        // Test joueur noir actif
        $uiNoir = new PieceSquadroUI(PieceSquadro::NOIR);

        // Test pièce noire jouable
        $boutonNoirJouable = $uiNoir->generatePieceNoire(4, 5, PieceSquadro::NORD, true);
        echo "\nGénération pièce noire jouable :\n";
        echo $boutonNoirJouable . "\n";
        $this->verifierContientElementHTML($boutonNoirJouable, 'button', 'piece-noire', 'piece-noire-nord', 'submit', 'name="piece"');

        // Test pièce noire non jouable
        $boutonNoirNonJouable = $uiNoir->generatePieceNoire(4, 5, PieceSquadro::NORD, false);
        echo "\nGénération pièce noire non jouable :\n";
        echo $boutonNoirNonJouable . "\n";
        $this->verifierContientElementHTML($boutonNoirNonJouable, 'button', 'piece-noire', 'piece-noire-nord', 'button', 'disabled');

        // Test génération CSS
        $css = $uiBlanc->generateCSS();
        echo "\nGénération CSS :\n";
        echo substr($css, 0, 500) . "...\n";
        $this->verifierContientElementHTML($css, 'style');
    }

    private function verifierContientElementHTML(string $html, string ...$elements) {
        foreach ($elements as $element) {
            if (strpos($html, $element) === false) {
                echo "ERREUR : L'élément '$element' est manquant dans le HTML généré.\n";
            } else {
                echo "OK : Élément '$element' présent.\n";
            }
        }
    }

    public function executerTousLesTests() {
        echo "======= TESTS DE LA CLASSE PIECESQUADROUI =======\n\n";
        $this->testGenerationBoutons();
        echo "======= FIN DES TESTS DE PIECESQUADROUI =======\n\n";
    }
}

// Exécution des tests
$testPieceUI = new TestPieceSquadroUI();
$testPieceUI->executerTousLesTests();