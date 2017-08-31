<?php

require_once "globals.php";

function redisQuery($query, $data = NULL) {
    $user_type = getUserType();
    if ($user_type == ADMIN_USER_TYPE) {
        // don't cache anything for admin
        return NULL;
    }
    if (getNav() == 0) {
        // fast-changing table,
        // go to memcached
        return memcachedQuery($query, $data);
    }

    $user_id = getUserId();
    $table = getNavDBTable();
    $page = getPage();

    $key = $user_type.'-'.$user_id.'-'.$table.'-'.$page;

    $redis = new Redis(); 
    $redis->connect('127.0.0.1', 6379); 

    if (!$redis->ping()) {
        return NULL;
    }

    if ($query == CACHE_DEL) {
        $keys_to_del = $redis->keys($user_type.'-'.$user_id.'*');
        foreach($keys_to_del as $key) {
            $redis->del($key); 
        }
        memcachedQuery(CACHE_DEL);
        return True;
    } else if ($query == CACHE_SET) {
        return $redis->set($key, serialize($data)); 
    } else if ($query == CACHE_GET) {
        $_SESSION["taken_from"] = "redis";
        return unserialize($redis->get($key));
    } else {
        // unknown opp
        return NULL;
    }
}

function redisFlush($user_type, $executor_id) {
    $redis = new Redis(); 
    $redis->connect('127.0.0.1', 6379); 
    if (!$redis->ping()) {
        return NULL;
    }
    $keys_to_del = $redis->keys($user_type.'-'.$executor_id.'*');
    foreach($keys_to_del as $key) {
        $redis->del($key); 
    }
    return True;
}

function memcachedQuery($query, $data) {
    $page = getPage();

    $mc = new Memcache; 
    $mc->connect('127.0.0.1', 11211); 
    if (!$mc) {
        return NULL;
    }

    if ($query == CACHE_DEL) {
        $mc->flush();
        return True;
    } else if ($query == CACHE_SET) {
        return $mc->set($page, serialize($data)); 
    } else if ($query == CACHE_GET) {
        $_SESSION["taken_from"] = "memcache";
        return unserialize($mc->get($page));
    } else {
        // unknown opp
        return NULL;
    }

}
