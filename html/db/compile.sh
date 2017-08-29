#!/bin/bash
g++ --std=c++14 -O2 -c main.cpp db.cpp &&
#g++ main.o /usr/lib/x86_64-linux-gnu/libboost_system.so -o server_db &&
g++ main.o db.o -lboost_system -o server_db &&
echo "Done."
