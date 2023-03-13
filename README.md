# GoofyFly

docker compose up -d

docker exec -it goofyfly-alpine-1 bash

composer install

symfony console make:migration
symfony console doctrine:migrations:migrate

npm install
npm run watch
