<?php

namespace dnj\Request\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\{JobFailed, JobProcessing, JobProcessed};
use dnj\Request\Contracts\Queue\IRequestableJob;
use dnj\Request\Models\Request;

class QueueWatcherServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Queue::before(static function(JobProcessing $event): void {
            if (!is_a($event->job->resolveName(), IRequestableJob::class, true)) {
                return;
            }
            $payload = $event->job->payload();

            /** @var IRequestableJob $realJob */
            $realJob = unserialize($payload["data"]["command"]);

            $request = $realJob->getRequest();
            $request->job_uuid = $event->job->uuid();
            $request->status = Request::RUNNING;
            $request->save();
        });
          
        Queue::after(static function(JobProcessed $event): void {
            if (!is_a($event->job->resolveName(), IRequestableJob::class, true)) {
                return;
            }
            $payload = $event->job->payload();

            /** @var IRequestableJob $realJob */
            $realJob = unserialize($payload["data"]["command"]);

            $request = $realJob->getRequest();
            $request->status = Request::COMPLETED;
            $request->save();
        });
          
        Queue::failing(static function(JobFailed $event): void {
            if (!is_a($event->job->resolveName(), IRequestableJob::class, true)) {
                return;
            }
            $payload = $event->job->payload();

            /** @var IRequestableJob $realJob */
            $realJob = unserialize($payload["data"]["command"]);

            $request = $realJob->getRequest();
            $request->status = Request::FAILED;
            $request->save();
        });
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        
    }
}
