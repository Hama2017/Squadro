<?php
/**
 * Classe PieceSquadroUI
 *
 * Cette classe est responsable de générer les boutons HTML
 * pour toutes les configurations du jeu Squadro.
 *
 * @author [Votre Nom]
 */
class PieceSquadroUI {
    /** @var int Le joueur actif (PieceSquadro::BLANC ou PieceSquadro::NOIR) */
    private int $joueurActif;

    /** @var string Le nom du formulaire pour transmettre les coordonnées */
    private string $nomFormulaire;

    /**
     * Constructeur de la classe
     *
     * @param int $joueurActif Le joueur actuel (BLANC ou NOIR)
     * @param string $nomFormulaire Nom du formulaire HTML
     */
    public function __construct(int $joueurActif, string $nomFormulaire = 'formSquadro') {
        $this->joueurActif = $joueurActif;
        $this->nomFormulaire = $nomFormulaire;
    }

    /**
     * Génère le bouton pour une case vide
     *
     * @return string Bouton HTML pour une case vide
     */
    public function generateCaseVide(): string {
        return '<button type="button" class="case-vide" disabled>·</button>';
    }

    /**
     * Génère le bouton pour une case neutre (coins du plateau)
     *
     * @return string Bouton HTML pour une case neutre
     */
    public function generateCaseNeutre(): string {
        return '<button type="button" class="case-neutre" disabled>×</button>';
    }

    /**
     * Génère le bouton pour une pièce blanche
     *
     * @param int $x Coordonnée X de la pièce
     * @param int $y Coordonnée Y de la pièce
     * @param int $direction Direction de la pièce
     * @param bool $estJouable Indique si la pièce peut être jouée
     * @return string Bouton HTML pour une pièce blanche
     */
    public function generatePieceBlanche(int $x, int $y, int $direction, bool $estJouable): string {
        // La pièce est-elle jouable pour les blancs ?
        $jouable = ($this->joueurActif === PieceSquadro::BLANC && $estJouable);

        // Classes pour la direction
        $directionClass = ($direction === PieceSquadro::EST) ? 'est' : 'ouest';

        if ($jouable) {
            // Bouton de soumission pour les pièces jouables
            return '<button type="submit" 
                            name="piece" 
                            value="' . $x . ',' . $y . '" 
                            form="' . $this->nomFormulaire . '" 
                            class="piece piece-blanche piece-blanche-' . $directionClass . '">BE</button>';
        } else {
            // Bouton désactivé
            return '<button type="button" 
                            class="piece piece-blanche piece-blanche-' . $directionClass . '" 
                            disabled>BE</button>';
        }
    }

    /**
     * Génère le bouton pour une pièce noire
     *
     * @param int $x Coordonnée X de la pièce
     * @param int $y Coordonnée Y de la pièce
     * @param int $direction Direction de la pièce
     * @param bool $estJouable Indique si la pièce peut être jouée
     * @return string Bouton HTML pour une pièce noire
     */
    public function generatePieceNoire(int $x, int $y, int $direction, bool $estJouable): string {
        // La pièce est-elle jouable pour les noirs ?
        $jouable = ($this->joueurActif === PieceSquadro::NOIR && $estJouable);

        // Classes pour la direction
        $directionClass = ($direction === PieceSquadro::NORD) ? 'nord' : 'sud';

        if ($jouable) {
            // Bouton de soumission pour les pièces jouables
            return '<button type="submit" 
                            name="piece" 
                            value="' . $x . ',' . $y . '" 
                            form="' . $this->nomFormulaire . '" 
                            class="piece piece-noire piece-noire-' . $directionClass . '">NN</button>';
        } else {
            // Bouton désactivé
            return '<button type="button" 
                            class="piece piece-noire piece-noire-' . $directionClass . '" 
                            disabled>NN</button>';
        }
    }

    /**
     * Génère le bouton pour une pièce en fonction de sa couleur
     *
     * @param int $x Coordonnée X de la pièce
     * @param int $y Coordonnée Y de la pièce
     * @param PieceSquadro $piece La pièce à représenter
     * @param bool $estJouable Indique si la pièce peut être jouée
     * @return string Bouton HTML de la pièce
     */
    public function generatePiece(int $x, int $y, PieceSquadro $piece, bool $estJouable): string {
        $couleur = $piece->getCouleur();
        $direction = $piece->getDirection();

        switch ($couleur) {
            case PieceSquadro::BLANC:
                return $this->generatePieceBlanche($x, $y, $direction, $estJouable);
            case PieceSquadro::NOIR:
                return $this->generatePieceNoire($x, $y, $direction, $estJouable);
            case PieceSquadro::VIDE:
                return $this->generateCaseVide();
            case PieceSquadro::NEUTRE:
                return $this->generateCaseNeutre();
            default:
                return '<button type="button" disabled>?</button>';
        }
    }

    /**
     * Crée un formulaire HTML pour l'interaction
     *
     * @param string $action URL de destination du formulaire
     * @param string $method Méthode HTTP (GET ou POST)
     * @return string Code HTML du formulaire
     */
    public function createForm(string $action, string $method = 'POST'): string {
        return '<form id="' . $this->nomFormulaire . '" action="' . $action . '" method="' . $method . '"></form>';
    }

    /**
     * Génère le CSS pour les boutons et pièces
     *
     * @return string Code CSS
     */
    public function generateCSS(): string {
        return '
        <style>
            .piece {
                width: 50px;
                height: 50px;
                border-radius: 5px;
                font-weight: bold;
                display: flex;
                justify-content: center;
                align-items: center;
                cursor: default;
            }

            .piece-blanche {
                background-color: white;
                color: black;
                border: 2px solid black;
            }

            .piece-noire {
                background-color: black;
                color: white;
                border: 2px solid white;
            }

            .piece-blanche.est::after, 
            .piece-blanche.ouest::after,
            .piece-noire.nord::after, 
            .piece-noire.sud::after {
                content: "";
                position: absolute;
                font-size: 12px;
            }

            .piece-blanche.est::after { content: "→"; }
            .piece-blanche.ouest::after { content: "←"; }
            .piece-noire.nord::after { content: "↑"; }
            .piece-noire.sud::after { content: "↓"; }

            .piece:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }

            .case-vide, .case-neutre {
                background-color: #f0f0f0;
                border: 1px solid #ccc;
                color: #999;
            }

            .piece.jouable {
                cursor: pointer;
                box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
            }
        </style>';
    }
}
?>