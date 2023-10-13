#!/usr/bin/env bash

set +o errexit
REPO_PATH="$(git rev-parse --show-toplevel)"
BASE_COMMIT=$(
  git log \
    --oneline \
    --committer="phlq" \
    --pretty="format:%H" \
    --max-count=50 \
    | head -1
)

bazel run --ui_event_filters=-info,-stdout,-stderr --noshow_progress //secscan/tools/trufflehog:scan_local -- $REPO_PATH $BASE_COMMIT

EXIT_CODE=$?

set -o errexit

# Secscan exits with code 4 only in case where it has findings,
# we should only block the diff in that scenario.
if [ $EXIT_CODE -eq 4 ]; then
    exit 1
fi

exit 0
