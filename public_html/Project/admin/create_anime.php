<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php

//TODO handle stock fetch
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $symbol =  strtoupper(se($_POST, "symbol", "", false));
    $result = [];

    $quote = [];
    foreach ($_POST as $k => $v) {
        if (!in_array($k, ["title", "rank", "score", "picture"])) {
            unset($_POST[$k]);
        }
        $anime = $_POST;
        error_log("Cleaned up POST: " . var_export($anime, true));
    }
    /*if ($symbol) {
      //  if ($action === "fetch") {
            //$result = fetch_quote($symbol);
        //    error_log("Data from API" . var_export($result, true));
          //  if ($result) {
            //    $quote = $result;
            //}
        //} else 
        if ($action === "create") {
            foreach ($_POST as $k => $v) {
                if (!in_array($k, ["title", "rank", "score", "picture"])) {
                    unset($_POST[$k]);
                }
                $anime = $_POST;
                error_log("Cleaned up POST: " . var_export($anime, true));
            }
        }
    } else {
        flash("You must provide a symbol", "warning");
    }*/
    //insert data
    $db = getDB();
    $query = "INSERT INTO `TopAnime` ";
    $columns = [];
    $params = [];
    //per record
    foreach ($anime as $k => $v) {
        array_push($columns, "`$k`");
        $params[":$k"] = $v;
    }
    $query .= "(" . join(",", $columns) . ")";
    $query .= "VALUES (" . join(",", array_keys($params)) . ")";
    error_log("Query: " . $query);
    error_log("Params: " . var_export($params, true));
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Inserted record " . $db->lastInsertId(), "success");
    } catch (PDOException $e) {
        error_log("Something broke with the query" . var_export($e, true));
        flash("An error occurred", "danger");
    }
}

//TODO handle manual create stock
?>
<div class="container-fluid">
    <h3>Create Listing</h3>
    <div id="create" class="tab-target">
        <form method="POST">

            <?php render_input(["type" => "text", "name" => "title", "placeholder" => "(ex. Bleach)", "label" => "Anime Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "number", "name" => "score", "placeholder" => "Anime Score (out of 10)", "label" => "Anime Score", "rules" => ["required" => "required", "step" => ".01"]]); ?>
            <?php render_input(["type" => "number", "name" => "rank", "placeholder" => "Rank in MAL", "label" => "Anime Rank", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "picture", "placeholder" => "URL", "label" => "Picture URL"]); ?>

            <?php render_input(["type" => "hidden", "name" => "action", "value" => "create"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Create"]); ?>
        </form>
    </div>
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>