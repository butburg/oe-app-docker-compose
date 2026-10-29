#!/usr/bin/env bash
# Load test for image delivery (FEAT-011). Runs k6 in a throwaway container on the compose network
# against the running stack (docker compose up -d app). Uses the data already in the DB (static seed),
# so before/after runs measure the same images. Prints the median of RUNS runs per scenario.
#
#   scripts/perf/images.sh [label]
set -euo pipefail

cd "$(dirname "$0")/../.."
LABEL="${1:-run}"
RUNS="${RUNS:-3}"
NETWORK="$(docker network ls --format '{{.Name}}' | grep -m1 '_laravel$')"
K6_IMAGE="grafana/k6:0.54.0"
BASE="http://app"

# Image URLs of the first gallery page as a guest, rewritten from APP_URL to the nginx service name
PAGE="$(docker run --rm --network "$NETWORK" curlimages/curl:8.10.1 -s "$BASE/")"
URLS="$(grep -oE 'src="[^"]*(/storage/files/|/img/)[^"]*"' <<<"$PAGE" | sed -E 's/^src="//; s/"$//; s#^https?://[^/]+#'"$BASE"'#' | sort -u)"
COUNT="$(grep -c . <<<"$URLS" || true)"
if [ "$COUNT" -eq 0 ]; then
    echo "No uploaded images found on $BASE/" >&2
    exit 1
fi

run_k6() { # requests vus urls
    docker run --rm -i --network "$NETWORK" -e URLS="$3" -e REQUESTS="$1" -e VUS="$2" \
        "$K6_IMAGE" run --quiet - <scripts/perf/images.js | tail -n1
}

median() { # column of the collected CSV lines
    cut -d, -f"$1" | sort -n | sed -n "$(((RUNS + 1) / 2))p"
}

scenario() { # name requests vus urls
    local lines=""
    for _ in $(seq "$RUNS"); do
        lines+="$(run_k6 "$2" "$3" "$4")"$'\n'
    done
    lines="$(grep . <<<"$lines")"
    printf '| %s | %s | %s | %s | %s | %s | %s |\n' "$LABEL" "$1" \
        "$(median 1 <<<"$lines")" "$(median 2 <<<"$lines")" "$(median 3 <<<"$lines")" \
        "$(median 4 <<<"$lines")" "$(median 5 <<<"$lines")"
}

echo "$COUNT image URLs, $RUNS runs per scenario, median shown. $(nproc) CPUs, $(grep -m1 'model name' /proc/cpuinfo | cut -d: -f2 | xargs)"
echo "| Label | Scenario | p50 ms | p95 ms | p99 ms | req/s | failed |"
echo "|---|---|---|---|---|---|---|"
scenario "images c10 (2000 req)" 2000 10 "$URLS"
scenario "images c50 (2000 req)" 2000 50 "$URLS"
scenario "gallery page c10 (200 req)" 200 10 "$BASE/"
