<?php
session_start();

if (!isset($_SESSION["LoggedIn"]) || $_SESSION["LoggedIn"] !== true) {
    header("location: login.php");
    exit;
}
?>