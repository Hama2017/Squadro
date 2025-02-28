<?php
class PDOSquadro
{
    private static PDO $pdo;

    public static function initPDO(string $sgbd, string $host, string $db, string $user, string $password): void
    {
        switch ($sgbd) {
            case 'mysql':
                // Connexion à MySQL
                self::$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
                break;

            case 'pgsql':
                self::$pdo = new PDO('pgsql:host=' . $host . ' dbname=' . $db . ' user=' . $user . ' password=' . $password);
                break;
            default:
                exit("Type de SGBD non correct : $sgbd fourni, 'mysql' ou 'pgsql' attendu");
        }

        // pour récupérer aussi les exceptions provenant de PDOStatement
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /* requêtes Préparées pour l'entitePlayerSquadro */
    private static PDOStatement $createPlayerSquadro;
    private static PDOStatement $selectPlayerByName;

    /******** Gestion des requêtes relatives à JoueurSquadro *************/
    public static function createPlayer(string $name): JoueurSquadro {
        $stmt = self::$pdo->prepare("INSERT INTO JoueurSquadro(joueurNom) VALUES (:name)");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        $id = self::$pdo->lastInsertId();
        return new JoueurSquadro($id, $name);
    }
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

    /* requêtes préparées pour l'entite PartieSquadro */
    private static PDOStatement $createPartieSquadro;
    private static PDOStatement $savePartieSquadro;
    private static PDOStatement $addPlayerToPartieSquadro;
    private static PDOStatement $selectPartieSquadroById;
    private static PDOStatement $selectAllPartieSquadro;
    private static PDOStatement $selectAllPartieSquadroByPlayerName;

    /******** Gestion des requêtes relatives à PartieSquadro *************/

    /**
     * initialisation et execution de $createPartieSquadro la requête préparée pour enregistrer une nouvelle partie
     */
    public static function createPartieSquadro(string $playerName, string $json): void {
        $player = self::selectPlayerByName($playerName);
        $stmt = self::$pdo->prepare("INSERT INTO PartieSquadro(playerOne, gameStatus, json) VALUES (:playerId, 'initialized', :json)");
        $stmt->bindParam(':playerId', $player->id);
        $stmt->bindParam(':json', $json);
        $stmt->execute();
    }

    /**
     * initialisation et execution de $savePartieSquadro la requête préparée pour changer
     * l'état de la partie et sa représentation json
     */
    public static function savePartieSquadro(string $gameStatus, string $json, int $partieId): void {
        $stmt = self::$pdo->prepare("UPDATE PartieSquadro SET gameStatus = :status, json = :json WHERE partieId = :id");
        $stmt->bindParam(':status', $gameStatus);
        $stmt->bindParam(':json', $json);
        $stmt->bindParam(':id', $partieId);
        $stmt->execute();
    }

    /**
     * initialisation et execution de $addPlayerToPartieSquadro la requête préparée pour intégrer le second joueur
     */
    public static function addPlayerToPartieSquadro(string $playerName, string $json, int $gameId): void {
        $player = self::selectPlayerByName($playerName);
        $stmt = self::$pdo->prepare("UPDATE PartieSquadro SET playerTwo = :playerId, gameStatus = 'waitingForPlayer', json = :json WHERE partieId = :id");
        $stmt->bindParam(':playerId', $player->id);
        $stmt->bindParam(':json', $json);
        $stmt->bindParam(':id', $gameId);
        $stmt->execute();
    }
    /**
     * initialisation et execution de $selectPartieSquadroById la requête préparée pour récupérer
     * une instance de PartieSquadro en fonction de son identifiant
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
     * initialisation et execution de $selectAllPartieSquadro la requête préparée pour récupérer toutes
     * les instances de PartieSquadro
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
     * initialisation et execution de $selectAllPartieSquadroByPlayerName la requête préparée pour récupérer les instances
     * de PartieSquadro accessibles au joueur $playerName
     * ne pas oublier les parties "à un seul joueur"
     */
    public static function getAllPartieSquadroByPlayerName(string $playerName): array {
        $player = self::selectPlayerByName($playerName);
        $stmt = self::$pdo->prepare("SELECT * FROM PartieSquadro WHERE playerOne = :playerId OR playerTwo = :playerId");
        $stmt->bindParam(':playerId', $player->id);
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
     * initialisation et execution de la requête préparée pour récupérer
     * l'identifiant de la dernière partie ouverte par $playername
     */
    public static function getLastGameIdForPlayer(string $playerName): int {
        $player = self::selectPlayerByName($playerName);
        $stmt = self::$pdo->prepare("SELECT partieId FROM PartieSquadro WHERE playerOne = :playerId ORDER BY partieId DESC LIMIT 1");
        $stmt->bindParam(':playerId', $player->id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['partieId'] : 0;
    }

}
