<?php

require_once 'skel/PDOSquadro.php';
require_once 'skel/JoueurSquadro.php';
require_once 'env/db.php'; // Ajoutez cette ligne pour inclure les paramètres de connexion à la base de données

session_start();

if (!isset($_SESSION['player'])) {
    header('Location: login.php');
    exit;
}

// Initialisez la connexion à la base de données
PDOSquadro::initPDO($_ENV['sgbd'], $_ENV['host'], $_ENV['database'], $_ENV['user'], $_ENV['password']);

$player = $_SESSION['player'];
if ($player instanceof JoueurSquadro && !empty($player->getJoueurNom())) {
    $parties = PDOSquadro::getAllPartieSquadroByPlayerName($player->getJoueurNom());
} else {
    $parties = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Accueil Squadro</title>
</head>
<body>
<h1>Bienvenue, <?= $player->getJoueurNom(); ?> !</h1>

<h2>Parties en cours</h2>
<ul>
    <?php foreach ($parties as $partie): ?>
        <?php if ($partie->gameStatus === 'waitingForPlayer'): ?>
            <li><a href="index.php?partieId=<?= $partie->partieId ?>">Partie #<?= $partie->partieId ?></a> - En attente d'un autre joueur</li>
        <?php elseif ($partie->gameStatus === 'initialized'): ?>
            <li><a href="index.php?partieId=<?= $partie->partieId ?>">Partie #<?= $partie->partieId ?></a> - Votre tour de jouer</li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>

<h2>Parties terminées</h2>
<ul>
    <?php foreach ($parties as $partie): ?>
        <?php if ($partie->gameStatus === 'finished'): ?>
            <li><a href="index.php?partieId=<?= $partie->partieId ?>">Partie #<?= $partie->partieId ?></a> - Terminée</li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>

<h2>Créer une nouvelle partie</h2>
<form action="index.php" method="post">
    <input type="hidden" name="action" value="createGame">
    <button type="submit">Nouvelle partie</button>
</form>
</body>
</html>
