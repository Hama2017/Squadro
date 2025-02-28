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

        // Vérifier si la position existe dans le plateau
        if (!isset($this->plateau[$x]) || !isset($this->plateau[$x][$y])) {
            error_log("Position ($x, $y) manquante dans le plateau. Initialisation avec une pièce vide.");
            $this->plateau[$x][$y] = PieceSquadro::initVide();
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
        // Log avant le retrait
        error_log("Tentative de retrait de la ligne jouable : $index");
        error_log("Lignes jouables avant retrait : " . implode(", ", $this->lignesJouables));

        // Filtrer et réindexer le tableau
        $this->lignesJouables = array_values(array_filter($this->lignesJouables, fn($val) => $val !== $index));

        // Log après le retrait
        error_log("Lignes jouables après retrait : " . implode(", ", $this->lignesJouables));
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
        // Débogage
        error_log("JSON reçu dans PlateauSquadro::fromJson: " . substr($json, 0, 100) . "...");

        // Créer un nouveau plateau correctement initialisé
        $plateau = new PlateauSquadro();

        try {
            $data = json_decode($json, true);

            // Vérifier si le décodage a réussi
            if ($data === null) {
                error_log("Erreur de décodage JSON: " . json_last_error_msg());
                // Si le décodage a échoué, retourner le nouveau plateau
                return $plateau;
            }

            // Vérifier la structure des données
            if (!isset($data['plateau'])) {
                error_log("La clé 'plateau' n'existe pas dans les données décodées");
                return $plateau; // Retourner le plateau déjà initialisé
            }

            // Si 'plateau' est une chaîne JSON et non un tableau, la décoder
            if (is_string($data['plateau'])) {
                $data['plateau'] = json_decode($data['plateau'], true);
                if ($data['plateau'] === null) {
                    error_log("Erreur de décodage du plateau: " . json_last_error_msg());
                    return $plateau; // Retourner le plateau déjà initialisé
                }
            }

            // Traiter les données du plateau si elles sont au format attendu
            if (is_array($data['plateau'])) {
                // Parcourir les données du plateau et mettre à jour le plateau initialisé
                for ($i = 0; $i < 7 && isset($data['plateau'][$i]); $i++) {
                    for ($j = 0; $j < 7 && isset($data['plateau'][$i][$j]); $j++) {
                        try {
                            $pieceData = $data['plateau'][$i][$j];
                            if (is_string($pieceData)) {
                                $piece = PieceSquadro::fromJson($pieceData);
                                $plateau->setPiece($piece, $i, $j);
                            } else if (is_array($pieceData)) {
                                $piece = PieceSquadro::fromJson(json_encode($pieceData));
                                $plateau->setPiece($piece, $i, $j);
                            }
                        } catch (Exception $e) {
                            error_log("Erreur lors du traitement de la pièce à ($i,$j): " . $e->getMessage());
                            // Conserver la pièce par défaut à cette position
                        }
                    }
                }
            } else {
                error_log("Le plateau n'est pas un tableau");
            }

            // Définir les lignes et colonnes jouables avec des valeurs par défaut si nécessaire
            if (isset($data['lignesJouables']) && is_array($data['lignesJouables'])) {
                $plateau->lignesJouables = $data['lignesJouables'];
            }

            if (isset($data['colonnesJouables']) && is_array($data['colonnesJouables'])) {
                $plateau->colonnesJouables = $data['colonnesJouables'];
            }

        } catch (Exception $e) {
            error_log("Exception lors du traitement du plateau: " . $e->getMessage());
            // En cas d'erreur, conserver le plateau initialisé
        }

        return $plateau;
    }

    // Correction : ajout de la methode __toString()
    public function __toString(): string {
        $result = "";
        for ($i = 0; $i < 7; $i++) {
            for ($j = 0; $j < 7; $j++) {
                if (isset($this->plateau[$i][$j])) {
                    if (is_array($this->plateau[$i][$j])) {
                        $result .= "[" . implode(", ", $this->plateau[$i][$j]) . "] ";
                    } else {
                        $result .= $this->plateau[$i][$j] . " ";
                    }
                } else {
                    $result .= "? ";
                }
            }
            $result .= "\n";
        }
        return $result;
    }
}