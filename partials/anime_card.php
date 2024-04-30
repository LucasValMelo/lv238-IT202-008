<?php
if (!isset($anime)) {
    error_log("Using Anime partial without data");
    flash("Dev Alert: Anime called without data", "danger");
}
?>
<?php if (isset($anime)) : ?>
    <!-- https://i.kym-cdn.com/entries/icons/original/000/029/959/Screen_Shot_2019-06-05_at_1.26.32_PM.jpg -->
    <div class="card mx-auto" style="width: 18rem;">
        <?php if (isset($anime["username"])) : ?>
            <div class="card-header">
                Owned By: <?php se($anime, "username", "N/A"); ?>
            </div>
        <?php endif; ?>
        <img src=<?php se($anime["picture"]); ?> class="card-img-top" alt="...">
        <div class="card-body">
            <h5 class="card-title"><?php se($anime, "title", "Unknown"); ?></h5>
            <div class="card-text">
                <ul class="list-group">
                    <li class="list-group-item">Rank: <?php $rank = se($anime, "rank");
                                                        $rank = "`$rank`"; ?></li>
                    <li class="list-group-item">Score: <?php se($anime, "score", "Unknown"); ?></li>
                </ul>

            </div>

            <div class="card-body">
                <?php if (isset($anime["id"])) : ?>
                    <a class="btn btn-secondary" href="<?php echo get_url("anime.php?id=" . $anime["id"]); ?>">View</a>
                <?php endif; ?>
                <?php if (!isset($anime["user_id"]) || $anime["user_id"] === "N/A") : ?>
                    <?php
                    $id = isset($anime["id"]) ? $anime["id"] : (isset($_GET["id"]) ? $_GET["id"] : -1);
                    ?>
                <?php else : ?>

                <?php endif; ?>
            </div>

        </div>
    </div>
<?php endif; ?>