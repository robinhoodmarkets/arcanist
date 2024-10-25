#!/usr/bin/env bash

set -euo pipefail

EVENT_FILE=$1

START_TS=$(perl -MTime::HiRes=time -e 'printf "%.0f\n", time * 1000000')

set +e

REPO_PATH="$(git rev-parse --show-toplevel)"

# run secscan cache target
"$REPO_PATH/secscan/scripts/secscan_cache.sh" "$REPO_PATH"

EXIT_CODE=$?

END_TS=$(perl -MTime::HiRes=time -e 'printf "%.0f\n", time * 1000000')

if [ -e "$EVENT_FILE" ]
then
    printf "{\"event_name\":\"secscan\",\"event_detail\":\"run secscan\",\"event_start_ts\":%s,\"event_end_ts\":%s}\n" "$START_TS" "$END_TS" >> "$EVENT_FILE"
fi

# Write execution time (in seconds) to a file for parent process (arcanistdiffworkflow.php) to pick up
echo -n $(echo "scale=6; ($END_TS - $START_TS) / 1000000" | bc) > "/tmp/.secscan_execution_time"

set -e

# Secscan exits with code 4 only in case where it has findings,
# we should only block the diff in that scenario.
if [ $EXIT_CODE -eq 4 ]; then
    exit 1
fi

exit 0
