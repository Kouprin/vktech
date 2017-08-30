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
function sqlDisconnect($sql) {
    $sql->close();
}


function sqlGet($table, $where, $page = 0) {
    if (!$sql = sqlConnect()) {
        $_SESSION["error_msg"] = ERR_CANT_CONNECT_MYSQL;
        return NULL;
    }
    $query = "SELECT * FROM ".$table;

    $limit_from = $page * ITEMS_PER_PAGE;
    if ($where and $where != "") {
        // "customer_id = x"
        // "executor_id = x"
        $query = $query." WHERE ".$where;
        $query = $query." LIMIT ".$limit_from.','.ITEMS_PER_PAGE;
    } else {
        // use WHERE clause for paging
        if ($page > 0) {
            $query = $query." WHERE id >= ".$limit_from;
        }
        $query = $query." LIMIT ".ITEMS_PER_PAGE;
    }

    if (!$result = $sql->query($query)) {
        // TODO print to log instead
        echo "Error: Our query failed to execute and here is why: \n";
        echo "Query: " . $query . "\n";
        echo "Errno: " . $sql->errno . "\n";
        echo "Error: " . $sql->error . "\n";
        $sql->close();
        // TODO show err message
        return [];
    }
    $rows = [];
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $rows[] = $row;
    }

    sqlDisconnect($sql);

    return $rows;
}

function sqlGetCount($table, $where) {
    if (!$sql = sqlConnect()) {
        $_SESSION["error_msg"] = ERR_CANT_CONNECT_MYSQL;
        return NULL;
    }
    $query = "SELECT count(*) FROM ".$table;
    if ($where and $where != "") {
        $query .= " WHERE ".$where;
    }
    if (!$result = $sql->query($query)) {
        // TODO print to log instead
        echo "Error: Our query failed to execute and here is why: \n";
        echo "Query: " . $query . "\n";
        echo "Errno: " . $sql->errno . "\n";
        echo "Error: " . $sql->error . "\n";
        $sql->close();
        // TODO show err message
        return 0;
    }
    $rows = [];
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
        $rows[] = $row;
    }
    sqlDisconnect($sql);
    return $rows[0][0];
}

function sqlAcceptOrder($order) {
    if (!$sql = sqlConnect()) {
        $_SESSION["error_msg"] = ERR_CANT_CONNECT_MYSQL;
        return NULL;
    }
    $query = "LOCK TABLES interactions.orders WRITE, interactions.contracts WRITE";
    if (!$result = $sql->query($query)) {
        $_SESSION["error_msg"] = ERR_CANT_LOCK_TABLES;
        return NULL;
    } else {
        $query = "SELECT * from interactions.orders where id=".$order;
        if (($result = $sql->query($query)) and ($result->num_rows == 1)) {
            // Make sure that the order still exists.
            // If true, it cannot run away because tables have been locked.
            $_SESSION["error_msg"] = ERR_OKAY;
            $row = $result->fetch_array();
            $query = "INSERT into interactions.contracts (customer_id, executor_id, status, description, money_cost, original_currency, created, last_action) values (".
                $row["customer_id"].",".
                getUserId().",".
                "'ACCEPTED',".
                q($row["description"]).",".
                q($row["money_cost"]).",".
                q($row["original_currency"]).",".
                q($row["created"]).",".
                q(date("Y-m-d H:i:s")).")";
            $money_cost = $row["money_cost"];
            if (!$result = $sql->query($query)) {
                // mysql failed while processing
                // :(
                return NULL;
            }
            //$query = "DELETE FROM interactions.orders where id=".$order;
            //$sql->query($query);
            //
            $query = "UNLOCK TABLES";
            $sql->query($query);

            $query = "INSERT IGNORE users.executors (global_id, orders_completed, money_received, registered, gain) VALUES (".getUserId().", 0, 0, ".q(date("Y-m-d H:i:s")).", 0)";
            $sql->query($query);

            $query = "INSERT INTO users.executors SELECT id, global_id, orders_completed, money_received, registered, gain FROM users.executors WHERE global_id=".getUserId()."
                ON DUPLICATE KEY UPDATE orders_completed = values(orders_completed)+1, money_received = values(money_received)+".((100 - PERCENT_SYSTEM_TAKES) * $money_cost).", gain = values(gain)+".(PERCENT_SYSTEM_TAKES * $money_cost);
            $sql->query($query);

            return NULL;
        } else {
            $_SESSION["error_msg"] = ERR_CANT_FIND_ORDER;
        }
    }
    $query = "UNLOCK TABLES";
    if (!$result = $sql->query($query)) {
        $_SESSION["error_msg"] = ERR_CANT_UNLOCK_TABLES;
        return NULL;
    }
    // return?
}

function sqlNewOrder($description, $money_cost, $original_currency) {
    if (!$sql = sqlConnect()) {
        $_SESSION["error_msg"] = ERR_CANT_CONNECT_MYSQL;
        return NULL;
    }
    $query = "LOCK TABLES interactions.orders WRITE, interactions.contracts WRITE, users.customers WRITE, users.executors WRITE";
    if (!$result = $sql->query($query)) {
        $_SESSION["error_msg"] = ERR_CANT_LOCK_TABLES;
        return NULL;
    } else {
        $query = "SELECT * from interactions.orders where id=".$order;
    }
    $query = "UNLOCK TABLES";
    if (!$result = $sql->query($query)) {
        $_SESSION["error_msg"] = ERR_CANT_UNLOCK_TABLES;
        return NULL;
    }
    // return?
}
