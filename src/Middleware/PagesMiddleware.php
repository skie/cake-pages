<?php
declare(strict_types=1);

namespace CakePages\Middleware;

use CakePages\Page\PageFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PagesMiddleware implements MiddlewareInterface
{
    /**
     * Application instance that supports controller factory
     *
     * @var \CakePages\Middleware\ApplicationWithControllerFactory
     */
    private ApplicationWithControllerFactory $app;

    /**
     * Constructor
     *
     * @param \CakePages\Middleware\ApplicationWithControllerFactory $app Application instance
     */
    public function __construct(ApplicationWithControllerFactory $app)
    {
        $this->app = $app;
    }

    /**
     * Serve assets if the path matches one.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $factory = new PageFactory($this->app->getContainer());
        $this->app->setControllerFactory($factory);

        return $handler->handle($request);
    }
}
