<?php

require_once "globals.php";


// One connection per query.
// Increase MAX_CONNECTIONS (1203 error) if necessary.

function sqlConnect() {
    // Do something here is necessary.
    $sql = new mysqli('localhost', 'vktech', 'vktech');
    if ($sql->connect_errno) {
        // TODO print to log instead
        echo "Error: Failed to make a MySQL connection, here is why: \n";
        echo "Errno: " . $sql->connect_errno . "\n";
        echo "Error: " . $sql->connect_error . "\n";
        die('Could not connect to mysql');
    }
    return $sql;
}
function sqlDisconnect($sql, $result) {
    $result->free();
    $sql->close();
}


function sqlGetOrders($where, $page = 0) {
    $sql = sqlConnect();
    $query = "SELECT * FROM interactions.orders";

    if ($where) {
        // "customer_id = x"
        // "executor_id = x"
        $query = $query." WHERE ".$where;
    } else {
        // use WHERE clause for paging
        if ($page > 0) {
            $limit_from = $page * ITEMS_PER_PAGE - 1;
            $query = $query." WHERE id > ".$limit_from;
        }
    }

    $query = $query." LIMIT ".ITEMS_PER_PAGE;
    if (!$result = $sql->query($query)) {
        // TODO print to log instead
        echo "Error: Our query failed to execute and here is why: \n";
        echo "Query: " . $query . "\n";
        echo "Errno: " . $sql->errno . "\n";
        echo "Error: " . $sql->error . "\n";
        $sql->close();
    }
    $rows = [];
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $rows[] = $row;
    }

    sqlDisconnect($sql, $result);

    return $rows;
}
