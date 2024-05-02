<?php
session_start();
require(__DIR__ . "/../../lib/functions.php");

$id = se($_GET, "id", -1, false);
$user_id = get_user_id();
if ($id < 1) {
    flash("Invalid id passed to Bookmark", "danger");
    redirect("admin/list_anime.php");
}

$db = getDB();

$fetchq = "SELECT `anime_id` FROM `UserFavs` WHERE `user_id` = $user_id AND  `anime_id` = :id ";
$stmt = $db->prepare($fetchq);
$results = [];
try {
    $stmt->execute([":id" => $id]);
    $r = $stmt->fetchAll();
    if ($r) {
        $results = $r;
        se($results);
    }
} catch (PDOException $e) {
    error_log("Error fetching anime " . var_export($e, true));
    flash("Unhandled error occurred", "danger");
}
se($results, true);
if ($results == "") {
    $query = "INSERT INTO `UserFavs` (`user_id`, `anime_id`) VALUES ($user_id, $id)";
    $redir = "admin/list_anime.php";
} else {
    $query = "DELETE FROM `UserFavs` uf  WHERE uf.user_id = $user_id AND uf.anime_id = :id";
    $redir = "mybookmarks.php";
}

//$query = "DELETE FROM `UserFav` uf JOIN `Users` u ON uf.user_id = $user_id WHERE uf.anime_id = :id";
try {
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id]);
    flash("Deleted record with id $id", "success");
} catch (Exception $e) {
    error_log("Error deleting stock $id" . var_export($e, true));
    flash("Error deleting record", "danger");
}
redirect($redir);
