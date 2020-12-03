#!/usr/bin/env bash

set -ev

vendor/bin/blt tests:phpunit:run --define drush.alias='${drush.aliases.ci}' -D behat.web-driver=chrome --no-interaction --ansi --verbose

set +v
