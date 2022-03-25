#!/bin/bash

rm -rf var
rm -rf vendor
rm -rf node_modules
rm -rf public/build
rm -rf public/bundles

composer install
yarn install

php bin/console ckeditor:install
php bin/console elfinder:install
php bin/console assets:install
yarn encore dev

php bin/console d:d:d --force --no-interaction
php bin/console d:d:create

php bin/console doctrine:migrations:migrate --no-interaction
php bin/console d:f:l --no-interaction

php bin/console app:import:user-property --no-interaction
php bin/console app:update:item-registration-item-type --no-interaction
php bin/console app:move:news --no-interaction
php bin/console app:create-index-page-edits
php bin/console app:create-catalog-support-program

php bin/console app:import:createHintFields
