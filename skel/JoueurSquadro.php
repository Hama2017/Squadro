<?php

// Classe JoueurSquadro
class JoueurSquadro {
    private $joueurNom;
    private $id;

    public function __construct($joueurNom = '', $id = 0) {
        $this->joueurNom = $joueurNom;
        $this->id = $id;
    }

    public function getJoueurNom(): string {
        return $this->joueurNom;
    }

    public function setJoueurNom(string $nom): void {
        $this->joueurNom = $nom;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function toJson(): string {
        return json_encode([
            'joueurNom' => $this->joueurNom,
            'id' => $this->id
        ]);
    }

    public static function fromJson(string $json): JoueurSquadro {
        $data = json_decode($json, true);
        return new JoueurSquadro($data['joueurNom'], $data['id']);
    }
}
?>
