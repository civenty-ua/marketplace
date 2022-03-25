#!/bin/bash

# shellcheck disable=SC2164
cd docker
docker-compose down
docker-compose up -d --remove-orphans
docker exec -it agro_market_php bash