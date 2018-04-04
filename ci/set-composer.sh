#!/usr/bin/env bash
test ! -e ~/.composer && mkdir ~/.composer
echo '{"http-basic": {"artifacts.schibsted.io": {"username": "'$ARTIFACTORY_USER'","password":"'$ARTIFACTORY_PWD'"}}}' > ~/.composer/auth.json
