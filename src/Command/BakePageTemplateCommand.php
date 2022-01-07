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
namespace CakePages\Command;

use Bake\Command\TemplateCommand as BaseTemplateCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Utility\Inflector;
use CakePages\Utility\TemplateRenderer;

/**
 * Task class for creating view template files.
 */
class BakePageTemplateCommand extends BaseTemplateCommand
{
    /**
     * Builds content from template and variables
     *
     * @param \Cake\Console\Arguments $args The CLI arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @param string $action name to generate content to
     * @param array|null $vars passed for use in templates
     * @return string|false Content from template
     */
    public function getContent(Arguments $args, ConsoleIo $io, string $action, ?array $vars = null)
    {
        if (!$vars) {
            $vars = $this->_loadController($io);
        }

        [, $viewModelClass] = namespaceSplit($this->_viewModelName($this->modelName));
        $viewModelClass .= $this->_camelize($action);
        $viewModelClass = sprintf('%s\ViewModel\%s\%s', $vars['namespace'], $this->controllerName, $viewModelClass);
        if (!class_exists($viewModelClass)) {
            $viewModelClass = null;
        }
        $vars['viewModelClass'] = $viewModelClass;

        if (empty($vars['primaryKey'])) {
            $io->error('Cannot generate views for models with no primary key');
            $this->abort();
        }

        $renderer = new TemplateRenderer($args->getOption('theme'));
        $renderer->set('action', $action);
        $renderer->set('plugin', $this->plugin);
        $renderer->set($vars);

        $indexColumns = 0;
        if ($action === 'index' && $args->getOption('index-columns') !== null) {
            $indexColumns = $args->getOption('index-columns');
        }
        $renderer->set('indexColumns', $indexColumns);

        return $renderer->generate("CakePages.Template/$action");
    }

    /**
     * Creates the proper entity name (singular) for the specified name
     *
     * @param string $name Name
     * @return string Camelized and plural model name
     */
    protected function _viewModelName(string $name): string
    {
        return Inflector::singularize(Inflector::camelize($name));
    }

}
