<?php

// Prompt pour la correction : Voici quelques corrections pour la classe PieceSquadro
// couleur et direction sont en protected, ajoute la methode __toString(),
// mettre les méthodes en statique pour initialiser des pièces
// simplifie la methode inverseDirection(), utilise des self pour rendre plus simple l'ensemble du code,
// pour initVide() utilise NEUTRE comme direction

class PieceSquadro
{
    public const BLANC = 0;
    public const NOIR = 1;
    public const VIDE = -1;
    public const NEUTRE = -2;

    public const NORD = 0;
    public const EST = 1;
    public const SUD = 2;
    public const OUEST = 3;

    // Correction : `couleur` et `direction` passent en `protected` pour un accès contrôlé dans les sous-classes
    protected int $couleur;
    protected int $direction;

    // Constructeur privé
    private function __construct(int $couleur, int $direction)
    {
        $this->couleur = $couleur;
        $this->direction = $direction;
    }

    // Correction : ajout du mot cle statique pour rendre les methode static
    public static function initVide(): self
    {
        return new self(self::VIDE, self::NEUTRE);
    }

    public static function initNoirNord(): self
    {
        return new self(self::NOIR, self::NORD);
    }

    public static function initNoirSud(): self
    {
        return new self(self::NOIR, self::SUD);
    }

    public static function initBlancEst(): self
    {
        return new self(self::BLANC, self::EST);
    }

    public static function initBlancOuest(): self
    {
        return new self(self::BLANC, self::OUEST);
    }

    public static function initNeutre(): self
    {
        return new self(self::NEUTRE, self::NEUTRE);
    }

    public function getCouleur(): int
    {
        return $this->couleur;
    }

    public function getDirection(): int
    {
        return $this->direction;
    }

    // Correction : methode inverseDirection() plus simple
    public function inverseDirection(): void
    {
        $this->direction = ($this->direction + 2) % 4;
    }

    public function toJson(): string
    {
        return json_encode([
            'couleur' => $this->couleur,
            'direction' => $this->direction,
        ]);
    }

    public static function fromJson(string $json): self
    {
        // Ajouter une gestion robuste de la désérialisation
        try {
            // Vérifier si le JSON est déjà un tableau
            $data = json_decode($json, true);

            // Si $data est null, le décodage a échoué
            if ($data === null) {
                error_log("Erreur de décodage JSON dans PieceSquadro::fromJson: " . json_last_error_msg() . ", JSON reçu: " . substr($json, 0, 100));
                return self::initVide(); // Retourner une pièce vide par défaut
            }

            // Si les clés 'couleur' et 'direction' ne sont pas présentes
            if (!isset($data['couleur']) || !isset($data['direction'])) {
                // Tenter de traiter le cas où le JSON est doublement encodé
                if (is_array($data) && isset($data[0]) && is_string($data[0])) {
                    $innerData = json_decode($data[0], true);
                    if (isset($innerData['couleur']) && isset($innerData['direction'])) {
                        return new self($innerData['couleur'], $innerData['direction']);
                    }
                }

                // Gestion pour des formats de données inattendus
                error_log("Les clés 'couleur' et 'direction' n'existent pas dans PieceSquadro::fromJson, data: " . print_r($data, true));
                return self::initVide(); // Retourner une pièce vide par défaut
            }

            // Construction normale
            return new self($data['couleur'], $data['direction']);
        } catch (Exception $e) {
            error_log("Exception dans PieceSquadro::fromJson: " . $e->getMessage());
            return self::initVide(); // Retourner une pièce vide par défaut
        }
    }

    // Correction : ajout de la methode __toString()
    public function __toString(): string
    {
        return "Couleur: {$this->couleur}, Direction: {$this->direction}";
    }
}