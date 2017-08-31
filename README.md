## Project

Orders execution task.
There are three types of users: customers, executors and admins.
Customer adds an order then executor can choose the order to complete it and take the amount of money which customer is set.
The system takes 2% commission.
When executor choose an order it becomes a contract.
Each contract contains of exactly one executor and exactly one customer.
No executor can see others contracts.

## Available

https://83.136.250.121/
https://github.com/Kouprin/vktech

## What I used to make it

1. nginx
2. php 7.0
3. bootstrap
4. mysql
5. memcached
6. redis

## The good sides

User rights separation.
Caching personal data to redis.
Caching common data to memcached.
Paging with nice buttons.
Integer amount of money. Don't lose your cents.
Correct counting the commission.
User-friendly interface. Some messages like "cached from" or "DB execution status/err message".
Locking tables. No race condition.
Https.
Easy to test. Use header to change user type and user id.
Escaping. No way for '; DROP TABLE.
The data is stored in different databases. Easy to divide and grow.
Nice info about customers and executors.

## Possible improvements

Increase paging speed. The solution is in sql.php.
Nicer SQL-queries.
Table header.
Take table sizes from redis/memcache (now it's from mysql and it's understandable slow).
