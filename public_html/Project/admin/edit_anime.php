<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php
$id = se($_GET, "id", -1, false);
//TODO handle anime fetch
if (isset($_POST["title"])) {
    foreach ($_POST as $k => $v) {
        if (!in_array($k, ["title", "rank", "score", "picture"])) {
            unset($_POST[$k]);
        }
        $quote = $_POST;
        error_log("Cleaned up POST: " . var_export($quote, true));
    }
    //insert data
    $db = getDB();
    $query = "UPDATE `TopAnime` SET ";

    $params = [];
    //per record
    foreach ($quote as $k => $v) {

        if ($params) {
            $query .= ",";
        }
        //be sure $k is trusted as this is a source of sql injection
        $query .= "`$k`=:$k";
        $params[":$k"] = $v;
    }

    $query .= " WHERE id = :id";
    $params[":id"] = $id;
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Updated record ", "success");
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
}

$anime = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    $query = "SELECT `title`, `score`, `rank`, `picture` FROM `TopAnime` ";
    $query .= " WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $anime = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
} else {
    flash("Invalid id passed", "danger");
    die(header("Location:" . get_url("admin/list_anime.php")));
}
/*$form = [
    ["type" => "text", "name" => "title", "placeholder" => "(ex. Bleach)", "label" => "Anime Name", "rules" => ["required" => "required"]],
    ["type" => "number", "name" => "score", "placeholder" => "Anime Score (out of 10)", "label" => "Anime Score", "rules" => ["required" => "required", "step"=>".01"]],
    ["type" => "number", "name" => "rank", "placeholder" => "Rank in MAL", "label" => "Anime Rank", "rules" => ["required" => "required"]],
    ["type" => "text", "name" => "picture", "placeholder" => "URL", "label" => "Picture URL"]
];
/*
$query = "SELECT 'id', `title`, `score`, `rank`, `picture` FROM `TopAnime` ";
$db = getDB();
$query .= " WHERE id = :id";
    $params[":id"] = $id;
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Updated record ", "success");
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }*/

if ($anime) {
    se("this is running");
    $form = [
        ["type" => "text", "name" => "title", "placeholder" => "(ex. Bleach)", "label" => "Anime Name", "rules" => ["required" => "required"]],
        ["type" => "number", "name" => "score", "placeholder" => "Anime Score (out of 10)", "label" => "Anime Score", "rules" => ["required" => "required", "step" => ".01"]],
        ["type" => "number", "name" => "rank", "placeholder" => "Rank in MAL", "label" => "Anime Rank", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "picture", "placeholder" => "URL", "label" => "Picture URL"]
    ];
    $keys = array_keys($anime);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $anime[$v["name"]];
        }
    }


    //error_log("Form: " . $anime);
}
//TODO handle manual create anime
?>
<div class="container-fluid">
    <h3>Edit Anime</h3>
    <div>
        <a href="<?php echo get_url("admin/list_anime.php"); ?>" class="btn btn-secondary">Back</a>
    </div>
    <form method="POST">
        <?php foreach ($form as $k => $v) {

            render_input($v);
        } ?>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update"]); ?>
    </form>

</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>