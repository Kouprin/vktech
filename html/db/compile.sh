#!/bin/bash
g++ --std=c++14 -O2 -c db.cpp &&
g++ db.o /usr/lib/x86_64-linux-gnu/libboost_system.so -o server_db &&
echo "Done."
