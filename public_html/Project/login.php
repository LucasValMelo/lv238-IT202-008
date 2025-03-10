<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<form onsubmit="return validate(this)" method="POST">
    <?php render_input(["type" => "text", "id" => "email", "name" => "email", "label" => "Email/Username", "rules" => ["required" => true]]); ?>
    <?php render_input(["type" => "password", "id" => "password", "name" => "password", "label" => "Password", "rules" => ["required" => true, "minlength" => 8]]); ?>
    <?php render_button(["text" => "Login", "type" => "submit"]); ?>
</form>
<script>
    //                                                      lv238 4/23/24
    //logic for email val taken from https://www.simplilearn.com/tutorials/javascript-tutorial/email-validation-in-javascript#:~:text=We%20can%20validate%20email%2C%20password,compared%20with%20server%2Dside%20validation.
    function emailVal(email) {
        /*var validRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

        if (email.value.match(validRegex)) 
        {
             return true;
        } 
        else 
        {
            return false;
        }*/
    }
    //taken logic finished
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let errorState = true;
        let jemail = form.email.value;
        let jpassword = form.password.value;
        let jusername = form.username;
        let validRegex = new RegExp(/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/);
        let valReg2 = new RegExp(/^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/);
        let userReg = new RegExp(/^[a-z0-9_-]{3,16}$/);

        /*if (!/^[a-z0-9_-]{3,16}$/.test(jusername))
        {
            flash("Invalid test Username", "info");
            errorState = false;
        }*/
        if (jpassword.length = 0) {
            flash("Password Cannot Be Empty", "info");
            errorState = false;
        }
        if (jpassword.length < 8) {
            flash("Password is Too Short", "info");
            errorState = false;
        } //lv238 4/23/24
        if (jemail.includes("@")) {
            if (!valReg2.test(jemail)) {
                flash("Email Address is invalid", "info");
                errorState = false;
            }
        } else {
            if (!/^[a-z0-9_-]{3,16}$/.test(jemail)) {
                flash("Invalid test Username", "info");
                errorState = false;
            }
        }

        //TODO update clientside validation to check if it should
        //valid email or username
        return errorState;
    }
</script>
<?php
//TODO 2: add PHP Code
if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);

    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty");
        $hasError = true;
    }
    if (str_contains($email, "@")) {
        //sanitize
        //$email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $email = sanitize_email($email);
        //validate
        /*if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash("Invalid email address");
            $hasError = true;
        }*/
        if (!is_valid_email($email)) {
            flash("Invalid email address");
            $hasError = true;
        }
    } else {
        if (!is_valid_username($email)) {                                   //4/23/24 lv238
            flash("Invalid username");
            $hasError = true;
        }
    }
    if (empty($password)) {
        flash("password must not be empty");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short");
        $hasError = true;
    }
    if (!$hasError) {
        //flash("Welcome, $email");
        //TODO 4
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, username, password from Users 
        where email = :email or username = :email");
        try {                                                                //4/23/24 lv238
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);
                    if (password_verify($password, $hash)) {
                        //flash("Weclome $email");
                        $_SESSION["user"] = $user; //sets our session data from db
                        //lookup potential roles
                        $stmt = $db->prepare("SELECT Roles.name FROM Roles 
                        JOIN UserRoles on Roles.id = UserRoles.role_id 
                        where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1");
                        $stmt->execute([":user_id" => $user["id"]]);
                        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC); //fetch all since we'll want multiple
                        //save roles or empty array
                        if ($roles) {
                            $_SESSION["user"]["roles"] = $roles; //at least 1 role
                        } else {
                            $_SESSION["user"]["roles"] = []; //no roles
                        }
                        flash("Welcome, " . get_username());                            //lv238 4.23.24
                        redirect("home.php");
                    } else {
                        flash("Invalid password");
                    }
                } else {
                    flash("Email/Username not found");
                }
            }
        } catch (Exception $e) {
            flash("<pre>" . var_export($e, true) . "</pre>");
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
