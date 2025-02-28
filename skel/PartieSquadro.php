<?php

class PartieSquadro {

    const PLAYER_ONE = 0;
    const PLAYER_TWO = 1;

    private $partieId = 0;
    private $joueurs = [];
    private $joueurActif = self::PLAYER_ONE;
    private $gameStatus = 'initialized';
    private $plateau;

    public function __construct(JoueurSquadro $playerOne) {
        $this->addJoueur($playerOne);
        $this->plateau = new PlateauSquadro(); // Crée un plateau vide
    }

    public function addJoueur(JoueurSquadro $player): void {
        if (count($this->joueurs) < 2) {
            $this->joueurs[] = $player;
        } else {
            throw new Exception("Maximum de 2 joueurs atteint");
        }
    }

    public function getJoueurActif(): JoueurSquadro {
        return $this->joueurs[$this->joueurActif];
    }

    public function getNomJoueurActif(): string {
        return $this->getJoueurActif()->getJoueurNom();
    }

    public function __toString(): string {
        return "PartieSquadro { partieId: " . $this->partieId . ", statut: " . $this->gameStatus . " }";
    }

    public function getPartieID(): int {
        return $this->partieId;
    }

    public function setPartieID(int $id): void {
        $this->partieId = $id;
    }

    public function getJoueurs(): array {
        return $this->joueurs;
    }

    public function toJson(int $id): string {
        return json_encode([
            'partieId' => $id,
            'joueurs' => array_map(function($joueur) {
                return json_decode($joueur->toJson(), true);
            }, $this->joueurs),
            'joueurActif' => $this->joueurActif,
            'gameStatus' => $this->gameStatus
        ]);
    }

    public static function fromJson(string $json): PartieSquadro {
        $data = json_decode($json, true);
        $partie = new PartieSquadro(new JoueurSquadro($data['joueurs'][0]['joueurNom'], $data['joueurs'][0]['id']));
        $partie->setPartieID($data['partieId']);
        $partie->gameStatus = $data['gameStatus'];
        foreach ($data['joueurs'] as $joueurData) {
            $partie->addJoueur(JoueurSquadro::fromJson(json_encode($joueurData)));
        }
        $partie->joueurActif = $data['joueurActif'];
        return $partie;
    }
}
?>
