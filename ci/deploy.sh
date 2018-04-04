#!/usr/bin/env bash
VERSION=$1
ORG_NAME=letsdeal-no
PACKAGE_NAME=spid-client

zip $PACKAGE_NAME-$VERSION.zip -qr src composer.json README.md
export JFROG_CLI_OFFER_CONFIG=false
jfrog rt upload \
    --props=composer.version=$VERSION \
    --apikey="$ARTIFACTORY_PWD" --user="$ARTIFACTORY_USER" \
    --url=https://artifacts.schibsted.io/artifactory/ \
    $PACKAGE_NAME-$VERSION.zip php-local/$ORG_NAME/$PACKAGE_NAME/$PACKAGE_NAME-$VERSION.zip
