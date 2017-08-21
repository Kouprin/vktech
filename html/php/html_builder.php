<?php

require_once "globals.php";

function htmlBuildUserBar($user_type = 0) {
    $user_bar = "";
    $user_types_array = unserialize(USER_TYPE_STR);
    for ($i = 0; $i < USER_TYPES; $i++) {
        $active = "";
        if ($i == $user_type) {
            $active = "active";
        }
        $user_bar = $user_bar.
'          <li class="nav-item '.$active.'">
            <a class="nav-link" href="#" onclick="showUserBar('.$i.')">'.$user_types_array[$i].'</a>
          </li>
';
    }
    return $user_bar;
}

function htmlBuildBody($user_type, $user_id) {
    assert($user_type >= 0 && $user_type < USER_TYPES);
    $nav = "";
    $dashboard = "";
    $body =
'  <body>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
      <a class="navbar-brand" href="#">VK Tech</a>
      <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto" id="userBar">
'.htmlBuildUserBar($user_type).'
        </ul>
        <form class="form-inline mt-2 mt-md-0">
          <input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">
          <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
'.$nav."\n".$dashboard.'
      </div>
    </div>
    <script src="./js/bootstrap.min.js"></script>
  </body>';
    return $body;
}

function htmlBuildMeta() {
    $meta =
'  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Dashboard Template for Bootstrap</title>

    <!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">
    <script>
    function showUserBar(str) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("userBar").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "php/html_builder.php?q=show_user_bar&which=" + str, true);
        xmlhttp.send();
    }
    </script>
  </head>';
    return $meta;
}

function htmlBuildPage($user_type, $user_id) {
    $page = '<!DOCTYPE html>'."\n".'<html lang="ru">'."\n".htmlBuildMeta()."\n".htmlBuildBody($user_type, $user_id)."\n".'</html>';
    return $page;
}

$q = $_REQUEST["q"];

if ($q == "show_user_bar") {
    print(htmlBuildUserBar($_REQUEST["which"]));
    exit(0);
}
