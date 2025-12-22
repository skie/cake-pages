<?php
declare(strict_types=1);

namespace CakePages\Test\TestCase\Middleware;

use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use CakePages\Middleware\PagesMiddleware;
use CakePages\Page\PageFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use TestApp\TestApplication;

class PagesMiddlewareTest extends TestCase
{
    protected TestApplication $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new TestApplication(CONFIG);
    }

    public function testProcessSetsControllerFactory(): void
    {
        $middleware = new PagesMiddleware($this->app);
        $request = new ServerRequest();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = new Response();
        $handler->method('handle')
            ->willReturn($response);

        $middleware->process($request, $handler);

        $factory = $this->app->getControllerFactory();
        $this->assertInstanceOf(PageFactory::class, $factory);
    }

    public function testProcessPassesRequestToHandler(): void
    {
        $middleware = new PagesMiddleware($this->app);
        $request = new ServerRequest(['url' => '/test']);
        $response = new Response();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->identicalTo($request))
            ->willReturn($response);

        $result = $middleware->process($request, $handler);
        $this->assertSame($response, $result);
    }

    public function testProcessReturnsHandlerResponse(): void
    {
        $middleware = new PagesMiddleware($this->app);
        $request = new ServerRequest();
        $response = new Response();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willReturn($response);

        $result = $middleware->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame($response, $result);
    }

    public function testControllerFactoryUsesApplicationContainer(): void
    {
        $middleware = new PagesMiddleware($this->app);
        $request = new ServerRequest();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willReturn(new Response());

        $middleware->process($request, $handler);

        $factory = $this->app->getControllerFactory();
        $this->assertInstanceOf(PageFactory::class, $factory);

        $reflection = new ReflectionClass($factory);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $container = $containerProperty->getValue($factory);

        $this->assertSame($this->app->getContainer(), $container);
    }
}
