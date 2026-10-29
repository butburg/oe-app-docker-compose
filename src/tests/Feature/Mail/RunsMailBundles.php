<?php

namespace Tests\Feature\Mail;

use App\Jobs\SendMailBundle;
use Illuminate\Support\Facades\Queue;

/**
 * For tests with Queue::fake(): runs the bundle jobs whose delay is over, like the worker would (FEAT-020).
 */
trait RunsMailBundles
{
    private array $ranBundles = [];

    protected function runDueBundles(): void
    {
        do {
            $due = Queue::pushed(SendMailBundle::class)
                ->filter(fn ($job) => $job->delay <= now() && !in_array($job, $this->ranBundles, true));
            $due->each(function ($job) {
                $this->ranBundles[] = $job;
                app()->call([$job, 'handle']);
            });
        } while ($due->isNotEmpty());
    }

    /**
     * Waits until the bundle window is over and sends.
     */
    protected function sendBundles(): void
    {
        $this->travel(SendMailBundle::minutes() + 1)->minutes();
        $this->runDueBundles();
    }
}
