<?php

namespace Hotrush\Stealer;

use React\Http\Response;
use FastRoute\Dispatcher;
use Psr\Log\LoggerInterface;
use Hotrush\Stealer\Spider\Registry;
use Psr\Http\Message\ServerRequestInterface;
use Hotrush\Stealer\ApiEndpoints\ListJobsEndpoint;
use Hotrush\Stealer\ApiEndpoints\CancelJobEndpoint;
use Hotrush\Stealer\ApiEndpoints\ScheduleJobEndpoint;

class Api
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Worker
     */
    private $worker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Dispatcher
     */
    private $routeDispatcher;

    /**
     * Api constructor.
     *
     * @param Registry          $registry
     * @param Worker            $worker
     * @param LoggerInterface   $logger
     */
    public function __construct(Registry $registry, Worker $worker, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->registry = $registry;
        $this->worker = $worker;
        $this->loadRouteDispatcher();
    }

    /**
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function dispatchRequest(ServerRequestInterface $request)
    {
        $routeInfo = $this->routeDispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        $this->logger->info(sprintf('%s %s requested', $request->getMethod(), $request->getUri()->getPath()));

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                $response = call_user_func(new $routeInfo[1]($this->registry, $this->worker), $routeInfo[2]);
                break;
            case Dispatcher::NOT_FOUND:
                $response = new Response(
                    404,
                    [
                        'Content-Type' => 'application/json'
                    ],
                    json_encode([
                        'message' => 'Not found.',
                    ])
                );
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = new Response(
                    405,
                    [
                        'Content-Type' => 'application/json'
                    ],
                    json_encode([
                        'message' => 'Method not allowed.',
                    ])
                );
                break;
            default:
                $response = new Response(
                    500,
                    [
                        'Content-Type' => 'application/json'
                    ],
                    json_encode([
                        'message' => 'Internal server error.',
                    ])
                );
                break;
        }

        $this->logger->info(
            sprintf(
                'Api responded with %s status code and body: %s',
                $response->getStatusCode(),
                $response->getBody()->getContents()
            )
        );

        return $response;
    }

    /**
     * Define routes dispatcher
     */
    private function loadRouteDispatcher()
    {
        $this->routeDispatcher = \FastRoute\simpleDispatcher(
            function(\FastRoute\RouteCollector $routes) {
                $routes->addRoute('GET', 'list', ListJobsEndpoint::class);
                $routes->addRoute('POST', 'schedule', ScheduleJobEndpoint::class);
                $routes->addRoute('POST', 'cancel', CancelJobEndpoint::class);
            }
        );
    }
}