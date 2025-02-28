<?php

// Version corrigée de la classe ActionSquadro
// - Correction du problème avec inverseDirection()
// - Amélioration de la gestion des mouvements et des captures

require_once 'PlateauSquadro.php';

class ActionSquadro {
    private PlateauSquadro $plateau;

    public function __construct(PlateauSquadro $plateau) {
        $this->plateau = $plateau;
    }

    /**
     * Vérifie si une pièce peut être jouée
     *
     * @param int $x Coordonnée X de la pièce
     * @param int $y Coordonnée Y de la pièce
     * @return bool True si la pièce peut être jouée
     */
    public function estJouablePiece(int $x, int $y): bool {
        $piece = $this->plateau->getPiece($x, $y);

        // Une case vide ou neutre n'est pas jouable
        if ($piece->getCouleur() === PieceSquadro::VIDE ||
            $piece->getCouleur() === PieceSquadro::NEUTRE) {
            return false;
        }

        // Calculer les coordonnées de destination
        [$destX, $destY] = $this->plateau->getCoordDestination($x, $y);

        // Vérifier si la destination est dans les limites du plateau
        if ($destX < 0 || $destX >= 7 || $destY < 0 || $destY >= 7) {
            return false;
        }

        // Vérifier si la case de destination est vide
        return $this->plateau->getPiece($destX, $destY)->getCouleur() === PieceSquadro::VIDE;
    }

    /**
     * Déplace une pièce selon les règles du jeu
     *
     * @param int $x Coordonnée X de la pièce à déplacer
     * @param int $y Coordonnée Y de la pièce à déplacer
     * @throws Exception Si le mouvement est invalide
     */
    public function jouePiece(int $x, int $y): void {
        // Vérifier si la pièce est jouable
        if (!$this->estJouablePiece($x, $y)) {
            throw new Exception("Mouvement invalide");
        }

        // Récupérer la pièce et ses propriétés
        $piece = $this->plateau->getPiece($x, $y);
        $couleur = $piece->getCouleur();
        $direction = $piece->getDirection();

        // Calculer la destination
        [$destX, $destY] = $this->plateau->getCoordDestination($x, $y);

        // CORRECTION: Déplacer la pièce en créant une nouvelle instance
        // au lieu de réutiliser l'ancienne référence
        $this->plateau->setPiece(PieceSquadro::initVide(), $x, $y);

        // Créer une nouvelle pièce avec les mêmes propriétés
        $nouvellePiece = null;
        if ($couleur === PieceSquadro::BLANC) {
            $nouvellePiece = ($direction === PieceSquadro::EST) ?
                PieceSquadro::initBlancEst() : PieceSquadro::initBlancOuest();
        } else {
            $nouvellePiece = ($direction === PieceSquadro::NORD) ?
                PieceSquadro::initNoirNord() : PieceSquadro::initNoirSud();
        }

        // Placer la nouvelle pièce à destination
        $this->plateau->setPiece($nouvellePiece, $destX, $destY);

        // CORRECTION: Gestion du retournement
        if ($this->estCaseRetournement($destX, $destY)) {
            // Récupérer la pièce à sa nouvelle position
            $pieceAInverser = $this->plateau->getPiece($destX, $destY);
            $directionInversee = ($pieceAInverser->getDirection() + 2) % 4; // Inverse la direction

            // Créer une nouvelle pièce avec la direction inversée
            $pieceInversee = null;
            if ($couleur === PieceSquadro::BLANC) {
                $pieceInversee = ($directionInversee === PieceSquadro::EST) ?
                    PieceSquadro::initBlancEst() : PieceSquadro::initBlancOuest();
            } else {
                $pieceInversee = ($directionInversee === PieceSquadro::NORD) ?
                    PieceSquadro::initNoirNord() : PieceSquadro::initNoirSud();
            }

            // Remplacer la pièce sur le plateau
            $this->plateau->setPiece($pieceInversee, $destX, $destY);
        }

        // Gérer les captures
        $this->gererCaptures($x, $y, $destX, $destY);
// Vérifier si la pièce a terminé son parcours
// Note: on utilise la direction d'origine car estCaseFinParcours vérifie
// si la pièce arrive à destination finale avec sa direction initiale
        error_log("Vérification fin de parcours");
        error_log("Coordonnées de destination : ($destX, $destY)");
        error_log("Direction originale : $direction");

        if ($this->estCaseFinParcours($destX, $destY, $direction)) {
            // Déterminer le rang (ligne pour blanc, colonne pour noir)
            $rang = ($direction === PieceSquadro::EST || $direction === PieceSquadro::OUEST) ? $x : $y;

            error_log("Fin de parcours détectée");
            error_log("Couleur de la pièce : $couleur");
            error_log("Rang : $rang");

            $this->sortPiece($couleur, $rang);

            error_log("Pièce sortie du plateau");
        }
    }

    /**
     * Recule une pièce à sa position initiale ou de retournement
     *
     * @param int $x Coordonnée X de la pièce à reculer
     * @param int $y Coordonnée Y de la pièce à reculer
     */
    public function reculePiece(int $x, int $y): void {
        $piece = $this->plateau->getPiece($x, $y);
        $couleur = $piece->getCouleur();
        $direction = $piece->getDirection();

        $newX = $x;
        $newY = $y;

        // Déterminer la nouvelle position selon la couleur et la direction
        if ($couleur === PieceSquadro::BLANC) {
            $newY = $direction === PieceSquadro::EST ? 0 : 6;
        } else {
            $newX = $direction === PieceSquadro::NORD ? 6 : 0;
        }

        // CORRECTION: Créer une nouvelle pièce avec les mêmes propriétés
        $nouvellePiece = null;
        if ($couleur === PieceSquadro::BLANC) {
            $nouvellePiece = ($direction === PieceSquadro::EST) ?
                PieceSquadro::initBlancEst() : PieceSquadro::initBlancOuest();
        } else {
            $nouvellePiece = ($direction === PieceSquadro::NORD) ?
                PieceSquadro::initNoirNord() : PieceSquadro::initNoirSud();
        }

        // Déplacer la pièce
        $this->plateau->setPiece(PieceSquadro::initVide(), $x, $y);
        $this->plateau->setPiece($nouvellePiece, $newX, $newY);
    }

    /**
     * Retire une pièce qui a terminé son parcours
     *
     * @param int $couleur Couleur de la pièce
     * @param int $rang Ligne ou colonne de la pièce
     */
    public function sortPiece(int $couleur, int $rang): void {
        if ($couleur === PieceSquadro::BLANC) {
            $this->plateau->setPiece(PieceSquadro::initNeutre(), $rang, 0);
            $this->plateau->retireLigneJouable($rang);

        } else {
            $this->plateau->setPiece(PieceSquadro::initNeutre(), 6, $rang);
            $this->plateau->retireColonneJouable($rang);
        }
    }

    /**
     * Vérifie si un joueur a gagné
     *
     * @param int $couleur Couleur du joueur
     * @return bool True si le joueur a gagné
     */
    public function remporteVictoire(int $couleur): bool {
        $piecesRestantes = $couleur === PieceSquadro::BLANC ?
            count($this->plateau->getLignesJouables()) :
            count($this->plateau->getColonnesJouables());

        return $piecesRestantes <= 1;
    }

    /**
     * Vérifie si une case est une case de retournement
     *
     * @param int $x Coordonnée X
     * @param int $y Coordonnée Y
     * @return bool True si c'est une case de retournement
     */
    private function estCaseRetournement(int $x, int $y): bool {
        return ($x === 0 || $x === 6 || $y === 0 || $y === 6) &&
            $this->plateau->getPiece($x, $y)->getCouleur() !== PieceSquadro::NEUTRE;
    }

    /**
     * Vérifie si une case est une case de fin de parcours pour la direction donnée
     *
     * @param int $x Coordonnée X
     * @param int $y Coordonnée Y
     * @param int $direction Direction de la pièce
     * @return bool True si c'est une case de fin de parcours
     */
    private function estCaseFinParcours(int $x, int $y, int $direction): bool {


        // Pour les pièces blanches (lignes)
        if ($direction === PieceSquadro::OUEST) {
            // A atteint le bord droit (y=6) et est revenue à sa position de départ (y=0)
            $retourComplet = $y === 0;
            return $retourComplet;
        }

        // Pour les pièces noires (colonnes)
        if ($direction === PieceSquadro::SUD) {
            // A atteint le bord supérieur (x=0) et est revenue à sa position de départ (x=6)
            $retourComplet = $x === 6;
            return $retourComplet;
        }

        return false;
    }
    /**
     * Gère les captures de pièces adverses
     *
     * @param int $startX Coordonnée X de départ
     * @param int $startY Coordonnée Y de départ
     * @param int $endX Coordonnée X d'arrivée
     * @param int $endY Coordonnée Y d'arrivée
     */
    private function gererCaptures(int $startX, int $startY, int $endX, int $endY): void {
        $piece = $this->plateau->getPiece($endX, $endY);

        if ($piece->getCouleur() === PieceSquadro::BLANC) {
            // Capture horizontale
            $minY = min($startY, $endY);
            $maxY = max($startY, $endY);

            for ($y = $minY + 1; $y < $maxY; $y++) {
                $pieceCapturee = $this->plateau->getPiece($startX, $y);
                if ($pieceCapturee->getCouleur() === PieceSquadro::NOIR) {
                    $this->reculePiece($startX, $y);
                }
            }
        } else {
            // Capture verticale
            $minX = min($startX, $endX);
            $maxX = max($startX, $endX);

            for ($x = $minX + 1; $x < $maxX; $x++) {
                $pieceCapturee = $this->plateau->getPiece($x, $startY);
                if ($pieceCapturee->getCouleur() === PieceSquadro::BLANC) {
                    $this->reculePiece($x, $startY);
                }
            }
        }
    }

    /**
     * Retourne la représentation JSON de l'objet
     *
     * @return string JSON
     */
    public function toJson(): string {
        return json_encode([
            'plateau' => $this->plateau->toJson()
        ]);
    }

    /**
     * Crée une instance d'ActionSquadro à partir d'une chaîne JSON
     *
     * @param string $json Représentation JSON
     * @return ActionSquadro Nouvelle instance
     */
    public static function fromJson(string $json): ActionSquadro {
        $data = json_decode($json, true);
        return new ActionSquadro(PlateauSquadro::fromJson($data['plateau']));
    }

    /**
     * Retourne une représentation textuelle de l'objet
     *
     * @return string Représentation textuelle
     */
    public function __toString(): string {
        return $this->plateau->__toString();
    }

    /**
     * Getter pour accéder au plateau
     *
     * @return PlateauSquadro Le plateau
     */
    public function getPlateau(): PlateauSquadro {
        return $this->plateau;
    }
}
?>