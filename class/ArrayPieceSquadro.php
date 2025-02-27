<?php

// Prompt pour la correction : Voici quelques corrections pour la classe ArrayPieceSquadro
// ajout de la methode add(),
// simplifie la methode fromJson()

require_once 'PieceSquadro.php';
class ArrayPieceSquadro implements ArrayAccess, Countable
{
    private array $pieces = [];

    public function __construct(array $pieces = [])
    {
        $this->pieces = $pieces;
    }

    // Correction : ajout de la methode add()
    public function add(PieceSquadro $piece): void
    {
        $this->pieces[] = $piece;
    }

    public function remove(int $index): void
    {
        if (isset($this->pieces[$index])) {
            unset($this->pieces[$index]);
            $this->pieces = array_values($this->pieces);
        }
    }

    public function count(): int
    {
        return count($this->pieces);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->pieces[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->pieces[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof PieceSquadro) {
            throw new InvalidArgumentException('La valeur doit etre une instance de PieceSquadro');
        }
        if ($offset === null) {
            $this->pieces[] = $value;
        } else {
            $this->pieces[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->pieces[$offset]);
    }

    public function toJson(): string
    {
        return json_encode(array_map(fn($piece) => [
            'couleur' => $piece->getCouleur(),
            'direction' => $piece->getDirection(),
        ], $this->pieces));
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true); // Décoder le JSON en tableau associatif

        $pieces = array_map(
            fn($pieceData) => PieceSquadro::fromJson(json_encode($pieceData)),
            $data
        );

        return new self($pieces);
    }

    public function __toString(): string
    {
        return implode(', ', array_map(fn($piece) => (string)$piece, $this->pieces));
    }


}
