#!/usr/bin/env bash

set -ev

vendor/bin/blt tests:phpunit:run --define drush.alias='${drush.aliases.ci}' -D behat.web-driver=chrome --no-interaction --ansi --verbose
mkdir -p coverage
vendor/bin/phpunit -c phpunit.xml.dist --coverage-clover coverage/coverage.xml --verbose

set +v
