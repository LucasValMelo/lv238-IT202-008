<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}

$form = [
    ["type" => "number", "name" => "low_score", "placeholder" => "Lowest Score", "label" => "Lowest --Score", "rules" => ["step" => ".01"], "include_margin" => false],
    ["type" => "number", "name" => "high_score", "placeholder" => "Highest Score", "label" => "Highest --Score", "rules" => ["step" => ".01"], "include_margin" => false],

    ["type" => "number", "name" => "low_rank", "placeholder" => "Lowest Rank", "label" => "Lowest --Rank", "include_margin" => false],
    ["type" => "number", "name" => "high_rank", "placeholder" => "Highest Rank", "label" => "Highest --Rank", "include_margin" => false],

    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["score" => "Score", "rank" => "Rank"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "100", "include_margin" => false]
];

if (count($_GET) > 0); {
    $keys = array_keys($_GET);
    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }
}


$query = "SELECT id, title, `rank`, score, picture FROM `TopAnime` WHERE 1=1";
$params = [];
$session_key = $_SERVER["SCRIPT_NAME"];
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
    session_delete($session_key);
    unset($_GET["clear"]);
    redirect($session_key);
} else {
    $session_data = session_load($session_key);
}

if (count($_GET) == 0 && isset($session_data) && count($session_data) > 0) {
    if ($session_data) {
        $_GET = $session_data;
    }
}

if (count($_GET) > 0); {
    error_log("Server Data : " . var_export($_SERVER, true));
    session_save($session_key, $_GET);
    $keys = array_keys($_GET);
    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }
    $low_score = se($_GET, "low_score", "-1", false);
    if (!empty($low_score) && $low_score > -1) {
        $query .= " AND score >= :low_score";
        $params[":low_score"] = $low_score;
    }
    $high_score = se($_GET, "high_score", "-1", false);
    if (!empty($high_score) && $high_score > -1) {
        $query .= " AND score <= :high_score";
        $params[":high_score"] = $high_score;
    }

    $low_rank = se($_GET, "low_rank", "-1", false);
    if (!empty($low_rank) && $low_rank > -1) {
        $query .= " AND score >= :low_rank";
        $params[":low_rank"] = $low_rank;
    }
    $high_rank = se($_GET, "high_rank", "-1", false);
    if (!empty($high_rank) && $high_rank > -1) {
        $query .= " AND score <= :high_rank";
        $params[":high_rank"] = $high_rank;
    }
    $sort = se($_GET, "sort", "score", false);
    if (!in_array($sort, ["rank", "score"])) {
        $sort = "score";
    } else {
        $sort = "`$sort`";
    }
    $order = se($_GET, "order", "desc", false);
    if (!in_array($order, ["asc", "desc"])) {
        $order = "asc";
    }
    //Verify Validate/ Trust
    $query .= " ORDER BY $sort $order";
    //limit
    try {
        $limit = (int)se($_GET, "limit", "100", false);
    } catch (Exception $e) {
        $limit = 10;
    }
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    $query .= " LIMIT $limit";
}


$db = getDB();
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

$table = ["data" => $results, "title" => "Latest Anime", "edit_url" => get_url("admin/edit_anime.php"), "delete_url" => get_url("admin/delete_entry.php"), "view_url" => get_url("anime.php")];
?>
<div class="container-fluid">
    <h3>List Anime</h3>
    <form method="GET">
        <div class="row" style="align-items: flex-end;">

            <?php foreach ($form as $k => $v) : ?>
                <div class="col-2">
                    <?php render_input($v); ?>
                </div>
            <?php endforeach;  ?>

        </div>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Filter"]); ?>
        <a href="?" class="btn btn-secondary">Clear</a>
    </form>
    <?php render_table($table); ?>
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>