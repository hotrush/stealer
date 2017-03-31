<?php

namespace Hotrush\Stealer;

use Hotrush\Stealer\Spider\Registry;
use Monolog\Logger;
use React\Http\Request;
use React\Http\Response;

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
     * @param Logger   $logger
     * @param Worker   $worker
     */
    public function __construct(Registry $registry, Logger $logger, Worker $worker)
    {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->worker = $worker;
    }

    public function processRequest(Request $request, Response $response)
    {
        if ($request->getMethod() !== 'POST') {
            $this->replyWithError(405, 'Method now allowed.', $response);

            return;
        }

        $endpoint = substr($request->getPath(), 1).'Action';
        $data = [];

        $this->logger->info(substr($request->getPath(), 1).' action requested');

        $request->on('data', function ($requestData) use (&$data, $response) {
            $data = json_decode($requestData, true);
            if ($data === false) {
                $this->replyWithError(400, 'Invalid payload.', $response);

                return;
            }
            $this->logger->info('Request payload: '.$requestData);
        });

        $request->on('end', function () use (&$data, $endpoint, $response) {
            if (!method_exists($this, $endpoint)) {
                $this->replyWithError(404, 'Not found.', $response);

                return;
            }

            $this->$endpoint($data, $response);
        });

        $request->on('error', function () use ($response) {
            $this->logger->error('Error occurred while data receiving.');
            $this->replyWithError(500, 'Error occurred while data receiving.', $response);
        });

        $request->close();
    }

    private function replyWithError($code, $error, Response $response)
    {
        $this->logger->error('Api responded with '.$code.' status code and error message: '.$error);
        $response->writeHead($code, ['Content-Type' => 'application/json']);
        $response->end(json_encode([
            'message' => $error ? $error : 'Error occurred.',
        ]));
    }

    private function replyWithJson(array $data, Response $response)
    {
        $dataEncoded = json_encode($data);
        $this->logger->info('Api responded with 200 status code and data: '.$dataEncoded);
        $response->writeHead(200, ['Content-Type' => 'application/json; charset=utf-8']);
        $response->end($dataEncoded);
    }

    private function scheduleAction(array $payload, Response $response)
    {
        if (!isset($payload['spider']) || !$this->registry->spiderExists($payload['spider'])) {
            $this->replyWithError(400, 'No spider found', $response);

            return;
        }

        $spider = $this->registry->getSpider($payload['spider']);
        $jobId = $this->worker->runSpiderJob($spider);

        $this->replyWithJson(['message' => 'Job scheduled', 'job_id' => $jobId], $response);
    }

    private function listAction(array $payload, Response $response)
    {
        $activeJobs = [];

        foreach ($this->worker->getActiveJobs() as $item) {
            $activeJobs[] = [
                'id'         => $item->getId(),
                'time_start' => $item->getStartTime(),
            ];
        }

        $this->replyWithJson(['active_jobs' => $activeJobs], $response);
    }

    private function cancelAction(array $payload, Response $response)
    {
        if (!isset($payload['id'])) {
            $this->replyWithError(400, 'No job id provided.', $response);

            return;
        }

        try {
            $this->worker->stopJob($payload['id']);
            $this->replyWithJson([], $response);
        } catch (\InvalidArgumentException $e) {
            $this->replyWithError(400, $e->getMessage(), $response);
        }
    }
}
