<?php

require_once "globals.php";

function redisQuery($query, $data = NULL) {
    $user_type = getUserType();
    $user_id = getUserId();
    $table = getNavDBTable();
    $page = getPage();

    $key = $user_type.'-'.$user_id.'-'.$table.'-'.$page;

    $redis = new Redis(); 
    $redis->connect('127.0.0.1', 6379); 

    if (!$redis->ping()) {
        return NULL;
    }

    if ($query == REDIS_DEL) {
        $keys_to_del = $redis->keys($user_type.'-'.$user_id.'*');
        foreach($keys_to_del as $key) {
            $redis->del($key); 
        }
        return True;
    } else if ($query == REDIS_SET) {
        return $redis->set($key, serialize($data)); 
    } else if ($query == REDIS_GET) {
        return unserialize($redis->get($key));
    } else {
        // unknown opp
        return NULL;
    }
}
