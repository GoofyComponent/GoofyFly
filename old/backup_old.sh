#!/bin/bash

usernames=($(mysql -h localhost -D data -se "SELECT username FROM linux_credentials"))

sudo mkdir -p /backups/folders/users
sudo mkdir -p /backups/bdd/users

sudo chown -R $(whoami):$(whoami) /backups/folders/users
sudo chmod -R 700 /backups/folders/users

sudo chown -R $(whoami):$(whoami) /backups/bdd/users
sudo chmod -R 700 /backups/bdd/users

for username in "${usernames[@]}"
do
    sudo tar -czvf "/backups/folders/users/${username}_$(date '+%Y-%m-%d_%H-%M-%S').tar.gz" "/home/${username}"
    sudo mysqldump --defaults-extra-file=./.my.cnf ${username} > "/backups/bdd/users/${username}_$(date '+%Y-%m-%d_%H-%M-%S').sql"
done

sudo chown -R $(whoami):$(whoami) /backups/folders/users/*
sudo chmod -R 600 /backups/folders/users/*

sudo chown -R $(whoami):$(whoami) /backups/bdd/users/*
sudo chmod -R 600 /backups/bdd/users/*