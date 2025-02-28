<?php
/**
 * Classe PDOSquadro
 *
 * Gère les accès à la base de données pour le jeu Squadro
 */
class PDOSquadro
{
    /** @var PDO Instance PDO pour la connexion à la base de données */
    private static PDO $pdo;

    /**
     * Initialise la connexion PDO avec la base de données
     *
     * @param string $sgbd Type de SGBD ('mysql' ou 'pgsql')
     * @param string $host Hôte de la base de données
     * @param string $db Nom de la base de données
     * @param string $user Identifiant de connexion
     * @param string $password Mot de passe de connexion
     * @throws Exception Si le type de SGBD est incorrect
     */
    public static function initPDO(string $sgbd, string $host, string $db, string $user, string $password): void
    {
        switch ($sgbd) {
            case 'mysql':
                // Connexion à MySQL
                self::$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
                break;

            case 'pgsql':
                self::$pdo = new PDO("pgsql:host=$host dbname=$db user=$user password=$password");
                break;

            default:
                throw new Exception("Type de SGBD non correct : $sgbd fourni, 'mysql' ou 'pgsql' attendu");
        }

        // Pour récupérer aussi les exceptions provenant de PDOStatement
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /******** Gestion des requêtes relatives à JoueurSquadro *************/

    /**
     * Crée un nouveau joueur dans la base de données
     *
     * @param string $name Nom du joueur
     * @return JoueurSquadro Instance du joueur créé
     */
    public static function createPlayer(string $name): JoueurSquadro {
        $stmt = self::$pdo->prepare("INSERT INTO JoueurSquadro(joueurNom) VALUES (:name)");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        $id = self::$pdo->lastInsertId();
        return new JoueurSquadro($id, $name);
    }

    /**
     * Sélectionne un joueur par son nom
     *
     * @param string $name Nom du joueur
     * @return JoueurSquadro|null Instance du joueur ou null si non trouvé
     */
    public static function selectPlayerByName(string $name): ?JoueurSquadro {
        $stmt = self::$pdo->prepare("SELECT * FROM JoueurSquadro WHERE joueurNom = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new JoueurSquadro($data['id'], $data['joueurNom']);
        }
        return null;
    }

    /**
     * Sélectionne un joueur par son identifiant
     *
     * @param int $id Identifiant du joueur
     * @return JoueurSquadro|null Instance du joueur ou null si non trouvé
     */
    public static function selectPlayerById(int $id): ?JoueurSquadro {
        $stmt = self::$pdo->prepare("SELECT * FROM JoueurSquadro WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new JoueurSquadro($data['id'], $data['joueurNom']);
        }
        return null;
    }

    /******** Gestion des requêtes relatives à PartieSquadro *************/

    /**
     * Récupère les parties en attente d'un second joueur
     * (parties initialisées auxquelles le joueur ne participe pas)
     *
     * @param int $playerId Identifiant du joueur
     * @return array Liste des parties en attente
     */
    public static function getWaitingGamesForPlayer(int $playerId): array {
        $stmt = self::$pdo->prepare("
            SELECT * FROM PartieSquadro 
            WHERE gameStatus = 'initialized' 
            AND playerOne != :playerId 
            AND playerTwo IS NULL
        ");
        $stmt->bindParam(':playerId', $playerId);
        $stmt->execute();

        $parties = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $playerOne = self::selectPlayerById($data['playerOne']);
            $parties[] = new PartieSquadro(
                $data['partieId'],
                $playerOne,
                null,
                $data['gameStatus'],
                $data['json']
            );
        }

        return $parties;
    }

    /**
     * Crée une nouvelle partie dans la base de données
     *
     * @param string $playerName Nom du joueur créant la partie
     * @param string $json Représentation JSON de la partie
     * @return int Identifiant de la partie créée
     */
    public static function createPartieSquadro(string $playerName, string $json): int {
        $player = self::selectPlayerByName($playerName);
        $playerId = $player->getId();
        $stmt = self::$pdo->prepare("INSERT INTO PartieSquadro(playerOne, gameStatus, json) VALUES (:playerId, 'initialized', :json)");
        $stmt->bindParam(':playerId', $playerId);
        $stmt->bindParam(':json', $json);
        $stmt->execute();
        return (int)self::$pdo->lastInsertId();
    }

    /**
     * Met à jour l'état d'une partie dans la base de données
     *
     * @param string $gameStatus Nouvel état de la partie
     * @param string $json Nouvelle représentation JSON de la partie
     * @param int $partieId Identifiant de la partie à mettre à jour
     */
    public static function savePartieSquadro(string $gameStatus, string $json, int $partieId): void {
        $stmt = self::$pdo->prepare("UPDATE PartieSquadro SET gameStatus = :status, json = :json WHERE partieId = :id");
        $stmt->bindParam(':status', $gameStatus);
        $stmt->bindParam(':json', $json);
        $stmt->bindParam(':id', $partieId);
        $stmt->execute();
    }

    /**
     * Ajoute un second joueur à une partie existante
     *
     * @param string $playerName Nom du joueur à ajouter
     * @param string $json Nouvelle représentation JSON de la partie
     * @param int $gameId Identifiant de la partie
     */
    public static function addPlayerToPartieSquadro(string $playerName, string $json, int $gameId): void {
        $player = self::selectPlayerByName($playerName);
        $playerId = $player->getId();
        $stmt = self::$pdo->prepare("UPDATE PartieSquadro SET playerTwo = :playerId, gameStatus = 'waitingForPlayer', json = :json WHERE partieId = :id");
        $stmt->bindParam(':playerId', $playerId);
        $stmt->bindParam(':json', $json);
        $stmt->bindParam(':id', $gameId);
        $stmt->execute();
    }

    /**
     * Récupère une partie par son identifiant
     *
     * @param int $gameId Identifiant de la partie
     * @return PartieSquadro|null Instance de la partie ou null si non trouvée
     */
    public static function getPartieSquadroById(int $gameId): ?PartieSquadro {
        $stmt = self::$pdo->prepare("SELECT * FROM PartieSquadro WHERE partieId = :id");
        $stmt->bindParam(':id', $gameId);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $playerOne = self::selectPlayerById($data['playerOne']);
            $playerTwo = $data['playerTwo'] ? self::selectPlayerById($data['playerTwo']) : null;
            return new PartieSquadro($data['partieId'], $playerOne, $playerTwo, $data['gameStatus'], $data['json']);
        }
        return null;
    }

    /**
     * Récupère toutes les parties de la base de données
     *
     * @return array Tableau des parties
     */
    public static function getAllPartieSquadro(): array {
        $stmt = self::$pdo->query("SELECT * FROM PartieSquadro");
        $parties = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $playerOne = self::selectPlayerById($data['playerOne']);
            $playerTwo = $data['playerTwo'] ? self::selectPlayerById($data['playerTwo']) : null;
            $parties[] = new PartieSquadro($data['partieId'], $playerOne, $playerTwo, $data['gameStatus'], $data['json']);
        }
        return $parties;
    }

    /**
     * Récupère toutes les parties associées à un joueur
     *
     * @param string $playerName Nom du joueur
     * @return array Tableau des parties du joueur
     */
    public static function getAllPartieSquadroByPlayerName(string $playerName): array {
        $player = self::selectPlayerByName($playerName);
        $playerId = $player->getId();
        $stmt = self::$pdo->prepare("SELECT * FROM PartieSquadro WHERE playerOne = :playerId OR playerTwo = :playerId");
        $stmt->bindParam(':playerId', $playerId);
        $stmt->execute();
        $parties = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $playerOne = self::selectPlayerById($data['playerOne']);
            $playerTwo = $data['playerTwo'] ? self::selectPlayerById($data['playerTwo']) : null;
            $parties[] = new PartieSquadro($data['partieId'], $playerOne, $playerTwo, $data['gameStatus'], $data['json']);
        }
        return $parties;
    }

    /**
     * Récupère l'identifiant de la dernière partie créée par un joueur
     *
     * @param string $playerName Nom du joueur
     * @return int Identifiant de la dernière partie ou 0 si aucune
     */
    public static function getLastGameIdForPlayer(string $playerName): int {
        $player = self::selectPlayerByName($playerName);
        $playerId = $player->getId();
        $stmt = self::$pdo->prepare("SELECT partieId FROM PartieSquadro WHERE playerOne = :playerId ORDER BY partieId DESC LIMIT 1");
        $stmt->bindParam(':playerId', $playerId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['partieId'] : 0;
    }
}