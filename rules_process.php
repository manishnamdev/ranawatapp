<?php
session_start();

if (!isset($_POST['agree'])) {
    header("Location: rules.php");
    exit;
}

// user agreed
$_SESSION['rules_agreed'] = true;

header("Location: register.php");
exit;
