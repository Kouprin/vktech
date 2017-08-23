<?php

// check flood queries

session_start();

// check flood sessions

require_once "./php/html_builder.php";

if (!isset($_SESSION['session_started']) || ($_SESSION['session_started'] == 0)) {
    if (!isset($_SESSION['user_type'])) {
        $_SESSION['user_type'] = 0;
    }
    $html = htmlBuildPage($user_bar_only = True);
} else {
    $html = htmlBuildPage($user_bar_only = False);
}

print($html);
