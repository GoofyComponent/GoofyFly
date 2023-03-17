#!/bin/bash

# recup les user
usernames=($(mysql -h localhost -D data -se "SELECT username FROM linux_credentials"))

#Creation et permission dossier
sudo mkdir -p "/backups"
sudo chmod -R 777 /backups

sudo chown -R $(whoami):$(whoami) /backups/*
sudo chmod -R 777 /backups/*

# For sur les user
for username in "${usernames[@]}"
do
    #Creation et permission dossier
    sudo mkdir -p "/backups/${username}/folder"
    sudo mkdir -p "/backups/${username}/bdd"
    sudo chown -R $(whoami):$(whoami) "/backups/${username}/folder"
    sudo chmod -R 777 "/backups/${username}/folder"
    sudo chown -R $(whoami):$(whoami) "/backups/${username}/bdd"
    sudo chmod -R 777 "/backups/${username}/bdd"

    #Cree les backups
    sudo tar -czvf "/backups/${username}/folder/${username}_$(date '+%Y-%m-%d_%H-%M-%S').tar.gz" "/home/${username}"
    sudo mysqldump --defaults-extra-file=./.my.cnf ${username} > "/backups/${username}/bdd/${username}_$(date '+%Y-%m-%d_%H-%M-%S').sql"
done

#Ajoute les perms en recursif
sudo chown -R $(whoami):$(whoami) /backups/*
sudo chmod -R 777 /backups/*