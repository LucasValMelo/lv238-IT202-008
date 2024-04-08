<?php
require(__DIR__."/../../partials/nav.php");
?>
<h1>Home</h1>
<?php
if(isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])){
 flash("Welcome, " . get_user_email());
}
else{
  flash("You're not logged in at all");
}
require(__DIR__ . "/../../partials/flash.php");
?>