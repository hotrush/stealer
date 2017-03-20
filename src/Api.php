<?php

namespace Hotrush\Stealer;

use Hotrush\Stealer\Spider\Registry;
use Monolog\Logger;
use React\Http\Request;
use React\Http\Response;
use React\Promise\Deferred;

class Api
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Worker
     */
    private $worker;

    /**
     * Api constructor.
     *
     * @param Registry $registry
     * @param Logger $logger
     * @param Worker $worker
     */
    public function __construct(Registry $registry, Logger $logger, Worker $worker)
    {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->worker = $worker;
    }

    public function processRequest(Request $request, Response $response)
    {
        if ($request->getMethod() !== 'POST')
        {
            $this->replyWithError(405, 'Method now allowed.', $response);
            return;
        }

        $this->getEndpointAndPayload($request)
            ->then(function($data) use ($response, $request) {
                list($action, $payload) = $data;

                if (!method_exists($this, $action))
                {
                    $this->replyWithError(404, 'Not found.', $response);
                    return;
                }

                $this->$action($payload, $response);

            }, function($reason) use ($response) {
                $this->replyWithError(500, $reason, $response);
            });
    }

    private function getEndpointAndPayload(Request $request)
    {
        $deferred = new Deferred();
        $endpoint = substr($request->getPath(), 1).'Action';
        $data = [];

        // @todo DO SOMETHING WITH REQUESTS WITHOUT PAYLOAD !!!!
        $this->logger->info(substr($request->getPath(), 1) . ' action requested');

        $request->on('data', function ($requestData) use ($deferred, &$data, $endpoint) {
            $data = json_decode($requestData, true);
            if ($data === false)
            {
                $deferred->reject('Invalid payload.');
            }
            $this->logger->info('Request payload: ' . $requestData);
        });

        $request->on('end', function() use ($deferred, &$data, $endpoint) {
            $deferred->resolve([$endpoint, $data]);
        });

        $request->on('error', function() use ($deferred) {
            $this->logger->error('Error occurred while data receiving.');
            $deferred->reject('Error occurred while data receiving.');
        });

        return $deferred->promise();
    }

    public function replyWithError($code, $error, Response $response)
    {
        $this->logger->error('Api responded with ' . $code . ' status code and error message: ' . $error);
        $response->writeHead($code, array('Content-Type' => 'application/json'));
        $response->end(json_encode([
            'message' => $error ? $error : 'Error occurred.',
        ]));
    }

    private function scheduleAction(array $payload, Response $response)
    {
        if (!isset($payload['spider']) || !$this->registry->spiderExists($payload['spider']))
        {
            $this->replyWithError(400, 'No spider found', $response);
            return;
        }

        $spider = $this->registry->getSpider($payload['spider']);
        $jobId = $this->worker->runSpiderJob($spider);

        $response->writeHead(200, array('Content-Type' => 'application/json; charset=utf-8'));
        $response->end(json_encode(['message' => 'Job scheduled', 'job_id' => $jobId]));
    }

    private function listjobsAction(array $payload, Response $response)
    {
        $activeJobs = [];

        foreach ($this->worker->getActiveJobs() as $item)
        {
            $activeJobs[] = [
                'id' => $item->getId(),
                'time_start' => $item->getStartTime(),
            ];
        }

        $response->writeHead(200, array('Content-Type' => 'application/json; charset=utf-8'));
        $response->end(json_encode(['active_jobs' => $activeJobs]));
    }
}
