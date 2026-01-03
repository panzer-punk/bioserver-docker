#!/bin/bash
set -e

java -cp lib/mysql-connector.jar:bin bioserver.ServerMain
