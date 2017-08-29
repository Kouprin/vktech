#pragma once

#include <iostream>
#include <string>

#define INIT_ORDERS_RECORDS 102400
#define EXPECTED_DESC_SIZE 64

enum exec {
    UNKNOWN_FAIL,
    SUCCESS,
    FAIL
};

enum query_type {
    UNKNOWN_QUERY,
    SELECT,
    ADD,
    REMOVE,
    UPDATE
};

enum order_status {
    UNKNOWN_STATUS,
    NEW,
    UPDATED,
    TAKEN,
    COMPLETED,
    REJECTED,
    DELETED
};
