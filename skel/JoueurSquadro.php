<?php
/**
 * Classe JoueurSquadro
 *
 * Représente un joueur dans le jeu Squadro
 */
class JoueurSquadro {
    /** @var int Identifiant du joueur */
    private int $id;

    /** @var string Nom du joueur */
    private string $joueurNom;

    /**
     * Constructeur
     *
     * @param int $id Identifiant du joueur
     * @param string $joueurNom Nom du joueur
     */
    public function __construct(int $id, string $joueurNom) {
        $this->id = $id;
        $this->joueurNom = $joueurNom;
    }

    /**
     * Retourne l'identifiant du joueur
     *
     * @return int Identifiant du joueur
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * Modifie l'identifiant du joueur
     *
     * @param int $id Nouvel identifiant
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * Retourne le nom du joueur
     *
     * @return string Nom du joueur
     */
    public function getJoueurNom(): string {
        return $this->joueurNom;
    }

    /**
     * Modifie le nom du joueur
     *
     * @param string $joueurNom Nouveau nom
     */
    public function setJoueurNom(string $joueurNom): void {
        $this->joueurNom = $joueurNom;
    }

    /**
     * Sérialise le joueur au format JSON
     *
     * @return string Représentation JSON du joueur
     */
    public function toJson(): string {
        return json_encode([
            'id' => $this->id,
            'joueurNom' => $this->joueurNom
        ]);
    }

    /**
     * Désérialise un joueur à partir d'une chaîne JSON
     *
     * @param string $json Représentation JSON du joueur
     * @return JoueurSquadro Nouvelle instance du joueur
     */
    public static function fromJson(string $json): JoueurSquadro {
        $data = json_decode($json, true);
        return new JoueurSquadro($data['id'], $data['joueurNom']);
    }
}