#!/bin/bash

# Shell script to find all *.json files and start a migration process for each.
# This allows us to use horizontal scaling to get better performance.

for F in *.json
do
    dcm.pl -r oii_manual_migration config=${F} &
    echo "Started process for $F"
done
