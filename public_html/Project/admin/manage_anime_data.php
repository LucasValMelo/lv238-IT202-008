<?php

require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin"))
{
    flash("You cannot view this page, why are you trying", "warning");
    die(header("Location: " . get_url("home.php")));
}

function insert_animes_into_db($db, $animes, $mappings)
{
    $query = "INSERT INTO `TopAnime` ";
    if(count($animes)>0)
    {
        $cols = array_keys($animes[0]);
        $query .= "(" . implode(",", array_map(function ($col)
                                                {
                                                    return "`$col`";
                                                }, $cols
                                              )) . ") VALUES ";

        $values = [];
        foreach ($animes as $i => $anime)
        {
            $animePlaceholder = array_map(function ($v) use ($i)
                                                            {
                                                                return ":" . $v . $i;
                                                            }, $cols);
            $values[] = "(" . implode(",", $animePlaceholder) . ")";
        }

        $query .= implode(",", $values);

        $updates = array_reduce($cols, function ($carry, $col) {
            $carry[] = "`$col` = VALUES(`$col`)";
            return $carry;
        }, []);

        $query .= " ON DUPLICATE KEY UPDATE " . implode(",", $updates);

        $stmt = $db->prepare($query);

        foreach ($animes as $i => $anime)
        {
            foreach ($cols as $col)
            {
                $placeholder = ":$col$i";
                $val = isset($anime[$col]) ? $anime[$col] : "";
                $param = PDO::PARAM_STR;
                if(str_contains($mappings[$col], "int"))
                {
                    $param = PDO::PARAM_INT;
                }
                $stmt->bindValue($placeholder, $val, $param);
            }
        }
        try
        {
            $stmt->execute();
        }catch (PDOException $e)
        {
            error_log(var_export($e,true));
        }
    }
}
function process_single_anime($anime, $columns, $mappings)
{
    // Process anime data
    $title = isset($anime["title"]) ? se($anime["title"]) : $anime["title"];
    $rank = isset($anime["rank"]) ? se($anime["rank"]) : $anime["rank"];
    $score = isset($anime["score"]) ? se($anime["score"]) : $anime["score"];
    $picURL = isset($anime["picture_url"]) ? se($anime["picture_url"]) : $anime["picture_url"];


    // Prepare record
    $record = [];
    $record["title"] = $title;
    $record["rank"] = $rank;
    $record["score"] = $score;
    $record["picture_url"] = $picURL;

    // Map anime data to columns
    foreach ($columns as $column) {
        if(in_array($columns, ["id", "title", "rank", "score", "picture_url"])){
            continue;
        }
        if(array_key_exists($column, $anime)){
            $record[$column] = $anime[$column];
            if(empty($record[$column])){
                if(str_contains($mappings[$column], "int")){
                    $record[$column] = "0";
                }
            }
        }
    }
    error_log("Record: " . var_export($record, true));
    return $record;
}

function process_animes($result)
{
    $status = se($result, "status", 400, false);
    if ($status != 200) {
        return;
    }

    // Extract data from result
    $data_string = html_entity_decode(se($result, "response", "{}", false));
    $wrapper = "{\"data\":$data_string}";
    $data = json_decode($wrapper, true);
    if (!isset($data["data"])) {
        return;
    }
    $data = $data["data"];
    error_log("data: " . var_export($data, true));
    // Get columns from TopAnime table
    $db = getDB();
    $stmt = $db->prepare("SHOW COLUMNS FROM TopAnime");
    $stmt->execute();
    $columnsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare columns and mappings
    $columns = array_column($columnsData, 'Field');
    $mappings = [];
    foreach ($columnsData as $column) {
        $mappings[$column['Field']] = $column['Type'];
    }
    $ignored = ["id", "created", "modified"];
    $columns = array_diff($columns, $ignored);

    // Process each anime
    $animes = [];
    foreach ($data as $anime) {
        $record = process_single_anime($anime, $columns, $mappings);
        array_push($animes, $record);
    }

    // Insert animes into database
    insert_animes_into_db($db, $animes, $mappings);
}

$action = se($_POST, "action", "", false);
if($action)
{
    switch ($action)
    {
        case "animes":
            $data = [];
            $endpoint = "https://myanimelist.p.rapidapi.com/anime/top/airing";
            $isRapidAPI = true;
            $rapidAPIHost = "myanimelist.p.rapidapi.com";
            $result = get($endpoint, "MAL_KEY", $data, $isRapidAPI, $rapidAPIHost);
            process_animes($result);
            break;
    }
}
?>

<div class= "container-fluid">
    <h1>Anime Data Management</h1>
    <div class="row">
        <div class="col">
            <form method = "POST">
                <input type="hidden" name = "action" value = "animes" />
                <input type = "submit" class = "btn btn-primary" value = "refresh anis" />
            </form>
        </div>
        
    </div>
</div>