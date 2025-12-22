<?php
declare(strict_types=1);

namespace CakePages\Page;

use Cake\Controller\Controller as BaseController;
use Cake\Event\EventDispatcherInterface;
use Cake\Event\EventListenerInterface;

class Page extends BaseController implements EventListenerInterface, EventDispatcherInterface
{
    use PageTrait;
}
