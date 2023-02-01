#!/usr/bin/env bash

# Exit if this process runs on non Robinhood machines
if [[ "${EMAIL}" != *"@robinhood.com" ]]; then
    exit 0
fi

set +o errexit

bazel run --ui_event_filters=-info,-stdout,-stderr --noshow_progress //secscan -- scan -d ${HOME}/robinhood/rh/ -s=pre-commit

EXIT_CODE=$?

set -o errexit

# Secscan exits with code 4 only in case where it has security findings,
# we exit with failure in only that scenario
# if [ $EXIT_CODE -eq 4 ]; then
#     exit 1
# fi
