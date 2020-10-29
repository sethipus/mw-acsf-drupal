#!/usr/bin/env bash

set -ev

vendor/bin/blt tests:all -D behat.web-driver=chrome --no-interaction --ansi --verbose

set +v
