<?php

// Prompt pour la correction : Voici quelques corrections pour la classe ActionSquadro
// ajoute une methode __toString()

require_once 'PlateauSquadro.php';
class ActionSquadro {
    private PlateauSquadro $plateau;

    public function __construct(PlateauSquadro $plateau) {
        $this->plateau = $plateau;
    }


    public function estJouablePiece(int $x, int $y): bool {
        $piece = $this->plateau->getPiece($x, $y);

        if ($piece->getCouleur() === PieceSquadro::VIDE ||
            $piece->getCouleur() === PieceSquadro::NEUTRE) {
            return false;
        }

        [$destX, $destY] = $this->plateau->getCoordDestination($x, $y);

        if ($destX < 0 || $destX >= 7 || $destY < 0 || $destY >= 7) {
            return false;
        }

        return $this->plateau->getPiece($destX, $destY)->getCouleur() === PieceSquadro::VIDE;
    }


    public function jouePiece(int $x, int $y): void {
        if (!$this->estJouablePiece($x, $y)) {
            throw new Exception("Mouvement invalide");
        }

        $piece = $this->plateau->getPiece($x, $y);
        [$destX, $destY] = $this->plateau->getCoordDestination($x, $y);

        $couleur = $piece->getCouleur();
        $direction = $piece->getDirection();

        $this->plateau->setPiece(PieceSquadro::initVide(), $x, $y);
        $this->plateau->setPiece($piece, $destX, $destY);

        if ($this->estCaseRetournement($destX, $destY)) {
            $piece->inverseDirection();
        }

        $this->gererCaptures($x, $y, $destX, $destY);

        if ($this->estCaseFinParcours($destX, $destY, $direction)) {
            $this->sortPiece($couleur, $direction === PieceSquadro::EST ||
                            $direction === PieceSquadro::OUEST ? $x : $y);
        }
    }


    public function reculePiece(int $x, int $y): void {
        $piece = $this->plateau->getPiece($x, $y);
        $newX = $x;
        $newY = $y;


        if ($piece->getCouleur() === PieceSquadro::BLANC) {
            $newY = $piece->getDirection() === PieceSquadro::EST ? 0 : 6;
        } else {
            $newX = $piece->getDirection() === PieceSquadro::NORD ? 6 : 0;
        }


        $this->plateau->setPiece(PieceSquadro::initVide(), $x, $y);
        $this->plateau->setPiece($piece, $newX, $newY);
    }


    public function sortPiece(int $couleur, int $rang): void {
        if ($couleur === PieceSquadro::BLANC) {
            $this->plateau->retireLigneJouable($rang);
        } else {
            $this->plateau->retireColonneJouable($rang);
        }
    }


    public function remporteVictoire(int $couleur): bool {
        $piecesRestantes = $couleur === PieceSquadro::BLANC ?
            count($this->plateau->getLignesJouables()) :
            count($this->plateau->getColonnesJouables());

        return $piecesRestantes <= 1;
    }

    private function estCaseRetournement(int $x, int $y): bool {
        return ($x === 0 || $x === 6 || $y === 0 || $y === 6) &&
               $this->plateau->getPiece($x, $y)->getCouleur() !== PieceSquadro::NEUTRE;
    }

    private function estCaseFinParcours(int $x, int $y, int $direction): bool {
        return ($direction === PieceSquadro::OUEST && $y === 0) ||
               ($direction === PieceSquadro::EST && $y === 6) ||
               ($direction === PieceSquadro::NORD && $x === 0) ||
               ($direction === PieceSquadro::SUD && $x === 6);
    }

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


    public function toJson(): string {
        return json_encode([
            'plateau' => $this->plateau->toJson()
        ]);
    }

    public static function fromJson(string $json): ActionSquadro {
        $data = json_decode($json, true);
        return new ActionSquadro(PlateauSquadro::fromJson($data['plateau']));
    }

    // Correction : ajout de la methode __toString()

    public function __toString(): string {
        return $this->plateau->__toString();
    }
}
?>
