#!/bin/sh

BASEDIR=$(dirname "$0")
cd "$BASEDIR"/..

ARG_1="$1:-''"
ENV="${SYMFONY_ENV:-dev}"

if [ "docker-cmd" = "${ARG_1}" ]
then
    export USER_ID=`id -u`
    export USER_GID=`id -g`
    docker-compose run php php -d memory_limit=512M bin/console scheduler:execute --env=${ENV}
else
    export MAILER_TRANSPORT="${MAILER_TRANSPORT:-smtp}"
    export MAILER_HOST="${MAILER_HOST:-smtp.gmail.com}"
    export MAILER_PORT="${MAILER_PORT:-587}"
    export MAILER_USER="${MAILER_USER:-wamcartest@gmail.com}"
    export MAILER_PASSWORD="${MAILER_PASSWORD}"
    export MAILER_DEFAUT_SENDER_ADDRESS="${MAILER_DEFAUT_SENDER_ADDRESS:-wamcartest@gmail.com}"
    export MAILER_DEFAUT_SENDER_NAME="${MAILER_DEFAUT_SENDER_NAME:-Wamcar}"

    export REQUEST_CONTEXT_HOST="${MAILER_DEFAUT_SENDER_NAME:-www.wamcar.com}"
    export REQUEST_CONTEXT_SCHEME="${REQUEST_CONTEXT_SCHEME:-https}"
    export REQUEST_CONTEXT_BASE_URL=

    php bin/console scheduler:execute --env=${ENV}
fi