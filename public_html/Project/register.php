<?php
require(__DIR__ . "/../../partials/nav.php");
reset_session();
?>
<form onsubmit="return validate(this)" method="POST">
    <?php render_input(["type" => "email", "id" => "email", "name" => "email", "label" => "Email", "rules" => ["required" => true]]); ?>
    <?php render_input(["type" => "text", "id" => "username", "name" => "username", "label" => "Username", "rules" => ["required" => true, "maxlength" => 30]]); ?>
    <?php render_input(["type" => "password", "id" => "password", "name" => "password", "label" => "Password", "rules" => ["required" => true, "minlength" => 8]]); ?>
    <?php render_input(["type" => "password", "id" => "confirm", "name" => "confirm", "label" => "Confirm Password", "rules" => ["required" => true, "minlength" => 8]]); ?>
    <?php render_button(["text" => "Register", "type" => "submit"]); ?>
</form>
<script>
    //lv238 4/22/24

    //logic for email val taken from https://www.simplilearn.com/tutorials/javascript-tutorial/email-validation-in-javascript#:~:text=We%20can%20validate%20email%2C%20password,compared%20with%20server%2Dside%20validation.
    function emailVal(email) {
        var validRegex = new RegExp(/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/);

        if (validRegex.test(email.value)) {
            return true;
        } else {
            return false;
        }
    }
    //taken logic finished  
    function validate(form) {
        let errorState = true;
        let jemail = form.email.value;
        let jpassword = form.password.value;
        let jusername = form.username.value;
        let jconfirm = form.confirm.value;
        let validRegex = new RegExp(/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/);
        let valReg2 = new RegExp(/^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/);
        let userReg = new RegExp(/^[a-zA-Z0-9_-]{3,16}$/);

        if (!userReg.test(jusername)) {
            flash("Invalid test Username", "info");
            errorState = false; //lv238 4/22/24 
        }
        if (jpassword.length = 0) {
            flash("Password Cannot Be Empty", "info");
            errorState = false;
        }
        if (jpassword.length < 8) {
            flash("Password is Too Short", "info");
            errorState = false;
        }
        if (!valReg2.test(jemail)) {
            flash("Email Address is invalid", "info");
            errorState = false;
        }
        if (jconfirm.length === 0) {
            flash("Confirm Password Field Should Not Be Empty", "info");
            errorState = false;
        } //LV238 4/22/24
        if (jpassword != jconfirm) {
            flash("Passwords Must Match", "info");
            errorState = false;
        }



        //TODO update clientside validation to check if it should
        //valid email or username
        return errorState;
    }
</script>
<?php
//TODO 2: add PHP Code
if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"]) && isset($_POST["username"])) {
    $email = se($_POST, "email", "", false);
    $username = se($_POST, "username", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);

    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty", "danger");
        $hasError = true;
    }
    //hoping change appears in git                                    lv238 4/22/24
    //sanitize
    $email = sanitize_email($email);
    //validate
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 characters a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("password must not be empty", "danger");
        $hasError = true;
    }
    if (empty($confirm)) {
        flash("Confirm password must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short", "danger");
        $hasError = true;                                           //lv238 4/22/24
    }
    if (strlen($password) > 0 && $password !== $confirm) {
        flash("Passwords must match", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        //TODO 4
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES(:email, :password, :username)");
        try {
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
            flash("\nSuccessfully registered!", "success");
        } catch (PDOException $e) {
            users_check_duplicate($e->errorInfo);
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>