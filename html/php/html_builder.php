<?php

require_once "globals.php";
require_once "sql.php";

function getRows() {
    $where_id = "";
    if (getUserType() == ADMIN_USER_TYPE) {
        //return sqlGet(getNavDBTable(), NULL, getPage());
    } else if (getUserType() == CUSTOMER_USER_TYPE) {
        $where_id = "customer_id = ".getUserId();
    } else if (getUserType() == EXECUTOR_USER_TYPE) {
        $where_id = "executor_id = ".getUserId();
    }
    if (getNavDBTable() == "interactions.orders") {
        $where_id = "";
    }
    $rows = sqlGet(getNavDBTable(), $where_id, getPage());
    return $rows;
}

function getTableHeader($nav) {
    return '#,Header,Header2,Header3,Header4';
}

function htmlBuildTableRow($row) {
    htmlIncreaseIndent();
    $table_row = htmlPrint('<tr>');
    htmlIncreaseIndent();
    for ($i = 0; $i < count($row); $i++) {
        $table_row .= htmlPrint('<td>'.$row[$i].'</td>');
    }
    htmlDecreaseIndent();
    $table_row .= htmlPrint('</tr>');
    htmlDecreaseIndent();
    return $table_row;
}

function htmlBuildTableButtons() {
    $records = sqlGetCount(getNavDBTable());
    $current_page = getPage();
    $max_page = intdiv($records, ITEMS_PER_PAGE) - 1;
    if ($records % ITEMS_PER_PAGE > 0) {
        $max_page++;
    }
    htmlIncreaseIndent();
    $buttons = htmlPrint('<button class="btn btn-outline-primary my-2 my-sm-0 mx-sm-1" type="submit" onclick="setPage(0)"><<</button>');
    for ($i = $current_page - 2; $i <= $current_page + 2; $i++) {
        if ($i >= 0 and $i <= $max_page) {
            $active = "";
            if ($i == $current_page) {
                $active = "active";
            }
            $buttons .= htmlPrint('<button class="btn btn-outline-primary '.$active.' my-2 my-sm-0 mx-sm-1" type="submit" onclick="setPage('.$i.')">'.($i + 1).'</button>');
        }
    }
    $buttons .= htmlPrint('<button class="btn btn-outline-primary my-2 my-sm-0 mx-sm-1" type="submit" onclick="setPage('.$max_page.')">>></button>');
    htmlDecreaseIndent();
    return $buttons;
}

function htmlBuildAcceptButton($id = 0) {
    htmlIncreaseIndent();
    $button = htmlPrint('<button class="btn btn-outline-success my-2 my-sm-0 mx-sm-1" type="submit" onclick="takeOrder('.$id.')">ACCEPT</button>');
    htmlDecreaseIndent();
    return $button;
}

function htmlBuildTable() {
    htmlIncreaseIndent();
    $table = htmlPrint('<h2>Data</h2>');
    if (!getNavDBTable()) {
        htmlDecreaseIndent();
        // nothing to print!
        return $table;
    }
    $table .= htmlPrint('<div class="table-responsive">');
    htmlIncreaseIndent();
    $table .= htmlPrint('<table class="table table-striped">');
    htmlIncreaseIndent();
    $table .= htmlPrint('<thead>');
    htmlIncreaseIndent();
    $table .= htmlPrint('<tr>');
    htmlIncreaseIndent();
    $header = explode(',', getTableHeader(getNav()));
    for ($i = 0; $i < count($header); $i++) {
        $table .= htmlPrint('<th>'.$header[$i].'</th>');
    }
    htmlDecreaseIndent();
    $table .= htmlPrint('</tr>');
    htmlDecreaseIndent();
    $table .= htmlPrint('</thead>');
    $table .= htmlPrint('<tbody>');

    if ($rows = getRows()) {
        if (getUserType() == EXECUTOR_USER_TYPE and getNav() == 0) {
            // Add buttons to accept orders
            for($i = 0; $i < count($rows); $i++) {
                $rows[$i][] = htmlBuildAcceptButton($rows[$i][0]);
            }
        }
        foreach($rows as $row) {
            $table .= htmlBuildTableRow($row);
        }
    }

    $table .= htmlPrint('</tbody>');
    htmlDecreaseIndent();
    $table .= htmlPrint('</table>');
    htmlDecreaseIndent();

    $table .= htmlBuildTableButtons();
    $table .= htmlPrint('</div>');
    htmlDecreaseIndent();
    return $table;
}

function htmlBuildDashboard() {
    $user_types_array = unserialize(USER_TYPE_STR);
    htmlIncreaseIndent();
    $dashboard = htmlPrint('<main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">');
    htmlIncreaseIndent();
    $dashboard .= htmlPrint('<h1>Dashboard</h1>');
    $dashboard .= htmlPrint('<div class="text-muted">'.$user_types_array[getUserType()].' with user_id = '.intval(getUserId()).' is chosen for now</div>');

    $dashboard .= htmlBuildTable();

    htmlDecreaseIndent();
    $dashboard .= htmlPrint('</main>');
    htmlDecreaseIndent();
    return $dashboard;
}

function htmlBuildNav() {
    htmlIncreaseIndent();
    $nav = htmlPrint('<nav class="col-sm-3 col-md-2 d-none d-sm-block bg-light sidebar">');
    htmlIncreaseIndent();
    $nav .= htmlPrint('<ul class="nav nav-pills flex-column">');
    $nav_types_array = unserialize(NAV_TYPE_STR);
    for ($i = 0; $i < NAV_TYPES; $i++) {
        if (checkRights($i) == True) {
            $active = "";
            if ($i == getNav()) {
                $active = "active";
            }
            htmlIncreaseIndent();
            $nav .= htmlPrint('<li class="nav-item">');
            htmlIncreaseIndent();
            $nav .= htmlPrint('<a class="nav-link '.$active.'" href="#" onclick="setNav('.$i.')">'.$nav_types_array[$i].'</a>');
            htmlDecreaseIndent();
            $nav .= htmlPrint('</li>');
            htmlDecreaseIndent();
        }
    }
    $nav .= htmlPrint('</ul>');
    htmlDecreaseIndent();
    $nav .= htmlPrint('</nav>');
    htmlDecreaseIndent();
    return $nav;
}

function htmlBuildNavDashboard() {
    htmlIncreaseIndent();
    $nav_dashboard = htmlPrint('<div class="container-fluid">');
    htmlIncreaseIndent();
    $nav_dashboard .= htmlPrint('<div class="row">');
    $nav_dashboard .= htmlBuildNav();
    $nav_dashboard .= htmlBuildDashboard();
    $nav_dashboard .= htmlPrint('</div>');
    htmlDecreaseIndent();
    $nav_dashboard .= htmlPrint('</div>');
    htmlDecreaseIndent();
    return $nav_dashboard;
}

function htmlBuildUserBar() {
    $user_bar = "";
    $user_types_array = unserialize(USER_TYPE_STR);
    for ($i = 0; $i < USER_TYPES; $i++) {
        $active = "";
        if ($i == getUserType()) {
            $active = "active";
        }
        htmlIncreaseIndent();
        $user_bar .= htmlPrint('<li class="nav-item '.$active.'">');
        htmlIncreaseIndent();
        $user_bar .= htmlPrint('<a class="nav-link" href="#" onclick="setUserType('.$i.')">'.$user_types_array[$i].'</a>');
        htmlDecreaseIndent();
        $user_bar .= htmlPrint('</li>');
        htmlDecreaseIndent();
    }
    return $user_bar;
}

function htmlBuildSessionForm() {
    $inputDisabled = '';
    if (getUserType() == ADMIN_USER_TYPE) {
        // there are no admin ids
        $inputDisabled = 'disabled';
    }
    htmlIncreaseIndent();
    $form = htmlPrint('<form class="form-inline mt-2 mt-md-0">');
    htmlIncreaseIndent();
    $form .= htmlPrint('<input class="form-control mr-sm-2" type="text" placeholder="user id" aria-label="UserId" id="userId" '.$inputDisabled.'>');
    $form .= htmlPrint('<button class="btn btn-outline-success my-2 my-sm-0" type="submit" onclick="setUserId()">Session start</button>');
    htmlDecreaseIndent();
    $form .= htmlPrint('</form>');
    htmlDecreaseIndent();
    return $form;
}

function htmlBuildBody($user_bar_only) {
    assert($user_type >= 0 && $user_type < USER_TYPES);
    htmlIncreaseIndent();
    $body = htmlPrint('<body>');
    htmlIncreaseIndent();
    $body .= htmlPrint('<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">');
    htmlIncreaseIndent();
    $body .= htmlPrint('<a class="navbar-brand" href="#">VK Tech</a>');
    $body .= htmlPrint('<button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">');
    htmlIncreaseIndent();
    $body .= htmlPrint('<span class="navbar-toggler-icon"></span>');
    htmlDecreaseIndent();
    $body .= htmlPrint('</button>');

    $body .= htmlPrint('<div class="collapse navbar-collapse" id="navbarsExampleDefault">');
    htmlIncreaseIndent();

    $body .= htmlPrint('<ul class="navbar-nav ml-auto" id="userBar">');
    $body .= htmlBuildUserBar();
    $body .= htmlPrint('</ul>');
    $body .= htmlBuildSessionForm();
    htmlDecreaseIndent();
    $body .= htmlPrint('</div>');
    htmlDecreaseIndent();
    $body .= htmlPrint('</nav>');

    $body .= htmlPrint('<span id="navDashboardId">');
    if ($user_bar_only == False) {
        $body .= htmlBuildNavDashboard();
    }
    $body .= htmlPrint('</span>');
    htmlDecreaseIndent();
    $body .= htmlPrint('</body>');
    htmlDecreaseIndent();
    return $body;
}

function htmlBuildMeta() {
    htmlIncreaseIndent();
    $meta = htmlPrint('<head>');
    htmlIncreaseIndent();
    $meta .= htmlPrintIgnoreIndent('
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>

    <title>Dashboard Template for Bootstrap</title>

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
    </script>');
    htmlDecreaseIndent();
    $meta .= htmlPrint('</head>');
    htmlDecreaseIndent();
    return $meta;
}

function htmlBuildPage($user_bar_only) {
    $page = "";
    $page .= htmlPrint('<!DOCTYPE html>');
    $page .= htmlPrint('<html lang="ru">');
    $page .= htmlBuildMeta();
    $page .= htmlBuildBody($user_bar_only);
    $page .= htmlPrint('</html>');
    return $page;
}

// It's possible to parse tags and make indent automatically (if necessary).
// In this life I just don't care.

function htmlIncreaseIndent() {
    $GLOBALS["html_indent"] += 4;
}

function htmlDecreaseIndent() {
    $GLOBALS["html_indent"] -= 4;
}

function htmlPrint($str) {
    $html = "";
    for($i = 0; $i < $GLOBALS["html_indent"]; $i++) {
        $html .= " ";
    }
    $html .= $str."\n";
    return $html;
}

function htmlPrintIgnoreIndent($str) {
    $html = "";
    $html .= $str."\n";
    return $html;
}

// main

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
        if ($type == CUSTOMER_USER_TYPE) {
            $_SESSION['nav_type'] = 1;
        }
        $_SESSION["user_id"] = 0;
        $_SESSION['session_started'] = 0;
        $_SESSION['page'] = 0;
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
        $GLOBALS["html_indent"] = 0;
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
            $GLOBALS["html_indent"] = 0;
            print(htmlBuildNavDashboard());
        }
    }
    if ($q == "set_page") {
        if (!isset($_REQUEST["page"])) {
            die();
        }
        $page = $_REQUEST["page"];
        session_start();
        if (setPage($page)) {
            $GLOBALS["html_indent"] = 0;
            print(htmlBuildNavDashboard());
        }
    }
}
