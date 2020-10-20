#!/usr/bin/env bash

set -ev

vendor/bin/blt tests:behat:run -D behat.web-driver=google-chrome --no-interaction --verbose

set +v
