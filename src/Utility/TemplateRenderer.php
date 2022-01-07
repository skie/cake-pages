<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakePages\Utility;

use Bake\View\BakeView;
use Cake\Core\ConventionsTrait;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\View\Exception\MissingTemplateException;
use Cake\View\View;
use Cake\View\ViewVarsTrait;
use Bake\Utility\TemplateRenderer as BaseRenderer;

/**
 * Used by other tasks to generate templated output, Acts as an interface to BakeView
 */
class TemplateRenderer extends BaseRenderer
{
    /**
     * Get view instance
     *
     * @return \Cake\View\View
     * @triggers Bake.initialize $view
     */
    public function getView(): View
    {
        if ($this->view) {
            return $this->view;
        }

        $this->viewBuilder()
            ->setHelpers(['Bake.Bake', 'Bake.DocBlock', 'CakePages.PageBake'])
            ->setTheme($this->theme);

        $view = $this->createView(BakeView::class);
        $event = new Event('Bake.initialize', $view);
        EventManager::instance()->dispatch($event);
        /** @var \Bake\View\BakeView $view */
        $view = $event->getSubject();
        $this->view = $view;

        return $this->view;
    }
}
