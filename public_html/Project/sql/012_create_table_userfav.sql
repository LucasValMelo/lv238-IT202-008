CREATE TABLE IF NOT EXISTS  `UserFavs`
(
    `id`         int auto_increment not null,
    `user_id`    int,
    `anime_id`  int,
    `created`    timestamp default current_timestamp,
    `modified`   timestamp default current_timestamp on update current_timestamp,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES Users(`id`),
    FOREIGN KEY (`anime_id`) REFERENCES TopAnime(`id`),
    UNIQUE KEY (`user_id`, `anime_id`)
)