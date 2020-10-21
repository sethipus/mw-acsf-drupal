#!/usr/bin/env bash

set -ev

vendor/bin/blt tests:behat:run -D behat.paths=Base.feature --no-interaction --ansi --verbose

set +v
