# Symfony Docker Environment


## Introduction:

The following steps will help you initiate and install the suitable development environment

## Setting global environment variables:

You'll probably need to customize your .env file in the root directory containing the docker-compose.yml file

## Installation:

Run the following command to download and build the containers: 
```
docker-compose up -d --build
```

## Stopping all containers:

I personnaly use the following command to stop all running containers because I usually use the same ports and some of the containers remain up so i need to stop them before :)
```
docker container stop $(docker container ps -aq)
```

## Starting the environment:

Please be careful, if you always run docker-compose up -d you may lose all config added to the containers. So i advice to run the following command to prevent recreation : 
```
docker-compose up -d --no-recreate --remove-orphans
```

## Rebuilding after adding a container or a config:

If you have edited  the Dockerfile files or  added some config  to docker-compose.yml (networking, ips ...), you need to recreate the container(s)

```
docker-compose up -d --force-recreate
```

## The Symfony Application:


Run the following command to execute commands in the server container: 
```
docker exec -it server bash
```

After that run the following commands to build your Symfony Project in codebase directory:

```
composer install
```

## Setting up database connection via .env file:

First run this command to create a .env file

```
docker exec -it server bash
composer dump-env dev
```

You'll probably need to customize the created .env file in codebase according to your database choice:

```
#To use mysql include this line:
DATABASE_URL='mysql://${DB_USER}:${DB_PASS}@db_server:${DB_PORT_INSIDE}/${DB_NAME}?serverVersion=${DB_SERVER_VERSION}&charset=${DB_CHARSET}'


#To use mariadb include this line instead:
DATABASE_URL='mysql://${DB_USER}:${DB_PASS}@db_server:${DB_PORT_INSIDE}/${DB_NAME}?serverVersion=${DB_SERVER}-${DB_SERVER_VERSION}'

#To use postgresql include this line instead:
DATABASE_URL='postgresql://${DB_USER}:${DB_PASS}@db_server:${DB_PORT_INSIDE}/${DB_NAME}?serverVersion=${DB_SERVER_VERSION}&charset=${DB_CHARSET}'


#NOTE: You should replace ( ${DB_USER} , ${DB_PASS} , ${DB_PORT_INSIDE}, ${DB_NAME} , ${DB_SERVER} , ${DB_SERVER_VERSION} , ${DB_CHARSET} ) by their respective values from .env file in the root directory

```

## Setting up database connection via docker-compose.yml file:

Before running (docker-compose up -d --build) command to start up your container, make sure to uncomment one of these lines in docker-compose.yml

```

# Uncomment these lines to prioritize it
#environment:
# Use mysql
#    - 'DATABASE_URL=mysql://${DB_USER}:${DB_PASS}@db_server:${DB_PORT_INSIDE}/${DB_NAME}?serverVersion=${DB_SERVER_VERSION}&charset=${DB_CHARSET}'
# Use mariadb
#    - 'DATABASE_URL=mysql://${DB_USER}:${DB_PASS}@db_server:${DB_PORT_INSIDE}/${DB_NAME}?serverVersion=${DB_SERVER}-${DB_SERVER_VERSION}'
# Use postgresql
#    - 'DATABASE_URL=postgresql://${DB_USER}:${DB_PASS}@db_server:${DB_PORT_INSIDE}/${DB_NAME}?serverVersion=${DB_SERVER_VERSION}&charset=${DB_CHARSET}'

#NOTE: You don't have to replace ( ${DB_USER} , ${DB_PASS} , ${DB_PORT_INSIDE}, ${DB_NAME} , ${DB_SERVER} , ${DB_SERVER_VERSION} , ${DB_CHARSET} ).
```

## Run database migrations load fake data and start queue worker:

NOTE: Every command related to the symfony should be executed in the server container: 
```
docker exec -it server bash
```

Run migrations

```
php bin/console d:m:m --no-interaction 

OR

symfony console d:m:m --no-interaction 
```

Update schema

```
php bin/console d:s:u --force

OR

symfony console d:s:u --force
```

Load fake data

```
php bin/console d:f:l --no-interaction

OR

symfony console d:f:l --no-interaction
```

Start queue worker

```
php bin/console messenger:consume -vv async

OR

symfony console messenger:consume -vv async
```

That's all! 

Go to [http://localhost](http://localhost) to see the app

Happy hacking !