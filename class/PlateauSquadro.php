<?php

// Prompt pour la correction : Voici quelques corrections pour la classe PlateauSquadro
// ajoute une methode __toString(), fait pas l'initialisation de lignesJouables et colonnesJouables dans le constructeur,

require_once 'PieceSquadro.php';
require_once 'ArrayPieceSquadro.php';

class PlateauSquadro {
    // Constantes de vitesse
    public const BLANC_V_ALLER = [0, 1, 3, 2, 3, 1, 0];
    public const BLANC_V_RETOUR = [0, 3, 1, 2, 1, 3, 0];
    public const NOIR_V_ALLER = [0, 3, 1, 2, 1, 3, 0];
    public const NOIR_V_RETOUR = [0, 1, 3, 2, 3, 1, 0];

    private array $plateau = [];

    // Correction : initialisation effectuer directement
    private array $lignesJouables = [1, 2, 3, 4, 5];
    private array $colonnesJouables = [1, 2, 3, 4, 5];

    public function __construct() {
        $this->initCasesVides();
        $this->initCasesNeutres();
        $this->initCasesBlanches();
        $this->initCasesNoires();
    }

    private function initCasesVides(): void {
        for ($i = 0; $i < 7; $i++) {
            for ($j = 0; $j < 7; $j++) {
                $this->plateau[$i][$j] = PieceSquadro::initVide();
            }
        }
    }

    private function initCasesNeutres(): void {
        $this->plateau[0][0] = PieceSquadro::initNeutre();
        $this->plateau[0][6] = PieceSquadro::initNeutre();
        $this->plateau[6][0] = PieceSquadro::initNeutre();
        $this->plateau[6][6] = PieceSquadro::initNeutre();
    }

    private function initCasesBlanches(): void {
        for ($i = 1; $i <= 5; $i++) {
            $this->plateau[$i][0] = PieceSquadro::initBlancEst();
        }
    }

    private function initCasesNoires(): void {
        for ($j = 1; $j <= 5; $j++) {
            $this->plateau[6][$j] = PieceSquadro::initNoirNord();
        }
    }

    public function getPlateau(): array {
        return $this->plateau;
    }

    public function getPiece(int $x, int $y): PieceSquadro {
        if ($x < 0 || $x >= 7 || $y < 0 || $y >= 7) {
            throw new InvalidArgumentException("Coordonnée ($x, $y) hors limites.");
        }
        return $this->plateau[$x][$y];
    }

    public function setPiece(PieceSquadro $piece, int $x, int $y): void {
        if ($x < 0 || $x >= 7 || $y < 0 || $y >= 7) {
            throw new InvalidArgumentException("Coordonnée ($x, $y) hors limites.");
        }
        $this->plateau[$x][$y] = $piece;
    }

    public function getLignesJouables(): array {
        return $this->lignesJouables;
    }

    public function getColonnesJouables(): array {
        return $this->colonnesJouables;
    }

    public function retireLigneJouable(int $index): void {
        $this->lignesJouables = array_values(array_filter($this->lignesJouables, fn($val) => $val !== $index));
    }

    public function retireColonneJouable(int $index): void {
        $this->colonnesJouables = array_values(array_filter($this->colonnesJouables, fn($val) => $val !== $index));
    }

    public function getCoordDestination(int $x, int $y): array {
        $piece = $this->getPiece($x, $y);
        $vitesse = $this->getVitesse($piece, $x, $y);

        switch ($piece->getDirection()) {
            case PieceSquadro::NORD:
                return [$x - $vitesse, $y];
            case PieceSquadro::SUD:
                return [$x + $vitesse, $y];
            case PieceSquadro::EST:
                return [$x, $y + $vitesse];
            case PieceSquadro::OUEST:
                return [$x, $y - $vitesse];
            default:
                return [$x, $y];
        }
    }

    private function getVitesse(PieceSquadro $piece, int $x, int $y): int {
        if ($piece->getCouleur() === PieceSquadro::BLANC) {
            return $piece->getDirection() === PieceSquadro::EST ?
                self::BLANC_V_ALLER[$x] : self::BLANC_V_RETOUR[$x];
        } else {
            return $piece->getDirection() === PieceSquadro::NORD ?
                self::NOIR_V_ALLER[$y] : self::NOIR_V_RETOUR[$y];
        }
    }

    public function getDestination(int $x, int $y): PieceSquadro {
        $coords = $this->getCoordDestination($x, $y);
        return $this->getPiece($coords[0], $coords[1]);
    }

    public function toJson(): string {
        return json_encode([
            'plateau' => array_map(fn($row) => array_map(fn($piece) => $piece->toJson(), $row), $this->plateau),
            'lignesJouables' => $this->lignesJouables,
            'colonnesJouables' => $this->colonnesJouables
        ]);
    }

    public static function fromJson(string $json): PlateauSquadro {
        $data = json_decode($json, true);
        $plateau = new PlateauSquadro();
        $plateau->plateau = array_map(
            fn($row) => array_map(fn($pieceData) => PieceSquadro::fromJson($pieceData), $row),
            $data['plateau']
        );
        $plateau->lignesJouables = $data['lignesJouables'];
        $plateau->colonnesJouables = $data['colonnesJouables'];
        return $plateau;
    }


    // Correction : ajout de la methode __toString()

    public function __toString(): string {
        $result = "";
        for ($i = 0; $i < count($this->plateau); $i++) {
            for ($j = 0; $j < count($this->plateau[$i]); $j++) {
                if (is_array($this->plateau[$i][$j])) {
                    $result .= "[" . implode(", ", $this->plateau[$i][$j]) . "] ";
                } else {
                    $result .= $this->plateau[$i][$j] . " ";
                }
            }
            $result .= "\n";
        }
        return $result;
    }
}
