<?php
require(__DIR__ . "/../../partials/nav.php");

?>

<?php
$anime = [];
$id = se($_GET, "id", -1, false);
//se("Id has not been passed \n", true);
se($anime, false);
if ($id > -1) {
    //fetch
    //se("id is passed \n", true);
    //e($anime, true);
    $db = getDB();
    $query = "SELECT `title`, `score`, `rank`, `picture` FROM `TopAnime` ";
    $query .= " WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $anime = $r;
            se("this is running", true);
            se($anime, true);
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
} else {
    flash("Invalid id passed", "danger");
    //die(header("Location:" . get_url("admin/list_anime.php")));
    redirect("admin/list_anime.php");
}

foreach ($anime as $k => $v) {
    if (is_null($v)) {
        $anime[$k] = "N/A";
    }
}
//TODO add a check if anything actually changed (not in this video 4-27-24)


//TODO handle manual create stock
?>
<div class="container-fluid">
    <h3>Anime View: <?php se($anime, "title", "Unknown"); ?></h3>
    <div>
        <a href="<?php echo get_url("admin/list_anime.php"); ?>" class="btn btn-secondary">Back</a>
    </div>

    <?php render_anime_card($anime); ?>

</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../partials/flash.php");
?>