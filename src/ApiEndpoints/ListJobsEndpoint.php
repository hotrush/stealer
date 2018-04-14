<?php

namespace Hotrush\Stealer\ApiEndpoints;

use React\Http\Response;

class ListJobsEndpoint extends BaseEndpoint
{
    public function __invoke(): Response
    {
        $activeJobs = [];

        foreach ($this->worker->getActiveJobs() as $job) {
            $activeJobs[] = [
                'id'         => $job->getId(),
                'time_start' => $job->getStartTime(),
            ];
        }

        return new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            json_encode([
                'active_jobs' => $activeJobs,
            ])
        );
    }
}
