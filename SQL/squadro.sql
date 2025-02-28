DROP TABLE IF EXISTS PartieSquadro;
DROP TABLE IF EXISTS JoueurSquadro;

CREATE TABLE JoueurSquadro (
                               id INT AUTO_INCREMENT PRIMARY KEY,   -- Utilisation de AUTO_INCREMENT au lieu de serial
                               joueurNom VARCHAR(255) UNIQUE NOT NULL
);

INSERT INTO JoueurSquadro(joueurNom) VALUES ('ToTo'), ('Titi'), ('Lulu');

CREATE TABLE PartieSquadro (
                               partieId INT AUTO_INCREMENT PRIMARY KEY,  -- Utilisation de AUTO_INCREMENT pour l'id
                               playerOne INT NOT NULL,                    -- Références sans "REFERENCES JoueurSquadro(id)" car MySQL n'en a pas besoin ici
                               playerTwo INT NULL,
                               gameStatus VARCHAR(100) NOT NULL DEFAULT 'initialized' CHECK (gameStatus IN ('initialized', 'waitingForPlayer', 'finished')),
                               json TEXT NOT NULL,
                               CONSTRAINT players CHECK (playerOne <> playerTwo),
                               FOREIGN KEY (playerOne) REFERENCES JoueurSquadro(id),
                               FOREIGN KEY (playerTwo) REFERENCES JoueurSquadro(id)
);

