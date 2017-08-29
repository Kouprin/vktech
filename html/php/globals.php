<?php
define("SESSION_REFRESH_TIME", 100);

define("USER_TYPES", 3);
define("ADMIN_USER_TYPE", 0);
define("CUSTOMER_USER_TYPE", 1);
define("EXECUTOR_USER_TYPE", 2);
define("USER_TYPE_STR", serialize(array('Admin', 'Customer', 'Executor')));

define("NAV_TYPES", 7);
define("NAV_TYPE_STR", serialize(array('All orders', 'My active orders', 'My completed orders', 'New order', 'Update order', 'All customers', 'All executors')));

define("NAV_RIGHTS", serialize(array(7, 6, 6, 2, 2, 1, 1))); // a bitmask: each bit means an appropriate user type

define("NAV_DB_TABLES", serialize(array("interactions.orders", "interactions.orders", "interactions.orders", NULL, NULL, "users.customers", "users.executors")));

define("ITEMS_PER_PAGE", 30);

function setNav($nav) {
    if (!(0 <= $nav && $nav < NAV_TYPES)) {
        return False;
    }
    if (!checkRights($nav)) {
        return False;
    }
    $_SESSION["nav_type"] = $nav;
    return True;
}

function setPage($page) {
    if (!(0 <= $page && $page < PHP_INT_MAX)) {
        return False;
    }
    // that's okay if you want to send a request to show some-huge-number-page directly for some reasons
    // you'll see no records in the table
    $_SESSION["page"] = $page;
    return True;
}

function getNav() {
    if (!isset($_SESSION["nav_type"])) {
        $_SESSION["nav_type"] = 0;
    }
    return intval($_SESSION["nav_type"]);
}

function getNavDBTable() {
    $nav_db_tables = unserialize(NAV_DB_TABLES);
    return $nav_db_tables[getNav()];
}

function getPage() {
    if (!isset($_SESSION["page"])) {
        $_SESSION["page"] = 0;
    }
    return intval($_SESSION["page"]);
}

function getUserType() {
    if (!isset($_SESSION["user_type"])) {
        $_SESSION["user_type"] = 0;
    }
    return intval($_SESSION["user_type"]);
}

function getUserId() {
    if (!isset($_SESSION["user_id"])) {
        $_SESSION["user_id"] = 0;
    }
    return intval($_SESSION["user_id"]);
}

function checkRights($nav) {
    $nav_rights_array = unserialize(NAV_RIGHTS);
    $nav_access = $nav_rights_array[$nav];
    $user_type_bit = 1 << getUserType();
    return ($user_type_bit & $nav_access);
}
