<?php
require(__DIR__ . "/../../../partials/nav.php");
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("home.php");
}

//build search form
$form = [
    ["type" => "number", "name" => "low_score", "placeholder" => "Lowest Score", "label" => "Lowest --Score", "rules" => ["step" => ".01"], "include_margin" => false],
    ["type" => "number", "name" => "high_score", "placeholder" => "Highest Score", "label" => "Highest --Score", "rules" => ["step" => ".01"], "include_margin" => false],

    ["type" => "number", "name" => "low_rank", "placeholder" => "Lowest Rank", "label" => "Lowest --Rank", "include_margin" => false],
    ["type" => "number", "name" => "high_rank", "placeholder" => "Highest Rank", "label" => "Highest --Rank", "include_margin" => false],

    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["score" => "Score", "rank" => "Rank"], "include_margin" => false],
    ["type" => "select", "name" => "order", "label" => "Order", "options" => ["asc" => "+", "desc" => "-"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "100", "include_margin" => false]
];
//error_log("Form data: " . var_export($form, true));

$total_records = get_total_count("`TopAnime` a
JOIN `UserFavs` uf ON a.id = uf.user_id");


$query = "SELECT u.username, a.id, title, `rank`, score, picture FROM `TopAnime` a
JOIN `UserFavs` uf ON a.id = uf.anime_id JOIN Users u on u.id = uf.user_id";
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
if (count($_GET) > 0) {
    session_save($session_key, $_GET);
    $keys = array_keys($_GET);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $_GET[$v["name"]];
        }
    }
    //username
    $username = se($_GET, "username", "", false);
    if (!empty($username)) {
        $query .= " AND u.username like :username";
        $params[":username"] = "%$username%";
    }
    //name
    $title = se($_GET, "title", "", false);
    if (!empty($title)) {
        $query .= " AND title like :title";
        $params[":title"] = "%$title%";
    }
    //rarity range
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
    //IMPORTANT make sure you fully validate/trust $sort and $order (sql injection possibility)
    $query .= " ORDER BY $sort $order";
    //limit
    try {
        $limit = (int)se($_GET, "limit", "10", false);
    } catch (Exception $e) {
        $limit = 10;
    }
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }
    //IMPORTANT make sure you fully validate/trust $limit (sql injection possibility)
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
    <h3>Associated Anime</h3>
    <form method="GET">
        <div class="row mb-3" style="align-items: flex-end;">

            <?php foreach ($form as $k => $v) : ?>
                <div class="col">
                    <?php render_input($v); ?>
                </div>
            <?php endforeach; ?>

        </div>
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Filter"]); ?>
        <a href="?clear" class="btn btn-secondary">Clear</a>
    </form>
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
require_once(__DIR__ . "/../../../partials/flash.php");
?>