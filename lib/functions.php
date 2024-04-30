<?php
//TODO 1: require db.php
require_once(__DIR__ . "/db.php");
$BASE_PATH = '/Project/';
//require safer_echo.php
require(__DIR__ . "/safer_echo.php");
//TODO 2: filter helpers
require(__DIR__ . "/sanitizers.php");
//TODO 3: User helpers
require(__DIR__ . "/user_helpers.php");
//TODO 4: Flash Message Helpers
require(__DIR__ . "/flash_messages.php");

//dupe email/user
require(__DIR__ . "/duplicate_user_details.php");

//reset session
require(__DIR__ . "/reset_session.php");

require(__DIR__ . "/get_url.php");

require(__DIR__ . "/render_functions.php");

//require(__DIR__ . "/");

require(__DIR__ . "/api_helper.php");

require(__DIR__ . "/load_api_keys.php");

require(__DIR__ . "/ani_mapper.php");

require(__DIR__ . "/session_store.php");

require(__DIR__ . "/redirect.php");
