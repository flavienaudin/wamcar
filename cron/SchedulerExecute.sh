#!/bin/sh

BASEDIR=$(dirname "$0")
cd "$BASEDIR"/..

ENV="${SYMFONY_ENV:-dev}"

if [ "docker-cmd" = $1 ]
then
    export USER_ID=`id -u`
    export USER_GID=`id -g`
    docker-compose run php php -d memory_limit=512M bin/console scheduler:execute --env=${ENV}
else
    php bin/console scheduler:execute --env=${ENV}
fi