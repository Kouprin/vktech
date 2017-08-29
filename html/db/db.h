#pragma once

#include <boost/property_tree/ptree.hpp>
#include <boost/property_tree/json_parser.hpp>
#include <map>

#include "utils.h"

struct query_t {
    query_type type;
    int id;
    exec* (*executor)(const boost::property_tree::ptree& query);
};

exec parse_query(const char* buf, size_t len);

struct orders_table_data_t {
    int id;
    int customer_id;
    int executor_id;
    int timestamp_created;
    int timestamp_last_action;
    order_status status;
    int desc_start;
    long long price;
    long long original_price;
    char currency[3];
};

class orders_table_t {
public:
    orders_table_t() {
        orders_size = 0;
        orders_desc_size = 0;
        orders = (orders_table_data_t*)malloc(INIT_ORDERS_RECORDS * sizeof(orders_table_data_t));
        orders_desc = (char*)malloc(INIT_ORDERS_RECORDS * EXPECTED_DESC_SIZE);
    }
    // TODO: memory management
    void orders_table_add(int customer_id, int executor_id, char* desc, int desc_len, long long original_price, char* currency) {
        orders[orders_size].id = orders_size;
        orders[orders_size].customer_id = customer_id; 
        orders[orders_size].executor_id = executor_id; 
        orders[orders_size].timestamp_created = time(NULL); 
        orders[orders_size].timestamp_last_action = orders[orders_size].timestamp_created;
        orders[orders_size].status = NEW;
        orders[orders_size].desc_start = orders_desc_size;

        assert(strlen(desc) == desc_len);
        orders[orders_size].desc_start = orders_desc_size;
        strcpy(orders_desc + orders_desc_size, desc);
        orders_desc_size += desc_len;
        orders_desc[orders_desc_size] = 0;
        ++orders_desc_size;
        
        orders[orders_size].original_price = original_price;
        orders[orders_size].price = orders[orders_size].original_price * 100 * 100; // first 100 is about cents, second 100 is about commision
        assert(strlen(currency) == 3);
        strcpy(orders[orders_size].currency, desc);

        customers_to_orders[customer_id].push_back(&orders[orders_size]);
        executors_to_orders[executor_id].push_back(&orders[orders_size]);

        ++orders_size;
    }
    void orders_table_update(int id) {
    }
    void orders_table_select_customer(int customer_id, std::vector<orders_table_data_t>& data) {
        for (size_t i = 0; i < customers_to_orders[customer_id].size(); ++i) {
            data.push_back(*customers_to_orders[customer_id][i]);
        }
    }

    void orders_table_select_executor(int executor_id, std::vector<orders_table_data_t>& data) {
        for (size_t i = 0; i < executors_to_orders[executor_id].size(); ++i) {
            data.push_back(*executors_to_orders[executor_id][i]);
        }
    }

    void orders_table_select_from_to(int id, int from, int to, std::vector<orders_table_data_t>& data) {
        for (size_t i = from; i < std::min(to, orders_size); ++i) {
            data.push_back(orders[i]);
        }
    }
private:
    std::map<int, std::vector<orders_table_data_t*>> customers_to_orders;
    std::map<int, std::vector<orders_table_data_t*>> executors_to_orders;
    orders_table_data_t* orders;
    char* orders_desc;
    int orders_size;
    int orders_desc_size;

};
