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

use Bake\Command\BakeCommand;
use Bake\Command\TestCommand;
use Bake\Utility\TableScanner;
use Bake\Utility\TemplateRenderer;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Entity;

/**
 * Task class for creating and updating page files.
 */
class BakePageCommand extends BakeCommand
{
    /**
     * Path fragment for generated code.
     *
     * @var string
     */
    public string $pathFragment = 'Controller/';

    /**
     * Execute the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->extractCommonProperties($args);
        $name = $args->getArgument('name') ?? '';
        $name = $this->_getName($name);

        if (empty($name) && $args->getOption('base')) {
            $this->bakeBase($args, $io);

            return static::CODE_SUCCESS;
        } elseif (empty($name)) {
            /** @var \Cake\Database\Connection $connection */
            $connection = ConnectionManager::get($this->connection);
            $scanner = new TableScanner($connection);
            $io->out('Possible pages based on your current database:');
            foreach ($scanner->listUnskipped() as $table) {
                $io->out('- ' . $this->_camelize($table));
            }

            return static::CODE_SUCCESS;
        }

        $controller = $this->_camelize($name);
        $this->bake($controller, $args, $io);

        return static::CODE_SUCCESS;
    }

    /**
     * Assembles and writes a Controller file
     *
     * @param string $controllerName Controller name already pluralized and correctly cased.
     * @param \Cake\Console\Arguments $args The console arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return void
     */
    public function bake(string $controllerName, Arguments $args, ConsoleIo $io): void
    {
        $io->quiet(sprintf('Baking page classes for %s...', $controllerName));

        $actions = [];
        if (!$args->getOption('no-actions') && !$args->getOption('actions')) {
            $actions = ['index', 'view', 'add', 'edit', 'delete'];
        }
        if ($args->getOption('actions')) {
            $actions = array_map('trim', explode(',', $args->getOption('actions')));
            $actions = array_filter($actions);
        }

        $helpers = $this->getHelpers($args);
        $components = $this->getComponents($args);

        $prefix = $this->getPrefix($args);
        if ($prefix) {
            $prefix = '\\' . str_replace('/', '\\', $prefix);
        }

        // Pages default to importing AppController from `App`
        $baseNamespace = $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
        }
        // If the plugin has an AppController other plugin controllers
        // should inherit from it.
        if ($this->plugin && class_exists("{$namespace}\Controller\AppController")) {
            $baseNamespace = $namespace;
        }

        $currentModelName = $controllerName;
        $plugin = $this->plugin;
        if ($plugin) {
            $plugin .= '.';
        }

        if ($this->getTableLocator()->exists($plugin . $currentModelName)) {
            $modelObj = $this->getTableLocator()->get($plugin . $currentModelName);
        } else {
            $modelObj = $this->getTableLocator()->get($plugin . $currentModelName, [
                'connectionName' => $this->connection,
            ]);
        }

        $pluralName = $this->_variableName($currentModelName);
        $singularName = $this->_singularName($currentModelName);
        $singularHumanName = $this->_singularHumanName($controllerName);
        $pluralHumanName = $this->_variableName($controllerName);

        $defaultModel = sprintf('%s\Model\Table\%sTable', $namespace, $controllerName);
        if (!class_exists($defaultModel)) {
            $defaultModel = null;
        }
        $entityClassName = $this->_entityName($modelObj->getAlias());

        [, $entityClass] = namespaceSplit($entityClassName);
        $entityClassFqn = sprintf('%s\Model\Entity\%s', $namespace, $entityClass);
        $entityExists = class_exists($entityClassFqn);
        if (!$entityExists) {
            $entityClassFqn = sprintf('\%s', Entity::class);
        }

        $data = compact(
            // 'actions',
            'components',
            'currentModelName',
            'defaultModel',
            'entityClassName',
            'helpers',
            'modelObj',
            'namespace',
            'baseNamespace',
            'plugin',
            'pluralHumanName',
            'pluralName',
            'prefix',
            'singularHumanName',
            'singularName',
        );
        $data['name'] = $controllerName;
        $data['entityClassFqn'] = $entityClassFqn;
        $data['entityExists'] = $entityExists;
        $data['entityTypeHint'] = $entityExists ? $entityClassName : 'Entity';
        foreach ($actions as $action) {
            // $actionData = $data;
            $actionClass = strtoupper(substr($action, 0, 1)) . substr($action, 1);
            // $actionData['namespace'] .= '\\' . $actionClassName;
            $data['action'] = $action;
            $data['actionClass'] = $actionClass;

            $data['classImports'] = $this->getPageImports(
                $namespace,
                $controllerName,
                $prefix,
                $entityClassName,
                $actionClass,
                $this->plugin,
                $baseNamespace,
                $entityExists,
            );

            $this->bakePage($controllerName, $data, $args, $io);
            $this->bakeTest($controllerName, $args, $io);
        }
    }

    /**
     * Bake base AppPage class
     *
     * @param \Cake\Console\Arguments $args The console arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return void
     */
    public function bakeBase(Arguments $args, ConsoleIo $io): void
    {
        $prefix = $this->getPrefix($args);
        if ($prefix) {
            $prefix = '\\' . str_replace('/', '\\', $prefix);
        }

        $baseNamespace = $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
        }

        if ($this->plugin && class_exists("{$namespace}\Controller\AppController")) {
            $baseNamespace = $namespace;
        }

        $plugin = $this->plugin;
        if ($plugin) {
            $plugin .= '.';
        }

        $data = compact(
            // 'components',
            // 'helpers',
            'namespace',
            'baseNamespace',
            'plugin',
            'prefix',
        );

        // $data += [
            // 'namespace' => null,
            // 'prefix' => null,
            // 'helpers' => null,
            // 'components' => null,
            // 'plugin' => null,
            // 'pluginPath' => null,
        // ];

        $renderer = new TemplateRenderer($this->theme);
        $renderer->set($data);

        $contents = $renderer->generate('CakePages.Page/app_page');

        $path = $this->getPath($args);
        $filename = $path . 'AppPage.php';
        $io->createFile($filename, $contents, $args->getOption('force'));
    }

    /**
     * Generate the page code
     *
     * @param string $controllerName The name of the controller.
     * @param array<string, mixed> $data The data to turn into code.
     * @param \Cake\Console\Arguments $args The console args
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return void
     */
    public function bakePage(string $controllerName, array $data, Arguments $args, ConsoleIo $io): void
    {
        $data += [
            'name' => null,
            'namespace' => null,
            'prefix' => null,
            'actionClass' => null,
            'helpers' => null,
            'components' => null,
            'plugin' => null,
            'pluginPath' => null,
            'classImports' => [],
        ];

        $renderer = new TemplateRenderer($this->theme);
        $renderer->set($data);

        $contents = $renderer->generate('CakePages.Page/page');

        $path = $this->getPath($args);
        $filename = $path . $controllerName . DS . $data['actionClass'] . 'Page.php';
        $io->createFile($filename, $contents, $args->getOption('force'));
    }

    /**
     * Assembles and writes a unit test file
     *
     * @param string $className Controller class name
     * @param \Cake\Console\Arguments $args The console arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return void
     */
    public function bakeTest(string $className, Arguments $args, ConsoleIo $io): void
    {
        if ($args->getOption('no-test')) {
            return;
        }
        if (class_exists(TestCommand::class)) {
            /** @phpstan-var \Bake\Command\TestCommand $test */
            $test = new TestCommand();
            $testArgs = new Arguments(
                ['controller', $className],
                $args->getOptions(),
                ['type', 'name'],
            );
            $test->execute($testArgs, $io);
        }
    }

    /**
     * Get the list of components for the controller.
     *
     * @param \Cake\Console\Arguments $args The console arguments
     * @return array<string>
     */
    public function getComponents(Arguments $args): array
    {
        $components = [];
        if ($args->getOption('components')) {
            $components = explode(',', $args->getOption('components'));
            $components = array_values(array_filter(array_map('trim', $components)));
        }

        return $components;
    }

    /**
     * Get the list of helpers for the controller.
     *
     * @param \Cake\Console\Arguments $args The console arguments
     * @return array<string>
     */
    public function getHelpers(Arguments $args): array
    {
        $helpers = [];
        if ($args->getOption('helpers')) {
            $helpers = explode(',', $args->getOption('helpers'));
            $helpers = array_values(array_filter(array_map('trim', $helpers)));
        }

        return $helpers;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The console option parser
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->_setCommonOptions($parser);
        $parser->setDescription(
            'Bake a controller skeleton.',
        )->addArgument('name', [
            'help' => 'Name of the controller to bake (without the `Controller` suffix). ' .
                'You can use Plugin.name to bake controllers into plugins.',
        ])->addOption('components', [
            'help' => 'The comma separated list of components to use.',
        ])->addOption('helpers', [
            'help' => 'The comma separated list of helpers to use.',
        ])->addOption('prefix', [
            'help' => 'The namespace/routing prefix to use.',
        ])->addOption('actions', [
            'help' => 'The comma separated list of actions to generate. ' .
                      'You can include custom methods provided by your template set here.',
        ])->addOption('base', [
            'boolean' => true,
            'help' => 'Generate AppPage class.',
        ])->addOption('no-test', [
            'boolean' => true,
            'help' => 'Do not generate a test skeleton.',
        ])->addOption('no-actions', [
            'boolean' => true,
            'help' => 'Do not generate basic CRUD action methods.',
        ]);

        return $parser;
    }

    /**
     * Get imports for a page class
     *
     * @param string $namespace The namespace
     * @param string $controllerName The controller name
     * @param string $prefix The prefix
     * @param string $entityClassName The entity class name
     * @param string $actionClass The action class name
     * @param string|null $plugin The plugin name
     * @param string $baseNamespace The base namespace
     * @param bool $entityExists Whether the entity class exists
     * @return array<string, string>
     */
    protected function getPageImports(
        string $namespace,
        string $controllerName,
        string $prefix,
        string $entityClassName,
        string $actionClass,
        ?string $plugin,
        string $baseNamespace,
        bool $entityExists,
    ): array {
        $viewModelEntityName = $entityExists ? $entityClassName : 'Entity';
        $viewModelClass = sprintf(
            '%s\ViewModel\%s%s\%s%s',
            $namespace,
            $controllerName,
            $prefix,
            $viewModelEntityName,
            $actionClass,
        );
        $viewModelShort = $viewModelEntityName . $actionClass;

        $appControllerClass = $plugin || $prefix
            ? sprintf('%s\Controller\AppController', $baseNamespace)
            : sprintf('%s\Controller\AppController', $namespace);

        return [
            'AppController' => $appControllerClass,
            'PageTrait' => 'CakePages\Page\PageTrait',
            $viewModelShort => $viewModelClass,
        ];
    }
}
