<?php
function normalize_volume($volume)
{
    return log1p($volume) / 10;
}
function diminishing_returns($shares)
{
    return sqrt($shares);
}


function calculate_life($stocks, $base_life, $rarity)
{
    $total_life = $base_life;
    foreach ($stocks as $stock) {
        $adjusted_shares = diminishing_returns($stock["shares"]);
        $total_life += abs($stock["change"]) * $adjusted_shares;
    }
    return max(ceil($total_life * (1 + $rarity / .5)), 1);
}






function fetch_anime_data($ids = [], $user_id = null)
{
    if (!is_array($ids)) {
        $ids = [$ids];
    }
    if (is_null($user_id)) {
        $user_id = get_user_id();
    }
    $animes = [];
    $db = getDB();
    $query = "SELECT a.id, title, score, `rank`, picture, a.created, a.modified, FROM `TopAnime`";
    $placeholders = str_repeat("?", count($ids)); // ??
    $arr = str_split($placeholders); // [?,?]
    $placeholders = join(",", $arr); //?,?
    $query .= "($placeholders)";
    try {
        error_log("fetch_anime_data query: $query");

        array_unshift($ids, $user_id); //prepend user_id
        error_log("Params: " . var_export($ids, true));
        $stmt = $db->prepare($query);
        $stmt->execute($ids);
        $r = $stmt->fetchAll();
        if ($r) {
            $animes = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching records: " . var_export($e, true));
        flash("Error fetching animes", "danger");
    }
    error_log("anime results: " . var_export($animes, true));
    return $animes;
}
