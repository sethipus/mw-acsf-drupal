#!/usr/bin/env bash

set -ev

vendor/bin/blt tests:behat:run -D behat.web-driver=chrome --no-interaction --verbose

set +v
