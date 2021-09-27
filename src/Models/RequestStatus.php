<?php

namespace dnj\Request\Models;

use Spatie\Enum\Enum;

/**
 * @method static self NOT_STARTED()
 * @method static self RUNNING()
 * @method static self COMPLETED()
 * @method static self FAILED()
 */
class RequestStatus extends Enum
{
    /**
     * @return array<string,number>
     */
    protected static function values(): array
    {
        return [
            'NOT_STARTED' => 1,
            'RUNNING' => 2,
            'COMPLETED' => 3,
            'FAILED' => 4,
        ];
    }
}
