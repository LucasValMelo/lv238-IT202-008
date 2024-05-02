CREATE TABLE IF NOT EXISTS `TopAnime`
(
    `id`        int AUTO_INCREMENT not null,
    `title`      VARCHAR(120),
    `rank`      VARCHAR(2),
    `score`     VARCHAR(5),
    `picture`   TEXT,
    `created`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `modified`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`title`)
)