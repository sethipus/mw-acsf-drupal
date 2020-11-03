#!/usr/bin/env bash

set -ev

vendor/bin/blt setup --define drush.alias='${drush.aliases.ci}' --no-interaction --ansi --verbose --environment local

set +v
