<?php
declare(strict_types=1);

namespace CakePages\Page;

use Cake\Controller\Controller as BaseController;
use Cake\Controller\Exception\MissingActionException;
use Cake\Core\App;
use Cake\Datasource\ModelAwareTrait;
use Cake\Event\EventDispatcherInterface;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManagerInterface;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Log\LogTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Routing\Router;
use Cake\View\ViewVarsTrait;
use Closure;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use UnexpectedValueException;

class Page extends BaseController implements EventListenerInterface, EventDispatcherInterface
{
    use PageTrait;

}
