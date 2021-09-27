<?php

namespace dnj\Request\Providers;

use dnj\Request\Contracts\Queue\IRequestableJob;
use dnj\Request\Models\RequestStatus;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;

class QueueWatcherServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $queue = $this->app->make('queue');
        $queue->before(static function (JobProcessing $event): void {
            if (!is_a($event->job->resolveName(), IRequestableJob::class, true)) {
                return;
            }
            $payload = $event->job->payload();

            /** @var IRequestableJob $realJob */
            $realJob = unserialize($payload['data']['command']);

            $request = $realJob->getRequest();
            $request->job_uuid = $event->job->uuid();
            $request->status = RequestStatus::RUNNING();
            $request->save();
        });

        $queue->after(static function (JobProcessed $event): void {
            if (!is_a($event->job->resolveName(), IRequestableJob::class, true)) {
                return;
            }
            $payload = $event->job->payload();

            /** @var IRequestableJob $realJob */
            $realJob = unserialize($payload['data']['command']);

            $request = $realJob->getRequest();
            if (!$request->status->equals(RequestStatus::FAILED())) {
                $request->status = RequestStatus::COMPLETED();
                $request->save();
            }
        });

        $queue->failing(static function (JobFailed $event): void {
            if (!is_a($event->job->resolveName(), IRequestableJob::class, true)) {
                return;
            }
            $payload = $event->job->payload();

            /** @var IRequestableJob $realJob */
            $realJob = unserialize($payload['data']['command']);

            $request = $realJob->getRequest();
            $request->status = RequestStatus::FAILED();
            $request->save();
        });
    }
}
