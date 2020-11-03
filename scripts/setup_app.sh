#!/usr/bin/env bash

set -ev

blt setup --define drush.alias='${drush.aliases.ci}' --no-interaction --ansi --verbose

set +v
