<?php

namespace dnj\Request\Models;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;
use dnj\Request\Contracts\Queue\IRequestableJob;

/**
 * @property array<mixed>|array<string,mixed> $options
 * @property array<mixed>|array<string,mixed> $response
 */
class Request extends Model
{
    const NO_STARTED = 1;
    const RUNNING = 2;
    const COMPLETED = 3;
    const FAILED = 4;

    const STATUSES = [
        self::NO_STARTED,
        self::RUNNING,
        self::COMPLETED,
        self::FAILED,
    ];

    public static function pushJob(string $jobClass, array $options = []): self
    {
        if (!class_exists($jobClass)) {
            throw new InvalidArgumentException('Given class not exists! class: ' . $jobClass);
        }

        $model = new self();

        $job = new $jobClass();
        if (!($job instanceof IRequestableJob)) {
            throw new InvalidArgumentException('Given job class should be implement of: ' . IRequestableJob::class);
        }

        $model->type = $jobClass;
        $model->options = $options;
        $model->save();
        $job->setRequest($model);

        Queue::push($job);
        return $model;
    }

    public static function boot()
    {
        parent::boot();
        static::creating(static function (Request $model): void {
            if (!$model->status) {
                $model->status = self::NO_STARTED;
            }
            if (!$model->options) {
                $model->options = [];
            }
        });
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'job_uuid',
        'type',
        'options',
        'response',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'options'   => 'json',
        'response'   => 'array',
    ];

    public function setResponse(array $response, bool $keepOld = false): bool
    {
        if ($keepOld) {
            $response = array_merge((array)$this->response ?? [], $response);
        }
        $this->response = $response;
        return $this->save();
    }

    public function getHumanReadableStatus(): ?string
    {
        switch ($this->status)
        {
            case self::NO_STARTED: return 'NO_STARTED';
            case self::RUNNING: return 'RUNNING';
            case self::COMPLETED: return 'COMPLETED';
            case self::FAILED: return 'FAILED';
        }
        return null;
    }
}
