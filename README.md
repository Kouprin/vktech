# VK Tech

## Project

Orders execution task.
There are three types of users: customers, executors and admins.
Customer adds an order then executor can choose the order to complete it and take the amount of money which customer is set.
The system takes 2% commission.
When executor choose an order it becomes a contract.
Each contract contains of exactly one executor and exactly one customer.
No executor can see others contracts.

## Available

* https://83.136.250.121/
* https://github.com/Kouprin/vktech

## What I used to make it

1. nginx
2. php 7.0
3. bootstrap
4. mysql
5. memcached
6. redis

## Database

The simple script is located at [create.txt](./html/sql/create.txt). A little description is here:
```
table customers (id int auto_increment, global_id int, orders_created int, money_in_orders bigint, registered DATETIME, gain bigint, primary key (id), unique(global_id), index(registered));
table executors (id int auto_increment, global_id int, orders_completed int, money_received bigint, registered DATETIME, gain bigint, primary key (id), unique(global_id), index(registered));
table orders (id int auto_increment, customer_id int, status varchar(20), description varchar(8000), money_cost bigint, original_currency char(3), created DATETIME, last_action DATETIME, primary key (id), index(customer_id), index(last_action));
table contracts (id int auto_increment, customer_id int, executor_id int, status varchar(20), description varchar(8000), money_cost bigint, original_currency char(3), created DATETIME, last_action DATETIME, primary key (id), index(customer_id), index(executor_id), index(last_action));
```
The idea is to store orders separately from contracts &mdash; they are really two different essences.
The other idea is to store customers separetely from executors &mdash; each person could be customer and executor but not at the same time. So, we also store a little different data in those tables.

## Processing

A customer create an order. In this way we:
1. Check all the data is correct. No negative or overflow money, no strange things in description.
2. Update orders table.
3. Create a new customer if it's a newcomer. (INSERT IGNORE).
4. Update the customer. Calculate amount of money the system will receive. (INSERT ON DUPLICATE KEY UPDATE makes it atomic).
5. Flush the customer data from redis.
6. Flush memcached.

An executor accepts an order.
1. Lock orders- and contracts- tables to not make any race conditions.
2. Check the order still exists. That's important to avoid cases of double-taking.
3. Create a new contract.
4. Close an order.
5. Unlock the tables.
6. Create a new executor if it's a newcomer. (INSERT IGNORE).
7. Update the executor. Calculate some executor's things. (INSERT ON DUPLICATE KEY UPDATE makes it atomic).
8. Flush the executor data from redis.
9. Flush the customer data from redis. There are only two person which data have been changed.
10. Flush memcached.

## Cache difference

The orders table is available to see for all executors. This means two things:
1. Each order update changes the visible table for all executors.
2. All executors see the same table.
It's important. It's possible to cache the data independently to whom is looking for the page. I use memcached here.

The other case is the case of dependent change. But the data is changing for at most two persons: an executor and a customer.
I use redis here to store the data with a key *(USER_TYPE, USER_ID, TABLE, PAGE)*, and each refresh flushes only data with keys matched to *(USER_TYPE, USER_ID, anything, anything)*.

## The good sides

* User rights separation.
* Caching personal data to redis.
* Caching common data to memcached.
* Paging with nice buttons.
* Integer amount of money. Don't lose your cents.
* Correct counting the commission.
* User-friendly interface. Some messages like "cached from" or "DB execution status/err message".
* Locking tables. No race condition.
* Https.
* Easy to test. Use header to change user type and user id.
* Escaping. No way for '; DROP TABLE.
* The data is stored in different databases. Easy to divide and grow.
* Nice info about customers and executors.

## Possible improvements

* Increase paging speed.
* Make SQL-queries nicer.
* Take table sizes from redis/memcache.
* Flood control. No more than MAX_ORDERS_PER_DAY, no more than MAX_QUERIES_TO_MYSQL and etc.
* Use snapshots.
* Russian language support.

## Author

Alex Kouprin, Saint-Petersburg, 31.08.2017.
