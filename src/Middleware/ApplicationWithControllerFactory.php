<?php
declare(strict_types=1);

namespace CakePages\Middleware;

use Cake\Core\ContainerInterface;
use Cake\Http\ControllerFactoryInterface;

/**
 * Application interface that supports setting controller factory
 *
 * Your Application class should implement this interface and provide
 * the setControllerFactory() method.
 */
interface ApplicationWithControllerFactory
{
    /**
     * Get the dependency injection container
     *
     * @return \Cake\Core\ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * Set the controller factory
     *
     * @param \Cake\Http\ControllerFactoryInterface $factory The controller factory
     * @return void
     */
    public function setControllerFactory(ControllerFactoryInterface $factory): void;
}
