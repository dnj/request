<?php

namespace dnj\Request\Contracts\Queue;

use dnj\Request\Models\Request;

interface IRequestableJob
{
    /**
     * Set a request model to a job
     *
     * @param Request $request
     */
    public function setRequest(Request $request): void;

    /**
     * get the request model of job
     *
     * @return Request
     */
    public function getRequest(): Request;
}
