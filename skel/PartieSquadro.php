<?php
/**
 * Classe PartieSquadro
 *
 * Représente une partie de jeu Squadro
 */
class PartieSquadro {
    /** Constantes pour identifier les joueurs */
    const PLAYER_ONE = 0;
    const PLAYER_TWO = 1;

    /** @var int Identifiant de la partie */
    private int $partieId;

    /** @var array Tableau des joueurs de la partie */
    private array $joueurs = [];

    /** @var int Joueur actuellement actif */
    private int $joueurActif = self::PLAYER_ONE;

    /** @var string Statut de la partie */
    private string $gameStatus = 'initialized';

    /** @var string Représentation JSON du plateau de jeu */
    private string $json;

    /**
     * Constructeur
     *
     * @param int $partieId Identifiant de la partie
     * @param JoueurSquadro $playerOne Premier joueur
     * @param JoueurSquadro|null $playerTwo Second joueur (optionnel)
     * @param string $gameStatus Statut de la partie
     * @param string $json Représentation JSON de la partie
     */
    public function __construct(int $partieId, JoueurSquadro $playerOne, ?JoueurSquadro $playerTwo = null, string $gameStatus = 'initialized', string $json = '') {
        $this->partieId = $partieId;
        $this->joueurs[self::PLAYER_ONE] = $playerOne;

        if ($playerTwo !== null) {
            $this->joueurs[self::PLAYER_TWO] = $playerTwo;
        }

        $this->gameStatus = $gameStatus;
        $this->json = $json;
    }

    /**
     * Ajoute un joueur à la partie
     *
     * @param JoueurSquadro $player Joueur à ajouter
     * @throws Exception Si la partie a déjà deux joueurs
     */
    public function addJoueur(JoueurSquadro $player): void {
        if (count($this->joueurs) < 2) {
            $this->joueurs[] = $player;
        } else {
            throw new Exception("Maximum de 2 joueurs atteint");
        }
    }

    /**
     * Retourne le joueur actif
     *
     * @return JoueurSquadro Joueur actif
     */
    public function getJoueurActif(): JoueurSquadro {
        return $this->joueurs[$this->joueurActif];
    }

    /**
     * Retourne le nom du joueur actif
     *
     * @return string Nom du joueur actif
     */
    public function getNomJoueurActif(): string {
        return $this->getJoueurActif()->getJoueurNom();
    }

    /**
     * Retourne l'identifiant de la partie
     *
     * @return int Identifiant de la partie
     */
    public function getPartieID(): int {
        return $this->partieId;
    }

    /**
     * Modifie l'identifiant de la partie
     *
     * @param int $id Nouvel identifiant
     */
    public function setPartieID(int $id): void {
        $this->partieId = $id;
    }

    /**
     * Retourne le tableau des joueurs
     *
     * @return array Tableau des joueurs
     */
    public function getJoueurs(): array {
        return $this->joueurs;
    }

    /**
     * Retourne le statut de la partie
     *
     * @return string Statut de la partie
     */
    public function getGameStatus(): string {
        return $this->gameStatus;
    }

    /**
     * Modifie le statut de la partie
     *
     * @param string $status Nouveau statut
     */
    public function setGameStatus(string $status): void {
        $this->gameStatus = $status;
    }

    /**
     * Retourne la représentation JSON du plateau
     *
     * @return string Représentation JSON
     */
    public function getJson(): string {
        return $this->json;
    }

    /**
     * Modifie la représentation JSON du plateau
     *
     * @param string $json Nouvelle représentation JSON
     */
    public function setJson(string $json): void {
        $this->json = $json;
    }

    /**
     * Passe au joueur suivant
     */
    public function changeJoueurActif(): void {
        $this->joueurActif = ($this->joueurActif === self::PLAYER_ONE) ? self::PLAYER_TWO : self::PLAYER_ONE;
    }

    /**
     * Sérialise la partie au format JSON
     *
     * @return string Représentation JSON de la partie
     */
    public function toJson(): string {
        $joueurs = [];
        foreach ($this->joueurs as $joueur) {
            $joueurs[] = json_decode($joueur->toJson(), true);
        }

        return json_encode([
            'partieId' => $this->partieId,
            'joueurs' => $joueurs,
            'joueurActif' => $this->joueurActif,
            'gameStatus' => $this->gameStatus,
            'json' => $this->json
        ]);
    }

    /**
     * Désérialise une partie à partir d'une chaîne JSON
     *
     * @param string $json Représentation JSON de la partie
     * @return PartieSquadro Nouvelle instance de la partie
     */
    public static function fromJson(string $json): PartieSquadro {
        $data = json_decode($json, true);

        $playerOne = JoueurSquadro::fromJson(json_encode($data['joueurs'][0]));
        $playerTwo = null;

        if (isset($data['joueurs'][1])) {
            $playerTwo = JoueurSquadro::fromJson(json_encode($data['joueurs'][1]));
        }

        $partie = new PartieSquadro(
            $data['partieId'],
            $playerOne,
            $playerTwo,
            $data['gameStatus'],
            $data['json']
        );

        $partie->joueurActif = $data['joueurActif'];

        return $partie;
    }

    /**
     * Représentation textuelle de la partie
     *
     * @return string Représentation textuelle
     */
    public function __toString(): string {
        return "PartieSquadro { partieId: " . $this->partieId . ", statut: " . $this->gameStatus . " }";
    }
}