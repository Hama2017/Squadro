<?php
/**
 * Classe SquadroUIGenerator - Version corrigée
 *
 * Cette classe génère l'interface du jeu Squadro selon la description exacte:
 * - Plateau central 7x7 en gris clair (cases jouables)
 * - 4 coins en gris foncé (non jouables)
 * - Cadre extérieur 9x9 en rouge pour les vitesses
 */
class SquadroUIGenerator {
    /** @var ActionSquadro Instance de ActionSquadro */
    private ActionSquadro $action;

    /** @var PlateauSquadro Instance de PlateauSquadro */
    private PlateauSquadro $plateau;

    /** @var int Joueur actif */
    private int $joueurActif;

    /**
     * Constructeur
     */
    public function __construct(ActionSquadro $action, int $joueurActif = PieceSquadro::BLANC, ?PlateauSquadro $plateau = null) {
        $this->action = $action;
        $this->plateau = $plateau ?? new PlateauSquadro();
        $this->joueurActif = $joueurActif;
    }

    /**
     * Génère le CSS de l'interface
     */
    private function generateCSS(): string {
        return '
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
            
            .squadro-table {
                border-collapse: collapse;
                border: 2px solid #333;
            }
            
            .squadro-table td {
                width: 60px;
                height: 60px;
                text-align: center;
                font-weight: bold;
                font-size: 18px;
                border: 1px solid #999;
            }
            
            /* Cases du plateau */
            .case-jouable {
                background-color: #ccc; /* Gris clair */
            }
            
            .case-coin {
                background-color: #666; /* Gris foncé */
            }
            
            .case-cadre {
                background-color: #f99; /* Rouge clair */
            }
            
            /* Pièces */
            .piece-blanc {
                background-color: white;
                color: black;
            }
            
            .piece-noir {
                background-color: black;
                color: white;
            }
            
            /* Boutons */
            .piece-bouton {
                width: 100%;
                height: 100%;
                border: none;
                background: none;
                font-weight: bold;
                font-size: 18px;
                cursor: pointer;
            }
            
            .piece-bouton:hover {
                opacity: 0.8;
            }
            
            /* Sélectionné */
            .piece-selectionnee {
                background-color: #aaffaa !important;
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
        </style>
        ';
    }

    /**
     * Génère l'en-tête de la page
     */
    private function generateHeader(string $titre): string {
        return '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . $titre . '</title>
            ' . $this->generateCSS() . '
        </head>
        <body>
            <h1>Squadro</h1>';
    }

    /**
     * Génère le pied de page
     */
    private function generateFooter(): string {
        return '</body></html>';
    }

    /**
     * Obtient la vitesse pour une position donnée du cadre
     *
     * @param int $i La ligne (0-8)
     * @param int $j La colonne (0-8)
     * @return string La vitesse à afficher
     */
    private function getVitesse(int $i, int $j): string {
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
     * Génère le plateau de jeu sous forme de tableau HTML
     */
    private function generateTableau(bool $piecesJouables = true, ?array $pieceSelectionnee = null): string {
        $html = '<form id="formSquadro" method="post" action="' . $_SERVER['PHP_SELF'] . '">';
        $html .= '<table class="squadro-table">';

        // Génération du tableau ligne par ligne (9x9 avec cadre extérieur)
        for ($i = 0; $i < 9; $i++) {
            $html .= '<tr>';

            for ($j = 0; $j < 9; $j++) {
                // Calcul des coordonnées réelles du plateau (décalage de 1 pour le cadre)
                $x = $i - 1;
                $y = $j - 1;

                // Cadre extérieur (première/dernière ligne/colonne)
                if ($i == 0 || $i == 8 || $j == 0 || $j == 8) {
                    $vitesse = $this->getVitesse($i, $j);
                    $html .= '<td class="case-cadre">' . $vitesse . '</td>';
                    continue;
                }

                // Coins du plateau central (non jouables)
                if (($x == 0 && $y == 0) || ($x == 0 && $y == 6) ||
                    ($x == 6 && $y == 0) || ($x == 6 && $y == 6)) {
                    $html .= '<td class="case-coin"></td>';
                    continue;
                }

                // Case avec la pièce sélectionnée
                if ($pieceSelectionnee && $x == $pieceSelectionnee[0] && $y == $pieceSelectionnee[1]) {
                    $html .= '<td class="piece-selectionnee">✓</td>';
                    continue;
                }

                // Positions fixes des pièces blanches (colonne de gauche)
                if ($y == 0 && $x >= 1 && $x <= 5) {
                    $estJouable = $piecesJouables &&
                        $this->joueurActif === PieceSquadro::BLANC &&
                        $this->action->estJouablePiece($x, $y);

                    if ($estJouable) {
                        $html .= '<td class="piece-blanc">
                                   <button type="submit" name="piece" value="' . $x . ',' . $y . '" 
                                           class="piece-bouton">BE</button>
                                 </td>';
                    } else {
                        $html .= '<td class="piece-blanc">BE</td>';
                    }
                    continue;
                }

                // Positions fixes des pièces noires (ligne du bas)
                if ($x == 6 && $y >= 1 && $y <= 5) {
                    $estJouable = $piecesJouables &&
                        $this->joueurActif === PieceSquadro::NOIR &&
                        $this->action->estJouablePiece($x, $y);

                    if ($estJouable) {
                        $html .= '<td class="piece-noir">
                                   <button type="submit" name="piece" value="' . $x . ',' . $y . '" 
                                           class="piece-bouton">NN</button>
                                 </td>';
                    } else {
                        $html .= '<td class="piece-noir">NN</td>';
                    }
                    continue;
                }

                // Vérifier s'il y a une pièce en mouvement à cette position
                $piece = $this->plateau->getPiece($x, $y);

                if ($piece->getCouleur() === PieceSquadro::BLANC) {
                    // Pièce blanche en mouvement
                    $estJouable = $piecesJouables &&
                        $this->joueurActif === PieceSquadro::BLANC &&
                        $this->action->estJouablePiece($x, $y);

                    if ($estJouable) {
                        $html .= '<td class="piece-blanc">
                                   <button type="submit" name="piece" value="' . $x . ',' . $y . '" 
                                           class="piece-bouton">BE</button>
                                 </td>';
                    } else {
                        $html .= '<td class="piece-blanc">BE</td>';
                    }
                }
                else if ($piece->getCouleur() === PieceSquadro::NOIR) {
                    // Pièce noire en mouvement
                    $estJouable = $piecesJouables &&
                        $this->joueurActif === PieceSquadro::NOIR &&
                        $this->action->estJouablePiece($x, $y);

                    if ($estJouable) {
                        $html .= '<td class="piece-noir">
                                   <button type="submit" name="piece" value="' . $x . ',' . $y . '" 
                                           class="piece-bouton">NN</button>
                                 </td>';
                    } else {
                        $html .= '<td class="piece-noir">NN</td>';
                    }
                }
                else {
                    // Case vide (jouable)
                    $html .= '<td class="case-jouable"></td>';
                }
            }

            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</form>';

        return $html;
    }

    /**
     * Génère la page de choix de pièce
     */
    public function generatePageChoixPiece(): string {
        $html = $this->generateHeader('Squadro - Jeu');

        $joueurNom = ($this->joueurActif === PieceSquadro::BLANC) ? 'Les blancs jouent' : 'Les noirs jouent';
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
     */
    public function generatePageConfirmationDeplacement(int $x, int $y): string {
        $html = $this->generateHeader('Squadro - Confirmation');

        $joueurNom = ($this->joueurActif === PieceSquadro::BLANC) ? 'Les blancs jouent' : 'Les noirs jouent';
        $html .= '<h2>' . $joueurNom . '</h2>';

        $coordsDestination = $this->plateau->getCoordDestination($x, $y);

        $html .= '<div class="message message-info">
                    <p>Vous avez sélectionné la pièce en position (' . $x . ', ' . $y . ')</p>
                    <p>Cette pièce se déplacera vers la position (' . $coordsDestination[0] . ', ' . $coordsDestination[1] . ')</p>
                    <p>Confirmez-vous ce déplacement ?</p>
                  </div>';

        $html .= $this->generateTableau(false, [$x, $y]);

        $html .= '<div class="actions">
                    <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
                        <input type="hidden" name="confirmer" value="1">
                        <input type="hidden" name="x" value="' . $x . '">
                        <input type="hidden" name="y" value="' . $y . '">
                        <button type="submit" class="bouton-action bouton-confirmer">Confirmer</button>
                    </form>
                    <a href="' . $_SERVER['PHP_SELF'] . '" class="bouton-action bouton-annuler">Annuler</a>
                  </div>';

        $html .= $this->generateFooter();
        return $html;
    }

    /**
     * Génère la page de victoire
     */
    public function generatePageVictoire(int $vainqueur): string {
        $html = $this->generateHeader('Squadro - Fin de partie');

        $joueurNom = ($vainqueur === PieceSquadro::BLANC) ? 'Blanc' : 'Noir';

        $html .= '<div class="message message-victoire">
                    <p>Le joueur ' . $joueurNom . ' a remporté la partie !</p>
                  </div>';

        $html .= $this->generateTableau(false);

        $html .= '<div class="actions">
                    <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
                        <input type="hidden" name="nouvelle_partie" value="1">
                        <button type="submit" class="bouton-action bouton-confirmer">Nouvelle partie</button>
                    </form>
                  </div>';

        $html .= $this->generateFooter();
        return $html;
    }

    /**
     * Génère la page d'erreur
     */
    public function generatePageErreur(string $message): string {
        $html = $this->generateHeader('Squadro - Erreur');

        $html .= '<div class="message message-erreur">
                    <p>Erreur : ' . $message . '</p>
                  </div>';

        $html .= '<div class="actions">
                    <a href="' . $_SERVER['PHP_SELF'] . '" class="bouton-action bouton-confirmer">Retour au jeu</a>
                  </div>';

        $html .= $this->generateFooter();
        return $html;
    }

    /**
     * Modifie le joueur actif
     */
    public function setJoueurActif(int $joueurActif): void {
        $this->joueurActif = $joueurActif;
    }

    /**
     * Définit un nouveau plateau
     */
    public function setPlateau(PlateauSquadro $plateau): void {
        $this->plateau = $plateau;
    }

    /**
     * Récupère l'action associée
     */
    public function getAction(): ActionSquadro {
        return $this->action;
    }
}