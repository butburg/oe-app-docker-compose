// k6 load test for image delivery (FEAT-011), started by images.sh.
// URLS: newline separated list of absolute URLs, requests are spread over them round robin.
import http from 'k6/http';
import { check } from 'k6';

const urls = __ENV.URLS.split('\n').filter((u) => u.length > 0);

export const options = {
    vus: Number(__ENV.VUS || 10),
    iterations: Number(__ENV.REQUESTS || 2000),
    summaryTrendStats: ['p(50)', 'p(95)', 'p(99)'],
};

export default function () {
    const res = http.get(urls[(__ITER + __VU * 7919) % urls.length]);
    check(res, { 'status 200': (r) => r.status === 200 });
}

// One CSV line: p50,p95,p99 (ms), requests per second, failed requests
export function handleSummary(data) {
    const d = data.metrics.http_req_duration.values;
    const line = [d['p(50)'], d['p(95)'], d['p(99)'], data.metrics.http_reqs.values.rate]
        .map((v) => v.toFixed(2)).join(',') + ',' + data.metrics.http_req_failed.values.passes;
    return { stdout: line + '\n' };
}
