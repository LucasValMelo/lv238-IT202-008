<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("home.php");
}
//attempt to apply
if (isset($_POST["users"]) && isset($_POST["animes"])) {
    $user_ids = $_POST["users"]; //se() doesn't like arrays so we'll just do this
    $anime_ids = $_POST["animes"]; //se() doesn't like arrays so we'll just do this
    if (empty($user_ids) || empty($anime_ids)) {
        flash("Both users and animes need to be selected", "warning");
    } else {
        //for sake of simplicity, this will be a tad inefficient
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO `UserFavs` (user_id, anime_id) VALUES (:uid, :aid)");
        foreach ($user_ids as $uid) {
            foreach ($anime_ids as $aid) {
                try {
                    $stmt->execute([":uid" => $uid, ":aid" => $aid]);
                    flash("Added Bookmark", "success");
                } catch (PDOException $e) {
                    flash(var_export($e->errorInfo, true), "danger");
                }
            }
        }
    }
}

//get anime List
$anime_list = [];
$animeSet = "";

if (isset($_POST["animeS"])) {
    $db = getDB();
    $animeSet = se($_POST, "animeS", "", false);
    $params[":title"] = "%$animeSet%";
    if (!empty($animeSet)) {
        $stmt = $db->prepare("SELECT id, title FROM `TopAnime` WHERE title LIKE :title ORDER BY id");
        try {
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $anime_list = $results;
                //var_export($anime_list);
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    }
}

//search for user by username
$users = [];
$username = "";
if (isset($_POST["username"])) {
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Users.id, username, 
        (SELECT GROUP_CONCAT(name, ' (' , IF(ur.is_active = 1,'active','inactive') , ')') from 
        UserRoles ur JOIN Roles on ur.role_id = Roles.id WHERE ur.user_id = Users.id) as roles
        from Users WHERE username like :username");
        try {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    } else {
        flash("Username must not be empty", "warning");
    }
}


?>
<div class="container-fluid">
    <h1>Assign Animes</h1>
    <form method="POST">
        <?php render_input(["type" => "search", "name" => "username", "placeholder" => "Username Search", "value" => $username]);/*lazy value to check if form submitted, not ideal*/ ?>
        <?php render_input(["type" => "search", "name" => "animeS", "placeholder" => "Anime Search", "value" => $animeSet]);/*lazy value to check if form submitted, not ideal*/ ?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST">
        <?php if (isset($username) && !empty($username)) : ?>
            <input type="hidden" name="username" value="<?php se($username, false); ?>" />
        <?php endif; ?>
        <table class="table">
            <thead>
                <th>Users</th>
                <th>Animes to Bookmark</th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table class="table">
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td>
                                        <?php render_input(["type" => "checkbox", "id" => "user_" . se($user, 'id', "", false), "name" => "users[]", "label" => se($user, "username", "", false), "value" => se($user, 'id', "", false)]); ?>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td>
                        <?php foreach ($anime_list as $anime) : ?>
                            <div>
                                <?php render_input(["type" => "checkbox", "id" => "anime_" . se($anime, 'id', "", false), "name" => "animes[]", "label" => se($anime, "title", "", false), "value" => se($anime, 'id', "", false)]); ?>

                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php render_button(["text" => "Toggle Animes", "type" => "submit", "color" => "secondary"]); ?>
    </form>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>