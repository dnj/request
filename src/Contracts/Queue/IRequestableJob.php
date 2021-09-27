<?php

namespace dnj\Request\Contracts\Queue;

use dnj\Request\Models\Request;

interface IRequestableJob
{
    /**
     * Set a request model to a job.
     */
    public function setRequest(Request $request): void;

    /**
     * get the request model of job.
     */
    public function getRequest(): Request;
}
