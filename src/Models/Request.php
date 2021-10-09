<?php

namespace dnj\Request\Models;

use dnj\Request\Contracts\Queue\IRequestableJob;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @property array<mixed>                  $options
 * @property array<mixed>                  $response
 * @property class-string<IRequestableJob> $type
 * @property RequestStatus                 $status
 * @property string|null                   $job_uuid
 */
class Request extends Model
{
    /**
     * @param class-string<IRequestableJob> $jobClass
     * @param array<mixed>                  $options
     */
    public static function pushJob(string $jobClass, array $options = []): self
    {
        if (!class_exists($jobClass)) {
            throw new InvalidArgumentException('Given class not exists! class: '.$jobClass);
        }

        $job = new $jobClass();
        if (!($job instanceof IRequestableJob)) {
            throw new InvalidArgumentException('Given job class should be implement of: '.IRequestableJob::class);
        }

        $model = new self();
        $model->type = $jobClass;
        $model->options = $options;
        $model->save();
        $job->setRequest($model);

        Container::getInstance()->make('queue')->push($job);

        return $model;
    }

    public static function boot()
    {
        parent::boot();
        static::creating(static function (Request $model): void {
            $model->status = RequestStatus::NOT_STARTED();
            if (!isset($model->options)) {
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
     * @var array<string>
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
     * @var array<string,string>
     */
    protected $casts = [
        'status' => RequestStatus::class,
        'options' => 'json',
        'response' => 'array',
    ];

    /**
     * @param array<mixed> $response
     */
    public function setResponse(array $response, bool $keepOld = false): bool
    {
        if ($keepOld) {
            $response = array_replace((array) $this->response ?? [], $response);
        }
        $this->response = $response;

        return $this->save();
    }
}
