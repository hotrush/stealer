<?php

namespace Hotrush\Stealer\ApiEndpoints;

use React\Http\Response;

class ScheduleJobEndpoint extends BaseEndpoint
{
    public function __invoke(array $payload)
    {
        if (!isset($payload['spider']) || !$this->registry->spiderExists($payload['spider'])) {
            return new Response(
                400,
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode([
                    'message' => 'No spider found.',
                ])
            );
        }

        $spider = $this->registry->getSpider($payload['spider']);
        $jobId = $this->worker->runSpiderJob($spider);

        return new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            json_encode([
                'message' => 'Job scheduled.',
                'job_id' => $jobId
            ])
        );
    }
}