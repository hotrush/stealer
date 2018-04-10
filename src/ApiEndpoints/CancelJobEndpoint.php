<?php

namespace Hotrush\Stealer\ApiEndpoints;

use React\Http\Response;

class CancelJobEndpoint extends BaseEndpoint
{
    public function __invoke(array $payload)
    {
        if (!isset($payload['id'])) {
            return new Response(
                400,
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode([
                    'message' => 'No job id provided.',
                ])
            );
        }

        try {
            $this->worker->stopJob($payload['id']);
            return new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode([])
            );
        } catch (\InvalidArgumentException $e) {
            return new Response(
                400,
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode([
                    'message' => $e->getMessage(),
                ])
            );
        }
    }
}