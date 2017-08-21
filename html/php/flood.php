<?php

require "globals.php";

session_start();
if (isset($_SESSION['ip']) && ($_SESSION['last_post'] + SESSION_REFRESH_TIME > time())) die('too early');

$_SESSION['last_post'] = time();
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
