<?php
/**
 * Classe SquadroUIGenerator
 *
 * Cette classe est responsable de générer les différentes pages HTML
 * de l'interface de jeu Squadro.
 *
 * @author Claude
 */
class SquadroUIGenerator {
    /** @var ActionSquadro Instance de la classe ActionSquadro pour la logique du jeu */
    private ActionSquadro $action;

    /** @var PlateauSquadro Instance de la classe PlateauSquadro pour l'état du plateau */
    private PlateauSquadro $plateau;

    /** @var PieceSquadroUI Instance de la classe PieceSquadroUI pour la génération des pièces */
    private PieceSquadroUI $pieceUI;

    /** @var int Le joueur actif */
    private int $joueurActif;

    /**
     * Constructeur de la classe SquadroUIGenerator
     *
     * @param ActionSquadro $action Instance de ActionSquadro
     * @param int $joueurActif Le joueur actif (PieceSquadro::BLANC ou PieceSquadro::NOIR)
     */
    public function __construct(ActionSquadro $action, int $joueurActif = PieceSquadro::BLANC) {
        $this->action = $action;
        $this->plateau = $action->getPlateau();
        $this->joueurActif = $joueurActif;
        $this->pieceUI = new PieceSquadroUI($joueurActif);
    }

    /**
     * Génère le code HTML de l'en-tête de la page
     *
     * @param string $titre Titre de la page
     * @return string Code HTML de l'en-tête
     */
    private function generateHeader(string $titre): string {
        return '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . $titre . '</title>
            ' . $this->pieceUI->generateCSS() . '
        </head>
        <body>
            <h1 style="text-align: center;">' . $titre . '</h1>';
    }

    /**
     * Génère le code HTML du pied de page
     *
     * @return string Code HTML du pied de page
     */
    private function generateFooter(): string {
        return '
        </body>
        </html>';
    }

    /**
     * Génère le code HTML pour le plateau de jeu
     *
     * @param bool $piecesJouables Indique si les pièces du joueur actif doivent être jouables
     * @param array|null $coordsPieceSelectionnee Coordonnées de la pièce sélectionnée [x, y]
     * @return string Code HTML du plateau de jeu
     */
    private function generatePlateau(bool $piecesJouables = true, ?array $coordsPieceSelectionnee = null): string {
        $html = '<div class="plateau-squadro">';

        // Création du formulaire
        $html .= $this->pieceUI->createForm($_SERVER['PHP_SELF']);

        for ($x = 0; $x < 7; $x++) {
            for ($y = 0; $y < 7; $y++) {
                $piece = $this->plateau->getPiece($x, $y);
                $estJouable = false;

                // Vérifier si la pièce est jouable par le joueur actif
                if ($piecesJouables
                    && $piece->getCouleur() === $this->joueurActif
                    && $this->action->estJouablePiece($x, $y)) {
                    $estJouable = true;
                }

                // Si une pièce est sélectionnée, la mettre en surbrillance
                if ($coordsPieceSelectionnee !== null
                    && $x === $coordsPieceSelectionnee[0]
                    && $y === $coordsPieceSelectionnee[1]) {
                    $html .= '<button type="button" class="piece-selectionnee" disabled>✓</button>';
                } else {
                    $html .= $this->pieceUI->generatePiece($x, $y, $piece, $estJouable);
                }
            }
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Génère le code HTML pour l'information sur le joueur actif
     *
     * @return string Code HTML d'information sur le joueur actif
     */
    private function generateInfoJoueur(): string {
        $joueurNom = ($this->joueurActif === PieceSquadro::BLANC) ? 'Blanc' : 'Noir';
        return '<div class="info-jeu">
            <p>Joueur actif : <strong>' . $joueurNom . '</strong></p>
        </div>';
    }

    /**
     * Génère une page pour choisir une pièce à jouer
     *
     * @return string Code HTML de la page de sélection
     */
    public function generatePageChoixPiece(): string {
        $html = $this->generateHeader('Squadro - Choix de la pièce');
        $html .= $this->generateInfoJoueur();
        $html .= '<div class="info-jeu">
            <p>Sélectionnez une pièce à déplacer</p>
        </div>';
        $html .= $this->generatePlateau(true);
        $html .= $this->generateFooter();

        return $html;
    }

    /**
     * Génère une page pour confirmer le déplacement d'une pièce
     *
     * @param int $x Coordonnée X de la pièce sélectionnée
     * @param int $y Coordonnée Y de la pièce sélectionnée
     * @return string Code HTML de la page de confirmation
     */
    public function generatePageConfirmationDeplacement(int $x, int $y): string {
        $piece = $this->plateau->getPiece($x, $y);
        $coordsDestination = $this->plateau->getCoordDestination($x, $y);

        $html = $this->generateHeader('Squadro - Confirmation du déplacement');
        $html .= $this->generateInfoJoueur();

        // Informations sur le déplacement
        $joueurNom = ($this->joueurActif === PieceSquadro::BLANC) ? 'Blanc' : 'Noir';
        $html .= '<div class="info-jeu">
            <p>Vous avez sélectionné la pièce en position (' . $x . ', ' . $y . ')</p>
            <p>Cette pièce se déplacera vers la position (' . $coordsDestination[0] . ', ' . $coordsDestination[1] . ')</p>
            <p>Confirmez-vous ce déplacement ?</p>
        </div>';

        // Affichage du plateau avec la pièce sélectionnée
        $html .= $this->generatePlateau(false, [$x, $y]);

        // Boutons de confirmation
        $html .= '<div class="boutons-action">
            <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
                <input type="hidden" name="confirmer" value="1">
                <input type="hidden" name="x" value="' . $x . '">
                <input type="hidden" name="y" value="' . $y . '">
                <button type="submit" class="bouton-action bouton-confirmer">Confirmer</button>
                <a href="' . $_SERVER['PHP_SELF'] . '" class="bouton-action bouton-annuler" style="text-decoration: none; display: inline-block;">Annuler</a>
            </form>
        </div>';

        $html .= $this->generateFooter();

        return $html;
    }

    /**
     * Génère une page affichant le plateau final et le message de victoire
     *
     * @param int $vainqueur Le joueur vainqueur (PieceSquadro::BLANC ou PieceSquadro::NOIR)
     * @return string Code HTML de la page de victoire
     */
    public function generatePageVictoire(int $vainqueur): string {
        $joueurNom = ($vainqueur === PieceSquadro::BLANC) ? 'Blanc' : 'Noir';

        $html = $this->generateHeader('Squadro - Fin de partie');

        // Message de victoire
        $html .= '<div class="message-victoire">
            <p>Le joueur ' . $joueurNom . ' a remporté la partie !</p>
        </div>';

        // Affichage du plateau final
        $html .= $this->generatePlateau(false);

        // Bouton pour recommencer
        $html .= '<div class="boutons-action">
            <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
                <input type="hidden" name="nouvelle_partie" value="1">
                <button type="submit" class="bouton-action bouton-confirmer">Nouvelle partie</button>
            </form>
        </div>';

        $html .= $this->generateFooter();

        return $html;
    }

    /**
     * Génère une page d'erreur
     *
     * @param string $message Message d'erreur à afficher
     * @return string Code HTML de la page d'erreur
     */
    public function generatePageErreur(string $message): string {
        $html = $this->generateHeader('Squadro - Erreur');

        // Message d'erreur
        $html .= '<div class="message-victoire" style="color: red; border-color: red; background-color: #fff0f0;">
            <p>Erreur : ' . $message . '</p>
        </div>';

        // Bouton pour retourner au jeu
        $html .= '<div class="boutons-action">
            <a href="' . $_SERVER['PHP_SELF'] . '" class="bouton-action bouton-confirmer" style="text-decoration: none; display: inline-block;">Retour au jeu</a>
        </div>';

        $html .= $this->generateFooter();

        return $html;
    }

    /**
     * Récupère l'objet ActionSquadro associé
     *
     * @return ActionSquadro Instance de ActionSquadro
     */
    public function getAction(): ActionSquadro {
        return $this->action;
    }

    /**
     * Modifie le joueur actif
     *
     * @param int $joueurActif Le nouveau joueur actif
     */
    public function setJoueurActif(int $joueurActif): void {
        $this->joueurActif = $joueurActif;
        $this->pieceUI = new PieceSquadroUI($joueurActif);
    }
}
?>