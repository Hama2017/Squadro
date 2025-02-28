<?php
/**
 * Classe SquadroUIGenerator
 *
 * Génère les différentes pages de l'interface du jeu Squadro
 * Gère la construction des pages de jeu, confirmation et victoire
 *
 * @author [Votre Nom]
 */
class SquadroUIGenerator
{
    /** @var ActionSquadro Gestion des actions du jeu */
    private ActionSquadro $action;

    /** @var PlateauSquadro Plateau de jeu actuel */
    private PlateauSquadro $plateau;

    /** @var int Joueur actif */
    private int $joueurActif;

    /** @var PieceSquadroUI Générateur des composants UI */
    private PieceSquadroUI $pieceUI;

    /** @var string URL de traitement des actions */
    private string $actionUrl;

    /**
     * Constructeur
     *
     * @param ActionSquadro $action Instance de gestion des actions
     * @param int $joueurActif Joueur actuel (BLANC ou NOIR)
     * @param PlateauSquadro|null $plateau Plateau de jeu
     * @param string $actionUrl URL de traitement des actions
     */
    public function __construct(
        ActionSquadro   $action,
        int             $joueurActif = PieceSquadro::BLANC,
        ?PlateauSquadro $plateau = null,
        string          $actionUrl = 'traiteActionSquadro.php'
    )
    {
        $this->action = $action;
        $this->plateau = $plateau ?? new PlateauSquadro();
        $this->joueurActif = $joueurActif;
        $this->pieceUI = new PieceSquadroUI($joueurActif);
        $this->actionUrl = $actionUrl;
    }

    /**
     * Génère le CSS de l'interface
     *
     * @return string Code CSS
     */
    private function generateCSS(): string
    {
        return $this->pieceUI->generateCSS() . '
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            
            h1 {
                font-size: 36px;
                margin-bottom: 10px;
            }
            
            h2 {
                font-size: 24px;
                margin-bottom: 20px;
            }
            
            .squadro-plateau {
                display: grid;
                grid-template-columns: repeat(9, 50px);
                grid-template-rows: repeat(9, 50px);
                gap: 2px;
                justify-content: center;
            }
            
            .case-cadre {
                background-color: #f99; /* Rouge clair */
                display: flex;
                justify-content: center;
                align-items: center;
                font-weight: bold;
            }
            
            /* Actions */
            .actions {
                margin-top: 20px;
                display: flex;
                gap: 10px;
            }
            
            .bouton-action {
                padding: 10px 20px;
                font-size: 16px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            
            .bouton-confirmer {
                background-color: #4CAF50;
                color: white;
            }
            
            .bouton-annuler {
                background-color: #f44336;
                color: white;
                text-decoration: none;
                display: inline-block;
            }
            
            /* Messages */
            .message {
                margin: 20px 0;
                padding: 15px;
                border-radius: 5px;
                max-width: 500px;
                text-align: center;
            }
            
            .message-info {
                background-color: #e0e0e0;
            }
            
            .message-victoire {
                background-color: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
            }
            
            .message-erreur {
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
            }

            .piece-selectionnee {
                background-color: #aaffaa !important;
            }
        </style>';
    }

    /**
     * Génère l'en-tête HTML
     *
     * @param string $titre Titre de la page
     * @return string Code HTML de l'en-tête
     */
    private function generateHeader(string $titre): string
    {
        return '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . $titre . '</title>
            ' . $this->generateCSS() . '
        </head>
        <body>
            <h1>Squadro</h1>
            ' . $this->pieceUI->createForm($this->actionUrl) . '
            ';
    }

    /**
     * Génère le pied de page HTML
     *
     * @return string Code HTML du pied de page
     */
    private function generateFooter(): string
    {
        return '</body></html>';
    }

    /**
     * Obtient la vitesse pour une position donnée du cadre
     *
     * @param int $i Ligne (0-8)
     * @param int $j Colonne (0-8)
     * @return string Vitesse à afficher
     */
    private function getVitesse(int $i, int $j): string
    {
        // Bordure du haut (ligne 0)
        if ($i == 0) {
            if ($j == 2) return "1";
            if ($j == 3) return "3";
            if ($j == 4) return "2";
            if ($j == 5) return "3";
            if ($j == 6) return "1";
        }

        // Bordure du bas (ligne 8)
        if ($i == 8) {
            if ($j == 2) return "3";
            if ($j == 3) return "1";
            if ($j == 4) return "2";
            if ($j == 5) return "1";
            if ($j == 6) return "3";
        }

        // Bordure de gauche (colonne 0)
        if ($j == 0) {
            if ($i == 2) return "1";
            if ($i == 3) return "3";
            if ($i == 4) return "2";
            if ($i == 5) return "3";
            if ($i == 6) return "1";
        }

        // Bordure de droite (colonne 8)
        if ($j == 8) {
            if ($i == 2) return "3";
            if ($i == 3) return "1";
            if ($i == 4) return "2";
            if ($i == 5) return "1";
            if ($i == 6) return "3";
        }

        return "";
    }

    /**
     * Génère le plateau de jeu
     *
     * @param bool $piecesJouables Indique si les pièces sont jouables
     * @param array|null $pieceSelectionnee Coordonnées de la pièce sélectionnée
     * @return string Code HTML du plateau
     */
    private function generateTableau(bool $piecesJouables = true, ?array $pieceSelectionnee = null): string
    {
        $html = '<div class="squadro-plateau">';

        // Génération du tableau ligne par ligne (9x9 avec cadre extérieur)
        for ($i = 0; $i < 9; $i++) {
            for ($j = 0; $j < 9; $j++) {
                // Calcul des coordonnées réelles du plateau (décalage de 1 pour le cadre)
                $x = $i - 1;
                $y = $j - 1;

                // Cadre extérieur (première/dernière ligne/colonne)
                if ($i == 0 || $i == 8 || $j == 0 || $j == 8) {
                    $vitesse = $this->getVitesse($i, $j);
                    $html .= '<div class="case-cadre">' . $vitesse . '</div>';
                    continue;
                }

                // Coins du plateau central (non jouables)
                if (($x == 0 && $y == 0) || ($x == 0 && $y == 6) ||
                    ($x == 6 && $y == 0) || ($x == 6 && $y == 6)) {
                    $html .= $this->pieceUI->generateCaseNeutre();
                    continue;
                }

                // Case avec la pièce sélectionnée
                if ($pieceSelectionnee && $x == $pieceSelectionnee[0] && $y == $pieceSelectionnee[1]) {
                    $html .= '<div class="piece-selectionnee">✓</div>';
                    continue;
                }

                // Vérifier s'il y a une pièce à cette position
                $piece = $this->plateau->getPiece($x, $y);

                // Déterminer si la pièce est jouable
                // Modification pour corriger le problème: au lieu de vérifier avec estJouablePiece,
                // on vérifie simplement que la pièce appartient au joueur actif
                $estJouable = $piecesJouables &&
                    $piece->getCouleur() === $this->joueurActif;

                // Générer le HTML pour la pièce
                $html .= $this->pieceUI->generatePiece($x, $y, $piece, $estJouable);
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Génère la page de choix de pièce
     *
     * @return string Code HTML de la page de choix
     */
    public function generatePageChoixPiece(): string
    {
        $html = $this->generateHeader('Squadro - Jeu');

        $joueurNom = ($this->joueurActif === PieceSquadro::BLANC)
            ? 'Les blancs jouent'
            : 'Les noirs jouent';

        $html .= '<h2>' . $joueurNom . '</h2>';

        $html .= '<div class="message message-info">
                    <p>Sélectionnez une pièce à déplacer</p>
                  </div>';

        $html .= $this->generateTableau(true);

        $html .= $this->generateFooter();
        return $html;
    }

    /**
     * Génère la page de confirmation de déplacement
     *
     * @param int $x Coordonnée X de la pièce
     * @param int $y Coordonnée Y de la pièce
     * @return string Code HTML de la page de confirmation
     */
    public function generatePageConfirmationDeplacement(int $x, int $y): string
    {
        $html = $this->generateHeader('Squadro - Confirmation');

        $joueurNom = ($this->joueurActif === PieceSquadro::BLANC)
            ? 'Les blancs jouent'
            : 'Les noirs jouent';

        $html .= '<h2>' . $joueurNom . '</h2>';

        // Calcul de la destination avec une gestion des erreurs
        try {
            $coordsDestination = $this->plateau->getCoordDestination($x, $y);
            $messageDestination = "Cette pièce se déplacera vers la position (" . $coordsDestination[0] . ", " . $coordsDestination[1] . ")";
        } catch (Exception $e) {
            $messageDestination = "Cette pièce ne peut pas être déplacée (destination hors plateau)";
        }

        $html .= '<div class="message message-info">
                    <p>Vous avez sélectionné la pièce en position (' . $x . ', ' . $y . ')</p>
                    <p>' . $messageDestination . '</p>
                    <p>Confirmez-vous ce déplacement ?</p>
                  </div>';

        $html .= $this->generateTableau(false, [$x, $y]);

        $html .= '<div class="actions">
                    <form action="' . $this->actionUrl . '" method="post">
                        <input type="hidden" name="confirmer" value="1">
                        <input type="hidden" name="x" value="' . $x . '">
                        <input type="hidden" name="y" value="' . $y . '">
                        <button type="submit" class="bouton-action bouton-confirmer">Confirmer</button>
                    </form>
                    <form action="' . $this->actionUrl . '" method="post">
                        <input type="hidden" name="annuler" value="1">
                        <button type="submit" class="bouton-action bouton-annuler">Annuler</button>
                    </form>
                  </div>';

        $html .= $this->generateFooter();
        return $html;
    }

    /**
     * Génère la page de victoire
     *
     * @param int $vainqueur Couleur du joueur vainqueur
     * @return string Code HTML de la page de victoire
     */
    public function generatePageVictoire(int $vainqueur): string
    {
        $html = $this->generateHeader('Squadro - Fin de partie');

        $joueurNom = ($vainqueur === PieceSquadro::BLANC) ? 'Blanc' : 'Noir';

        $html .= '<div class="message message-victoire">
                    <p>Le joueur ' . $joueurNom . ' a remporté la partie !</p>
                  </div>';

        $html .= $this->generateTableau(false);

        $html .= '<div class="actions">
                    <form action="' . $this->actionUrl . '" method="post">
                        <input type="hidden" name="nouvelle_partie" value="1">
                        <button type="submit" class="bouton-action bouton-confirmer">Nouvelle partie</button>
                    </form>
                  </div>';

        $html .= $this->generateFooter();
        return $html;
    }

    /**
     * Génère la page d'erreur
     *
     * @param string $message Message d'erreur
     * @return string Code HTML de la page d'erreur
     */
    public function generatePageErreur(string $message): string
    {
        $html = $this->generateHeader('Squadro - Erreur');

        $html .= '<div class="message message-erreur">
                    <p>Erreur : ' . $message . '</p>
                  </div>';

        $html .= '<div class="actions">
                    <form action="' . $this->actionUrl . '" method="post">
                        <input type="hidden" name="nouvelle_partie" value="1">
                        <button type="submit" class="bouton-action bouton-confirmer">Nouvelle partie</button>
                    </form>
                  </div>';

        $html .= $this->generateFooter();
        return $html;
    }

    /**
     * Modifie le joueur actif
     *
     * @param int $joueurActif Nouveau joueur actif
     */
    public function setJoueurActif(int $joueurActif): void
    {
        $this->joueurActif = $joueurActif;
        $this->pieceUI = new PieceSquadroUI($joueurActif);
    }

    /**
     * Définit un nouveau plateau
     *
     * @param PlateauSquadro $plateau Nouveau plateau de jeu
     */
    public function setPlateau(PlateauSquadro $plateau): void
    {
        $this->plateau = $plateau;
    }

    public function generatePageAttenteTour(): string {
        $html = $this->generateHeader('Squadro - En attente');

        $joueurNom = ($this->joueurActif === PieceSquadro::BLANC)
            ? 'Les blancs jouent'
            : 'Les noirs jouent';

        $html .= '<h2>' . $joueurNom . '</h2>';

        $html .= '<div class="message message-info">
                <p>C\'est le tour de votre adversaire. Cette page se rafraîchira automatiquement.</p>
              </div>';

        $html .= $this->generateTableau(false);

        // Ajouter un rafraîchissement automatique toutes les 10 secondes
        $html .= '<script>
                setTimeout(function() {
                    window.location.reload();
                }, 10000);
              </script>';

        $html .= $this->generateFooter();
        return $html;
    }
}