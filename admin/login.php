<?php
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["LoggedIn"]) && $_SESSION["LoggedIn"] === true) {
    header("location: dashboard.php");
    exit;
}

include getenv("DOCUMENT_ROOT") . "/db/db.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) { // Check if username is empty
        $username_err = "Please enter username.";
    }
    else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    }
    else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $query = "SELECT ID, Username, Password, Role, ManagedDivisionID, ManagedTeamID "
                . "FROM users "
                . "WHERE Username = ? ";
        $types = "s";
        $params = array($username);
        
        $stmt = DB::getInstance()->makeQuery($query, $types, $params);
        $stmt->bind_result($id, $user, $dbpass, $role, $managedDivID, $managedTeamID);
        
        while ($stmt->fetch()) {
            if (password_verify($password, $dbpass)) {
                // Password is correct, so start a new session
                $status = session_status();
                if ($status == PHP_SESSION_NONE){
                    //There is no active session
                    session_start();
                }
                else if ($status == PHP_SESSION_DISABLED){
                    //Sessions are not available
                }
                else if($status == PHP_SESSION_ACTIVE){
                    //Destroy current and start new one
                    session_destroy();
                    session_start();
                }

                // Store data in session variables
                $_SESSION["LoggedIn"] = true;
                $_SESSION["ID"] = $id;
                $_SESSION["User"] = $username;
                $_SESSION["Role"] = $role;
                $_SESSION["DivisionID"] = $managedDivID;
                $_SESSION["TeamID"] = $managedTeamID;
                
                if ($role == "Newscaster") {
                    header("location: content/news?action=archive");
                }
                else {
                    // Redirect user to dashboard page
                    header("location: dashboard.php");
                }
            }
            else {
                // Display an error message if password is not valid
                $password_err = "The password you entered was not valid.";
            }
        }
        if (!$user) {
            // Display an error message if username doesn't exist
            $username_err = "No account found with that username.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<title>Login</title>
<link rel='stylesheet' href='/admin/css/style.css'>
</head>
<body>
<div id='wrapper'>
    <div id='login-container'>
        <h2>Login</h2>
        <form action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>' method='post'>
            <div class='form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>'>
                <label>Username</label>
                <input type='text' name='username' class='login-input' value='<?php echo $username; ?>' placeholder='Enter Username Here'>
                <span class='help-block'><?php echo $username_err; ?></span>
            </div>    
            <div class='form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>'>
                <label>Password</label>
                <input type='password' name='password' class='login-input' placeholder='Enter Password Here'>
                <span class='help-block'><?php echo $password_err; ?></span>
            </div>
            <div class='form-group'>
                <input type='submit' class='loginBtn' value='Log In'>
            </div>
        </form>
    </div>
</div>    
</body>
</html>