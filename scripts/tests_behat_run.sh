#!/usr/bin/env bash

set -ev

vendor/bin/blt tests:behat:run --no-interaction --ansi --verbose

set +v
