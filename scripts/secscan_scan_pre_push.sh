#!/usr/bin/env bash

set -euo pipefail

EVENT_FILE=$1

START_TS=$(perl -MTime::HiRes=time -e 'printf "%.0f\n", time * 1000000')

set +e

# run secscan cache target
bazel run --ui_event_filters=-info,-stdout,-stderr --noshow_progress //secscan/scripts:secscan_cache

EXIT_CODE=$?

END_TS=$(perl -MTime::HiRes=time -e 'printf "%.0f\n", time * 1000000')

if [ -e "$EVENT_FILE" ]
then
    printf "{\"event_name\":\"secscan\",\"event_detail\":\"run secscan\",\"event_start_ts\":%s,\"event_end_ts\":%s}\n" "$START_TS" "$END_TS" >> "$EVENT_FILE"
fi

set -e

# Secscan exits with code 4 only in case where it has findings,
# we should only block the diff in that scenario.
if [ $EXIT_CODE -eq 4 ]; then
    exit 1
fi

exit 0
