<?php
    $status = session_status();
    if ($status == PHP_SESSION_ACTIVE) {
        $_SESSION = array(); // Unset all of the session variables
        session_destroy(); // Destroy the session.
    }
    else {
        session_start();
        $_SESSION = array(); // Unset all of the session variables
        session_destroy(); // Destroy the session.
    }
    
    header("location: login.php");
    exit;
?>