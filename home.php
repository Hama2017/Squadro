<?php
/**
 * Page d'accueil du jeu Squadro
 *
 * Cette page affiche la salle de jeu avec les parties disponibles
 */

// Inclure les classes nécessaires
require_once 'autoload.php';
require_once 'env/db.php';

// Démarrer la session
session_start();

// Vérifier si le joueur est connecté
if (!isset($_SESSION['player'])) {
    header('Location: login.php');
    exit;
}

// Initialiser la connexion à la base de données
PDOSquadro::initPDO($_ENV['sgbd'], $_ENV['host'], $_ENV['database'], $_ENV['user'], $_ENV['password']);

// Récupérer le joueur connecté
$player = $_SESSION['player'];

// Variables pour les listes de parties
$partiesEnCours = [];
$partiesEnAttente = [];
$partiesTerminees = [];

// Si le joueur est une instance valide de JoueurSquadro
if ($player instanceof JoueurSquadro && !empty($player->getJoueurNom())) {
    // Récupérer les parties du joueur
    $parties = PDOSquadro::getAllPartieSquadroByPlayerName($player->getJoueurNom());

    // Trier les parties selon leur statut
    foreach ($parties as $partie) {
        if ($partie->getGameStatus() === 'finished') {
            $partiesTerminees[] = $partie;
        } else {
            $partiesEnCours[] = $partie;
        }
    }

    // Récupérer les parties en attente de joueur (qu'il n'a pas créées)
    // Au lieu d'accéder directement à PDOSquadro::$pdo, nous devons ajouter une méthode dans PDOSquadro
    // qui récupère les parties en attente pour un joueur spécifique
    $partiesEnAttente = PDOSquadro::getWaitingGamesForPlayer($player->getId());
} else {
    // Rediriger vers la page de connexion en cas d'anomalie
    header('Location: login.php');
    exit;
}

// Traitement des actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'createGame':
            // Créer une nouvelle partie
            $plateau = new PlateauSquadro();
            $action = new ActionSquadro($plateau);
            $json = $action->toJson();

            $gameId = PDOSquadro::createPartieSquadro($player->getJoueurNom(), $json);

            // Rediriger vers la partie créée
            header("Location: index.php?partieId=$gameId");
            exit;

        case 'joinGame':
            if (isset($_POST['gameId']) && is_numeric($_POST['gameId'])) {
                $gameId = (int)$_POST['gameId'];
                $partie = PDOSquadro::getPartieSquadroById($gameId);

                if ($partie) {
                    // Vérifier que la partie n'a pas déjà deux joueurs
                    if (count($partie->getJoueurs()) < 2) {
                        // Ajouter le joueur à la partie
                        $json = $partie->getJson();

                        PDOSquadro::addPlayerToPartieSquadro($player->getJoueurNom(), $json, $gameId);

                        // Rediriger vers la partie
                        header("Location: index.php?partieId=$gameId");
                        exit;
                    } else {
                        $_SESSION['erreur'] = "Cette partie est déjà complète.";
                        header('Location: home.php');
                        exit;
                    }
                }
            }
            break;

        case 'logout':
            // Déconnexion
            session_destroy();
            header('Location: login.php');
            exit;
    }
}

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salle de jeu Squadro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        h1, h2, h3 {
            color: #333;
            margin-top: 1em;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .logout-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #d32f2f;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            margin-bottom: 10px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #388E3C;
        }

        .btn-secondary {
            background-color: #2196F3;
        }

        .btn-secondary:hover {
            background-color: #1976D2;
        }

        .game-section {
            margin-bottom: 40px;
        }

        .game-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .game-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .game-card h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 12px;
            font-size: 18px;
        }

        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            margin-right: 10px;
            font-weight: bold;
        }

        .status-waiting {
            background-color: #FFC107;
            color: #333;
        }

        .status-in-progress {
            background-color: #2196F3;
            color: white;
        }

        .status-finished {
            background-color: #9E9E9E;
            color: white;
        }

        .empty-message {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            text-align: center;
            color: #666;
            font-style: italic;
        }

        .action-create {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #e8f5e9;
            border-radius: 8px;
        }

        .action-create .btn {
            font-size: 16px;
            padding: 12px 24px;
        }

        @media (max-width: 768px) {
            .game-list {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header form {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Bienvenue dans la salle de jeu, <?= htmlspecialchars($player->getJoueurNom()) ?> !</h1>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="logout-btn">Se déconnecter</button>
    </form>
</div>

<div class="action-create">
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="hidden" name="action" value="createGame">
        <button type="submit" class="btn">Créer une nouvelle partie</button>
    </form>
</div>

<div class="game-sections">
    <!-- Parties en cours du joueur -->
    <div class="game-section">
        <h2>Vos parties en cours</h2>
        <?php if (empty($partiesEnCours)): ?>
            <div class="empty-message">Vous n'avez aucune partie en cours.</div>
        <?php else: ?>
            <div class="game-list">
                <?php foreach ($partiesEnCours as $partie): ?>
                    <div class="game-card">
                        <h3>Partie #<?= $partie->getPartieID() ?></h3>
                        <?php
                        $status = '';
                        $statusClass = '';

                        if ($partie->getGameStatus() === 'initialized') {
                            $status = 'En attente d\'un joueur';
                            $statusClass = 'status-waiting';
                        } elseif ($partie->getGameStatus() === 'waitingForPlayer') {
                            $status = 'En cours';
                            $statusClass = 'status-in-progress';
                        }
                        ?>
                        <p>
                            <span class="status <?= $statusClass ?>"><?= $status ?></span>
                            <?php if (count($partie->getJoueurs()) > 1): ?>
                                <span>Contre <?= htmlspecialchars($partie->getJoueurs()[1]->getJoueurNom()) ?></span>
                            <?php endif; ?>
                        </p>
                        <a href="index.php?partieId=<?= $partie->getPartieID() ?>" class="btn">Jouer</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Parties en attente de joueur -->
    <div class="game-section">
        <h2>Parties en attente d'un joueur</h2>
        <?php if (empty($partiesEnAttente)): ?>
            <div class="empty-message">Aucune partie en attente de joueur.</div>
        <?php else: ?>
            <div class="game-list">
                <?php foreach ($partiesEnAttente as $partie): ?>
                    <div class="game-card">
                        <h3>Partie #<?= $partie->getPartieID() ?></h3>
                        <p>Créée par <?= htmlspecialchars($partie->getJoueurs()[0]->getJoueurNom()) ?></p>
                        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
                            <input type="hidden" name="action" value="joinGame">
                            <input type="hidden" name="gameId" value="<?= $partie->getPartieID() ?>">
                            <button type="submit" class="btn btn-secondary">Rejoindre la partie</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Parties terminées -->
    <div class="game-section">
        <h2>Parties terminées</h2>
        <?php if (empty($partiesTerminees)): ?>
            <div class="empty-message">Vous n'avez aucune partie terminée.</div>
        <?php else: ?>
            <div class="game-list">
                <?php foreach ($partiesTerminees as $partie): ?>
                    <div class="game-card">
                        <h3>Partie #<?= $partie->getPartieID() ?></h3>
                        <p>
                            <span class="status status-finished">Terminée</span>
                            <?php if (count($partie->getJoueurs()) > 1): ?>
                                <span>Contre <?= htmlspecialchars($partie->getJoueurs()[1]->getJoueurNom()) ?></span>
                            <?php endif; ?>
                        </p>
                        <a href="index.php?partieId=<?= $partie->getPartieID() ?>" class="btn">Consulter</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer style="margin-top: 40px; text-align: center; color: #777; font-size: 0.9em;">
    <p>Jeu Squadro - L3 InfoWeb</p>
</footer>
</body>
</html>