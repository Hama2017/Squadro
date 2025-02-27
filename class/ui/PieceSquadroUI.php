<?php
/**
 * Classe PieceSquadroUI
 *
 * Cette classe est responsable de générer le code HTML pour les différentes pièces
 * et cases du jeu Squadro.
 *
 * @author Claude
 */
class PieceSquadroUI {
    /** @var int Le joueur actif (PieceSquadro::BLANC ou PieceSquadro::NOIR) */
    private int $joueurActif;

    /** @var string Le nom du formulaire utilisé pour envoyer les coordonnées */
    private string $nomFormulaire;

    /**
     * Constructeur de la classe PieceSquadroUI
     *
     * @param int $joueurActif Le joueur actif (PieceSquadro::BLANC ou PieceSquadro::NOIR)
     * @param string $nomFormulaire Le nom du formulaire pour l'action
     */
    public function __construct(int $joueurActif, string $nomFormulaire = 'formulaireSquadro') {
        $this->joueurActif = $joueurActif;
        $this->nomFormulaire = $nomFormulaire;
    }

    /**
     * Génère le code HTML pour une case vide
     *
     * @param int $x Coordonnée X
     * @param int $y Coordonnée Y
     * @return string Code HTML pour une case vide
     */
    public function generateCaseVide(int $x, int $y): string {
        return '<button type="button" class="case-vide" disabled>·</button>';
    }

    /**
     * Génère le code HTML pour une case neutre (coins du plateau)
     *
     * @param int $x Coordonnée X
     * @param int $y Coordonnée Y
     * @return string Code HTML pour une case neutre
     */
    public function generateCaseNeutre(int $x, int $y): string {
        return '<button type="button" class="case-neutre" disabled>×</button>';
    }

    /**
     * Génère le code HTML pour une pièce blanche
     *
     * @param int $x Coordonnée X
     * @param int $y Coordonnée Y
     * @param int $direction Direction de la pièce (PieceSquadro::EST ou PieceSquadro::OUEST)
     * @param bool $estJouable Indique si la pièce est jouable par le joueur actif
     * @return string Code HTML pour une pièce blanche
     */
    public function generatePieceBlanche(int $x, int $y, int $direction, bool $estJouable = false): string {
        // Déterminer si la pièce est jouable (si le joueur actif est BLANC et la pièce est jouable)
        $jouable = ($this->joueurActif === PieceSquadro::BLANC && $estJouable);

        // Définir la classe CSS en fonction de la direction et si elle est jouable
        $directionClass = ($direction === PieceSquadro::EST) ? 'piece-blanche-est' : 'piece-blanche-ouest';
        $activeClass = $jouable ? 'jouable' : '';

        if ($jouable) {
            // Si la pièce est jouable, on crée un bouton qui soumet le formulaire avec les coordonnées
            return '<button type="submit" name="piece" value="' . $x . ',' . $y . '" 
                    form="' . $this->nomFormulaire . '" 
                    class="piece-blanche ' . $directionClass . ' ' . $activeClass . '">
                    ◯</button>';
        } else {
            // Sinon, on crée un bouton désactivé
            return '<button type="button" 
                    class="piece-blanche ' . $directionClass . '" 
                    disabled>◯</button>';
        }
    }

    /**
     * Génère le code HTML pour une pièce noire
     *
     * @param int $x Coordonnée X
     * @param int $y Coordonnée Y
     * @param int $direction Direction de la pièce (PieceSquadro::NORD ou PieceSquadro::SUD)
     * @param bool $estJouable Indique si la pièce est jouable par le joueur actif
     * @return string Code HTML pour une pièce noire
     */
    public function generatePieceNoire(int $x, int $y, int $direction, bool $estJouable = false): string {
        // Déterminer si la pièce est jouable (si le joueur actif est NOIR et la pièce est jouable)
        $jouable = ($this->joueurActif === PieceSquadro::NOIR && $estJouable);

        // Définir la classe CSS en fonction de la direction et si elle est jouable
        $directionClass = ($direction === PieceSquadro::NORD) ? 'piece-noire-nord' : 'piece-noire-sud';
        $activeClass = $jouable ? 'jouable' : '';

        if ($jouable) {
            // Si la pièce est jouable, on crée un bouton qui soumet le formulaire avec les coordonnées
            return '<button type="submit" name="piece" value="' . $x . ',' . $y . '" 
                    form="' . $this->nomFormulaire . '" 
                    class="piece-noire ' . $directionClass . ' ' . $activeClass . '">
                    ●</button>';
        } else {
            // Sinon, on crée un bouton désactivé
            return '<button type="button" 
                    class="piece-noire ' . $directionClass . '" 
                    disabled>●</button>';
        }
    }

    /**
     * Génère le code HTML pour une pièce basée sur son type, sa couleur et sa direction
     *
     * @param int $x Coordonnée X
     * @param int $y Coordonnée Y
     * @param PieceSquadro $piece Instance de la pièce
     * @param bool $estJouable Indique si la pièce est jouable
     * @return string Code HTML pour la pièce
     */
    public function generatePiece(int $x, int $y, PieceSquadro $piece, bool $estJouable = false): string {
        $couleur = $piece->getCouleur();
        $direction = $piece->getDirection();

        switch ($couleur) {
            case PieceSquadro::BLANC:
                return $this->generatePieceBlanche($x, $y, $direction, $estJouable);
            case PieceSquadro::NOIR:
                return $this->generatePieceNoire($x, $y, $direction, $estJouable);
            case PieceSquadro::VIDE:
                return $this->generateCaseVide($x, $y);
            case PieceSquadro::NEUTRE:
                return $this->generateCaseNeutre($x, $y);
            default:
                return '<button type="button" disabled>?</button>';
        }
    }

    /**
     * Crée un formulaire HTML pour l'interaction avec le jeu
     *
     * @param string $action URL de l'action du formulaire
     * @param string $method Méthode HTTP (GET ou POST)
     * @return string Code HTML du formulaire
     */
    public function createForm(string $action, string $method = 'POST'): string {
        return '<form id="' . $this->nomFormulaire . '" action="' . $action . '" method="' . $method . '"></form>';
    }

    /**
     * Génère le CSS nécessaire pour l'affichage des pièces et du plateau
     *
     * @return string Code CSS pour les pièces et le plateau
     */
    public function generateCSS(): string {
        return '
        <style>
            /* Styles du plateau */
            .plateau-squadro {
                display: grid;
                grid-template-columns: repeat(7, 50px);
                grid-template-rows: repeat(7, 50px);
                gap: 2px;
                margin: 20px auto;
                max-width: 400px;
            }
            
            /* Styles des boutons */
            .plateau-squadro button {
                width: 50px;
                height: 50px;
                font-size: 24px;
                border-radius: 5px;
                border: 1px solid #ccc;
                background-color: #f8f8f8;
                cursor: default;
            }
            
            /* Cases vides */
            .case-vide {
                background-color: #f0f0f0 !important;
            }
            
            /* Cases neutres (coins) */
            .case-neutre {
                background-color: #d0d0d0 !important;
            }
            
            /* Pièces blanches */
            .piece-blanche {
                background-color: #ffffff !important;
                color: #000000;
                border: 2px solid #000000 !important;
            }
            
            /* Pièces noires */
            .piece-noire {
                background-color: #000000 !important;
                color: #ffffff;
                border: 2px solid #ffffff !important;
            }
            
            /* Directions des pièces */
            .piece-blanche-est::after, .piece-blanche-ouest::after,
            .piece-noire-nord::after, .piece-noire-sud::after {
                font-size: 14px;
                position: relative;
                top: -5px;
            }
            
            .piece-blanche-est::after {
                content: "→";
            }
            
            .piece-blanche-ouest::after {
                content: "←";
            }
            
            .piece-noire-nord::after {
                content: "↑";
            }
            
            .piece-noire-sud::after {
                content: "↓";
            }
            
            /* Pièces jouables */
            .jouable {
                cursor: pointer !important;
                box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
            }
            
            .piece-blanche.jouable:hover {
                background-color: #e0e0e0 !important;
            }
            
            .piece-noire.jouable:hover {
                background-color: #333333 !important;
            }
            
            /* Informations de jeu */
            .info-jeu {
                text-align: center;
                margin: 20px auto;
                max-width: 400px;
                padding: 10px;
                background-color: #f0f0f0;
                border-radius: 5px;
            }
            
            /* Message de victoire */
            .message-victoire {
                text-align: center;
                font-size: 24px;
                color: green;
                margin: 20px auto;
                padding: 20px;
                background-color: #f0f8f0;
                border: 2px solid green;
                border-radius: 10px;
                max-width: 400px;
            }
            
            .boutons-action {
            text-align: center;
                margin: 20px auto;
                max-width: 400px;
            }

            .bouton-action {
            padding: 10px 20px;
                margin: 0 10px;
                font-size: 16px;
                border-radius: 5px;
                cursor: pointer;
            }

            .bouton-confirmer {
            background-color: #4CAF50;
                color: white;
                border: none;
            }

            .bouton-annuler {
            background-color: #f44336;
                color: white;
                border: none;
            }
        </style>';
    }
}
?>