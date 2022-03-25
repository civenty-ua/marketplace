### Agricultural marketplace and teaching platform

###### First launch
1. Create ```.env``` file from example
```shell
cp .env.example .env
```
2. Create docker ```.env``` file in docker folder  
```shell
cd docker
cp .env.dist .env
```
3. Start docker
```shell
docker-compose up -d
```
4. Go to php container
```shell
docker exec -it agro_market_php bash
```

5. Install composer dependencies (use command in php container)
```shell
composer install
```

6. Create DB tables by using migrate command (use command in php container)
```shell
bin/console d:m:m -n
``` 
or create from scratch
#### This operation should not be executed in a production environment!
```shell
bin/console doctrine:database:drop --force && \
bin/console doctrine:database:create && \
bin/console doctrine:schema:create
``` 

7. Load fixtures to fill DB (use command in php container)
```shell
bin/console doctrine:fixtures:load
```
or add only static pages to DB if needed (use command in php container)
```shell
php bin/console doctrine:fixtures:load --group=pages --append
```

Admin will be added:
```
admin@test.com
123456
```
and user:
```
user@test.com
654321
```

8. Install CKeditor (use commands in php container)
```shell
bin/console asset:install && \
bin/console ckeditor:install && \
bin/console elfinder:install
``` 

9. Install frontend modules (use command in php container)
```shell
yarn install
```

10. Build frontend (use command in php container)
```shell
yarn encore dev
```


11. Add domain to hosts file
```
127.0.0.1 agro.loc
```

12. Load some data

Add categories and attributes (use commands in php container)

```shell
bin/console app:import:market:categories-products src/DataFixtures/sources/import/market/productsCategories/data.xlsx && \
bin/console app:import:market:categories-services src/DataFixtures/sources/import/market/servicesCategories/data.xlsx
```

Add user properties to users (use command in php container)
```shell
php bin/console app:import:user-property --no-interaction
```

Seed fake data if needed (recommended only if APP_ENV=DEV) (use command in php container)
```shell
php bin/console app:seedFakeUsers
php bin/console app:seedFakeProducts
php bin/console app:seedFakeServices
```
or
```shell
sh seed.sh
```

Congrats! Now you can see website at http://agro.loc and admin dashboard at http://agro.loc/admin


###### Update project (dev)
1. Go to php container
```shell
docker exec -it agro_market_php bash
```

2. Remove and install composer dependencies
```shell
rm -rf ./vendor
composer install
```

3. Migrate DB by using command
```shell
bin/console d:m:m
```

4. Rebuild frontend
```shell
rm -rf ./node_modules
yarn install
yarn encore dev
```

5. Update webinarОбновить количество просмотров курсов и вебинаров при DEV ENVIRONMENT
```shell
php bin/console app:import:course-webinar-views (--no-interaction optional)
```


###### Update project (production)
1. Clear and recreate dead urls
```shell
php bin/console app:delete:deadUrl
```

2. Update user registration types (needed only once!)
```shell
php bin/console app:update:item-registration-item-type (--no-interaction optional)
```

3. Move news to separate news table
```shell
php bin/console app:move:news (--no-interaction optional)
```

4. Upload area information
```shell
php bin/console app:region:update
```

5. Load hint fields
```shell
php bin/console app:import:createHintFields
```

7. Add additional user properties
```shell
php bin/console app:import:user-property --no-interaction
```

###### cron
1. Find and dispatch events with commodities.  Must be launched together with cron ```UserCommodityNotificationWorker```
```
*/5 * * * * php7.4 PRODUCTION_PATH_TO bin/console app:commodityEventWorker
```

2. Sends notifications
```
*/5 * * * * php7.4 PRODUCTION_PATH_TO bin/console app:userCommodityNotificationWorker
```

3. Sends feedback forms
```
*/5 * * * * php7.4 PRODUCTION_PATH_TO bin/console app:offerReviewNotificationEventWorker
```