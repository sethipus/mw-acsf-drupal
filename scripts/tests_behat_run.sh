#!/usr/bin/env bash

set -ev

vendor/bin/blt tests:behat:run --environment=ci --no-interaction --ansi --verbose

set +v
