<?php
require(__DIR__ . "/../../partials/nav.php");

?>

<?php

$anime = [];
$params = [];
$id = get_user_id();
$total_records = get_total_count("`TopAnime` a
JOIN `UserFavs` uf ON uf.user_id = $id");
se($anime, false);
if (isset($id)) {
    //fetch
    $db = getDB();
    $query = "SELECT u.username, a.id, title, `rank`, score, picture FROM `TopAnime` a
    JOIN `UserFavs` uf ON a.id = uf.anime_id JOIN Users u on u.id = uf.user_id WHERE u.id = $id";
} else {
    flash("Invalid id passed", "danger");
    //die(header("Location:" . get_url("admin/list_anime.php")));
    redirect("list_anime.php");
}
$stmt = $db->prepare($query);
$results = [];
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll();
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    error_log("Error fetching anime " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}
foreach ($results as $index => $anime) {
    foreach ($anime as $key => $value) {
        if (is_null($value)) {
            $results[$index][$key] = "N/A";
        }
    }
}

$table = [
    "data" => $results, "title" => "Anime", "ignored_columns" => ["id"],
    "view_url" => get_url("anime.php"),
];
?>
<div class="container-fluid">
    <h3>My Bookmarks</h3>
    <?php render_result_counts(count($results), $total_records); ?>
    <div class="row w-100 row-cols-auto row-cols-sm-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 g-4">
        <?php foreach ($results as $anime) : ?>
            <div class="col">
                <?php render_anime_card($anime); ?>
            </div>
        <?php endforeach; ?>
        <?php if (count($results) === 0) : ?>
            <div class="col">
                No results to show
            </div>
        <?php endif; ?>
    </div>
</div>


<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>