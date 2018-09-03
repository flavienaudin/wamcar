#!/bin/sh

BASEDIR=$(dirname "$0")
cd "$BASEDIR"/..

if [ "docker-cmd" = $1 ]
then

    export USER_ID=`id -u`
    export USER_GID=`id -g`
    docker-compose run php php -d memory_limit=512M bin/console scheduler:execute
else
    php bin/console scheduler:execute
fi