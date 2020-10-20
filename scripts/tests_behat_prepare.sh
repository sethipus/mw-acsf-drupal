#!/usr/bin/env bash

set -ev

vendor/bin/blt setup --no-interaction --verbose
composer require behat/mink-selenium2-driver

set +v
