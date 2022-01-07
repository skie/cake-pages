<?php
declare(strict_types=1);

namespace CakePages\Page;

use Cake\Controller\Exception\MissingActionException;
use Cake\Http\ServerRequest;
use Closure;

/**
 * Trait PageTrait
 *
 * @package CakePages\Page
 * @property ServerRequest $request
 */
trait PageTrait
{
    public function getAction(): Closure
    {
        $request = $this->request;
        $action = $request->getParam('action');
        $method = $request->getMethod();
        $actionName = 'on' . substr($method, 0, 1) . substr(strtolower($method), 1);

        if (!$this->isAction($actionName)) {
            throw new MissingActionException([
                'controller' => $this->name . 'Controller',
                'action' => $request->getParam('action'),
                'prefix' => $request->getParam('prefix') ?: '',
                'plugin' => $request->getParam('plugin'),
            ]);
        }

        return Closure::fromCallable([$this, $actionName]);
    }
}
