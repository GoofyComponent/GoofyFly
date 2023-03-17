# Comment utiliser le script de backup

1. Placer le dossier [backups_script.sh](./backups_script.sh) dans le dossier utilisateur de votre serveur.
2. Placer le fichier [.my.cnf.exemple](./.my.cnf.exemple) au meme endroit que le script et le renommer en .my.cnf
3. Modifier le fichier .my.cnf avec les informations de SQL de votre serveur.
4. Changer les droits du fichier backups_script.sh avec la commande suivante:

```sh
   chmod 777 backups_script.sh
```

5. Ensuite ouvrez le fichier cron avec la commande suivante:

```sh
   crontab -e
```

6. Ajouter la ligne suivante au bas du fichier (celui ci s'executera toutes les 12 heures):

```sh
   0 */12 * * * /chemin/script.sh
```
