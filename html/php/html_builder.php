<?php

require_once "globals.php";
require_once "sql.php";
require_once "redis.php";

function getWhere() {
    $where = "";

    if (getNavDBTable() == "interactions.contracts") {
        if (getUserType() == CUSTOMER_USER_TYPE) {
            $where = "customer_id = ".getUserId();
        } else if (getUserType() == EXECUTOR_USER_TYPE) {
            $where = "executor_id = ".getUserId();
        }
    }
    if (getNavDBTable() == "interactions.orders" and getUserType() == CUSTOMER_USER_TYPE) {
        $where = "customer_id = ".getUserId();
    }
    return $where;
}

function getRows() {
    if (!$rows = redisQuery(CACHE_GET)) {
        // okay, go to mysql
        $rows = sqlGet(getNavDBTable(), getWhere(), getPage());
        redisQuery(CACHE_SET, $rows);
        unset($_SESSION["taken_from"]);
    }
    return $rows;
}

function getTableHeader() {
    $header = sqlGetHeader(getNavDBTable());
    return $header;
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
    $records = sqlGetCount(getNavDBTable(), getWhere()); // TODO use cache here
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
    $button = htmlPrint('<button class="btn btn-outline-success my-2 my-sm-0 mx-sm-1" type="submit" onclick="acceptOrder('.$id.')">ACCEPT</button>');
    htmlDecreaseIndent();
    return $button;
}

function htmlBuildNewOrder() {
    htmlIncreaseIndent();
    $new_order = htmlPrint('<form>');
    htmlIncreaseIndent();

    $new_order .= htmlPrint('<div class="form-group">');
    htmlIncreaseIndent();
    $new_order .= htmlPrint('<label for="orderDescription">Order description</label>');
    $new_order .= htmlPrint('<textarea class="form-control" id="orderDescription" rows="3">This is my fantastic order! I am user #'.getUserId().'.</textarea>');
    htmlDecreaseIndent();
    $new_order .= htmlPrint('</div>');

    $new_order .= htmlPrint('<div class="form-group row">');
    htmlIncreaseIndent();
    $new_order .= htmlPrint('<label for="moneyAmount" class="col-2 col-form-label">Money</label>');
    htmlIncreaseIndent();
    $new_order .= htmlPrint('<div class="col-10">');
    //$new_order .= htmlPrint('<input class="form-control" type="number" value="99.95" id="moneyAmount">');
    $new_order .= htmlPrint('<input class="form-control" value="99.95" id="moneyAmount">');
    htmlDecreaseIndent();
    $new_order .= htmlPrint('</div>');
    htmlDecreaseIndent();
    $new_order .= htmlPrint('</div>');
    htmlDecreaseIndent();

    $new_order .= htmlPrint('<div class="form-group row">');
    htmlIncreaseIndent();
    $new_order .= htmlPrint('<label for="currency" class="col-2 col-form-label">Currency</label>');
    htmlIncreaseIndent();
    $new_order .= htmlPrint('<div class="col-10">');
    $new_order .= htmlPrint('<select class="form-control" id="currency">');
    htmlIncreaseIndent();
    $new_order .= htmlPrint('<option>RUR</option>');
    htmlDecreaseIndent();
    $new_order .= htmlPrint('</select>');
    htmlDecreaseIndent();
    $new_order .= htmlPrint('</div>');
    htmlDecreaseIndent();
    $new_order .= htmlPrint('</div>');

    $new_order .= htmlPrint('<button type="submit" class="btn btn-primary" onclick="placeOrder()">Place a new order</button>');
    htmlDecreaseIndent();

    $new_order .= htmlPrint('</form>');
    htmlDecreaseIndent();
    return $new_order;
}

function htmlBuildTable() {
    if (!getNavDBTable()) {
        // okay, it's a form for new orders
        return htmlBuildNewOrder();
    }
    htmlIncreaseIndent();
    $table = htmlPrint('<h2>Data</h2>');
    $table .= htmlPrint('<div class="table-responsive">');
    htmlIncreaseIndent();
    $table .= htmlPrint('<table class="table table-striped">');
    htmlIncreaseIndent();
    $table .= htmlPrint('<thead>');
    htmlIncreaseIndent();
    $table .= htmlPrint('<tr>');
    htmlIncreaseIndent();
    $header = getTableHeader();
    if (getUserType() == EXECUTOR_USER_TYPE and getNav() == 0) {
        // button
        $header[] = "action";
    }
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
            // Add buttons to take orders
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
    if (isset($_SESSION["taken_from"])) {
        $table .= htmlPrint('<span class="text-muted">This data is taken from '.$_SESSION["taken_from"].'</span>');
        unset($_SESSION["taken_from"]);
    }
    $table .= htmlPrint('</div>');
    htmlDecreaseIndent();
    return $table;
}

function htmlErrorMessage() {
    $er = "";
    if ($_SESSION["error_msg"]) {
        $er .= htmlPrint('<h1>'.$_SESSION["error_msg"].'</h1>');
        unset($_SESSION["error_msg"]);
    }
    return $er;
}

function htmlBuildDashboard() {
    htmlIncreaseIndent();
    $dashboard = htmlPrint('<main class="col-sm-9 ml-sm-auto col-md-10 pt-3" role="main">');
    htmlIncreaseIndent();
    $dashboard .= htmlPrint('<h1>Dashboard</h1>');
    $dashboard .= htmlErrorMessage();

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
    $active_user = "";
    if (!$user_bar_only) {
        $user_types_array = unserialize(USER_TYPE_STR);
        $active_user = $user_types_array[getUserType()].', user_id = '.intval(getUserId());
    }
    $body .= htmlPrint('<a class="navbar-brand" href="#">VK Tech</a>');
    #$body .= htmlPrint('<a class="navbar-brand" href="#">VK Tech</a><div class="navbar-brand text-secondary" href="#">'.$active_user.'</div>');
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
    function acceptOrder(str) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("navDashboardId").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "php/html_builder.php?q=accept_order&id=" + str, true);
        xmlhttp.send();
    }
    function placeOrder(str) {
        var description = document.getElementById("orderDescription").value;
        var money = document.getElementById("moneyAmount").value;
        var currency = document.getElementById("currency").value;
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("navDashboardId").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "php/html_builder.php?q=place_order&description=" + escape(description) + "&money=" + escape(money) + "&currency=" + currency, true);
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
            setPage(0);
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
    if ($q == "accept_order") {
        if (!isset($_REQUEST["id"])) {
            die();
        }
        $id = $_REQUEST["id"];
        session_start();
        sqlAcceptOrder($id);
        redisQuery(CACHE_DEL);
        $GLOBALS["html_indent"] = 0;
        print(htmlBuildNavDashboard());
    }
    if ($q == "place_order") {
        if (!isset($_REQUEST["money"]) or !isset($_REQUEST["currency"])) {
            die();
        }
        $description = htmlspecialchars($_REQUEST["description"], ENT_QUOTES);
        $money = intval(floatval($_REQUEST["money"]) * 100);
        session_start();
        // TODO: add some number verification - customer should know how we transformed the number he wrote
        if ($money <= 0 or $money > MAX_ORDER_COST) {
            // you want something strange
            // no reason to allow you to do this
            $_SESSION["error_msg"] = ERR_INCORRECT_ORDER_COST;
            $GLOBALS["html_indent"] = 0;
            print(htmlBuildNavDashboard());
        } else {
            $money = $money * 100;
            $currency = $_REQUEST["currency"];
            // currency should be in the set of valid currencies
            if ($currency != "RUR") {
                // the only option for now
                die();
            }
            sqlNewOrder($description, $money, $currency);
            redisQuery(CACHE_DEL);
            setNav(1); // go to "My orders"
            $GLOBALS["html_indent"] = 0;
            print(htmlBuildNavDashboard());
        }
    }
}
