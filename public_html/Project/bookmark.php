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
$query = "INSERT INTO `UserFavs` (`user_id`, `anime_id`) VALUES ($user_id, :id)";
$redir = "admin/list_anime.php";
//$query = "DELETE FROM `UserFav` uf JOIN `Users` u ON uf.user_id = $user_id WHERE uf.anime_id = :id";
try {
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id]);
    flash("Bookmarked Anime with ID $id", "success");
} catch (Exception $e) {
    error_log("Error Bookmarking Anime with $id" . var_export($e, true));
    flash("Error bookmarking", "danger");
}
redirect($redir);
