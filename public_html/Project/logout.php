<?php
session_start();
require(__DIR__ . "/../../lib/functions.php");                              //lv238 4.23.24
reset_session();
require(__DIR__ . "/../../partials/flash.php");
flash("Successfully logged out", "success");
header("Location: login.php");