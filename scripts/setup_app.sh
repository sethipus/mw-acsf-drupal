#!/usr/bin/env bash

set -ev

blt setup --define drush.alias='${drush.aliases.local}' --no-interaction --ansi --verbose

set +v
