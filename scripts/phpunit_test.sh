#!/usr/bin/env bash

set -ev

vendor/bin/blt tests:phpunit:run --define drush.alias='${drush.aliases.ci}' -D behat.web-driver=chrome --no-interaction --ansi --verbose
# vendor/bin/phpunit -c phpunit.xml.dist --coverage-clover reports/phpunit/coverage.xml --verbose

set +v
