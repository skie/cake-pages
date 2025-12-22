<?php
declare(strict_types=1);

namespace TestApp;

use Cake\Core\ContainerInterface;
use Cake\Http\BaseApplication;
use Cake\Http\ControllerFactoryInterface;
use Cake\Http\MiddlewareQueue;

class TestApplication extends BaseApplication
{
    protected ?ControllerFactoryInterface $controllerFactory = null;

    public function bootstrap(): void
    {
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        return $middlewareQueue;
    }

    public function setControllerFactory(ControllerFactoryInterface $factory): void
    {
        $this->controllerFactory = $factory;
    }

    public function getControllerFactory(): ?ControllerFactoryInterface
    {
        return $this->controllerFactory;
    }

    public function getContainer(): ContainerInterface
    {
        return parent::getContainer();
    }
}
