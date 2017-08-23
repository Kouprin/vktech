<?php

require_once "globals.php";

function getTableHeader($nav) {
    return '#,Header,Header2,Header3,Header4';
}

function htmlBuildTableButtons() {
    return '<button class="btn btn-outline-primary my-2 my-sm-0 mx-sm-1" width="8%" type="submit" onclick="setUserId()">1</button>'.
        '<button class="btn btn-outline-primary active my-2 my-sm-0 mx-sm-1" type="submit" onclick="setUserId()">2</button>'.
        '<button class="btn btn-outline-primary my-2 my-sm-0 mx-sm-1" width="8%" type="submit" onclick="setUserId()">122</button>';
}

function htmlBuildTable() {
    $table =
'            <h2>Data</h2>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
';
    $header = explode(',', getTableHeader(getNav()));
    for ($i = 0; $i < count($header); $i++) {
        $table = $table.
'                    <th>'.$header[$i].'</th>
';
    }
    $table = $table.
'                  </tr>
                </thead>
                <tbody>
';
    $table = $table.
'                </tbody>
              </table>
';
    $table = $table.htmlBuildTableButtons().
'            </div>
';
    return $table;
}

function htmlBuildDashboard() {
    $user_types_array = unserialize(USER_TYPE_STR);
    $dashboard =
'            <main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">
               <h1>Dashboard</h1>
               <div class="text-muted">'.$user_types_array[getUserType()].' with user_id = '.intval(getUserId()).' is chosen for now</div>
';

    $dashboard = $dashboard.htmlBuildTable();

    $dashboard = $dashboard.
'             </main>
';
    return $dashboard;
}

function htmlBuildNav() {
    $nav =
'        <nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar">
          <ul class="nav nav-pills flex-column">
';
    $nav_types_array = unserialize(NAV_TYPE_STR);
    for ($i = 0; $i < NAV_TYPES; $i++) {
        if (checkRights($i) == True) {
            $active = "";
            if ($i == getNav()) {
                $active = "active";
            }
            $nav = $nav.
'          <li class="nav-item">
            <a class="nav-link '.$active.'" href="#" onclick="setNav('.$i.')">'.$nav_types_array[$i].'</a>
          </li>
';
        }
    }
    $nav = $nav.
'          </ul>
        </nav>
';
    return $nav;
}

function htmlBuildNavDashboard() {
    $navDashboard = 
'    <div class="container-fluid">
      <div class="row">
'.htmlBuildNav().htmlBuildDashboard().'
      </div>
    </div>';
    return $navDashboard;
}

function htmlBuildUserBar() {
    $user_bar = "";
    $user_types_array = unserialize(USER_TYPE_STR);
    for ($i = 0; $i < USER_TYPES; $i++) {
        $active = "";
        if ($i == getUserType()) {
            $active = "active";
        }
        $user_bar = $user_bar.
'          <li class="nav-item '.$active.'">
            <a class="nav-link" href="#" onclick="setUserType('.$i.')">'.$user_types_array[$i].'</a>
          </li>
';
    }
    return $user_bar;
}

function htmlBuildSessionForm() {
    $inputDisabled = '';
    if (getUserType() == ADMIN_USER_TYPE) {
        // there are no admin ids
        $inputDisabled = 'disabled';
    }
    $form =
'        <form class="form-inline mt-2 mt-md-0">
          <input class="form-control mr-sm-2" type="text" placeholder="user id" aria-label="UserId" id="userId" '.$inputDisabled.'>
          <button class="btn btn-outline-success my-2 my-sm-0" type="submit" onclick="setUserId()">Session start</button>
        </form>
';
    return $form;
}

function htmlBuildBody($user_bar_only) {
    assert($user_type >= 0 && $user_type < USER_TYPES);
    $body =
'  <body>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
      <a class="navbar-brand" href="#">VK Tech</a>
      <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav ml-auto" id="userBar">
'.htmlBuildUserBar().'
        </ul>
'.htmlBuildSessionForm().'
      </div>
    </nav>
    <span id="navDashboardId">
';
    if ($user_bar_only == False) {
        $body = $body.htmlBuildNavDashboard();
    }
    $body = $body.
'     </span>
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
    function setUserType(str) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                // do nothing - make it better
            }
        };
        xmlhttp.open("GET", "php/html_builder.php?q=set_user_type&type=" + str, true);
        xmlhttp.send();
        location.reload();
    }
    function setUserId() {
        var str = document.getElementById("userId").value;
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("navDashboardId").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "php/html_builder.php?q=set_user_id&id=" + str, true);
        xmlhttp.send();
    }
    function setNav(str) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("navDashboardId").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "php/html_builder.php?q=set_nav&nav=" + str, true);
        xmlhttp.send();
    }
    function setPage(str) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("navDashboardId").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "php/html_builder.php?q=set_page&page=" + str, true);
        xmlhttp.send();
    }
    </script>
  </head>';
    return $meta;
}

function htmlBuildPage($user_bar_only) {
    $page = '<!DOCTYPE html>'."\n".'<html lang="ru">'."\n".htmlBuildMeta()."\n".htmlBuildBody($user_bar_only)."\n".'</html>';
    return $page;
}


if (isset($_REQUEST["q"])) {
    $q = $_REQUEST["q"];

    // for demo only!
    //
    if ($q == "set_user_type") {
        assert($_REQUEST["type"]);
        $type = $_REQUEST["type"];
        assert(0 <= $type && $type < USER_TYPES);
        session_start();
        $_SESSION["user_type"] = $type;
        $_SESSION['nav_type'] = 0;
        $_SESSION["user_id"] = 0;
        $_SESSION['session_started'] = 0;
    }

    // for demo only!
    //
    if ($q == "set_user_id") {
        if (!isset($_REQUEST["id"])) {
            $id = 0;
        } else {
            $id = $_REQUEST["id"];
        }
        assert(0 <= $id && $id < USER_IDS);
        session_start();
        $_SESSION["user_id"] = $id;
        $_SESSION['session_started'] = 1;
        print(htmlBuildNavDashboard());
    }

    // okay, that's fine
    //
    if ($q == "set_nav") {
        if (!isset($_REQUEST["nav"])) {
            die();
        }
        $nav = $_REQUEST["nav"];
        session_start();
        if (setNav($nav)) {
            print(htmlBuildNavDashboard());
        }
    }
    if ($q == "set_page") {
        if (!isset($_REQUEST["page"])) {
            die();
        }
        $page = $_REQUEST["page"];
        session_start();
        if (setPage($nav)) {
            print(htmlBuildNavDashboard());
        }
    }
}
