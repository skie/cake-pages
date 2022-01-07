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
use Bake\Utility\TableScanner;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use CakePages\Utility\TemplateRenderer;

/**
 * Task class for creating and updating page files.
 */
class BakeViewModelCommand extends BakeCommand
{
    /**
     * Path fragment for generated code.
     *
     * @var string
     */
    public $pathFragment = 'ViewModel/';

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

        if (empty($name)) {
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

        $prefix = $this->getPrefix($args);
        if ($prefix) {
            $prefix = '\\' . str_replace('/', '\\', $prefix);
        }

        $namespace = Configure::read('App.namespace');
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
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
        $entityClass = sprintf('%s\Model\Entity\%s', $namespace, $entityClass);
        if (!class_exists($entityClass)) {
            $entityClass = EntityInterface::class;
        }

        $data = compact(
            // 'actions',
            'currentModelName',
            'defaultModel',
            'entityClassName',
            'entityClass',
            'modelObj',
            'namespace',
            'plugin',
            'pluralHumanName',
            'pluralName',
            'prefix',
            'singularHumanName',
            'singularName'
        );
        foreach ($actions as $action) {
            $data['actionClass'] = strtoupper(substr($action, 0, 1)) . substr($action, 1);
            $data['name'] = $data['entityClassName'] . $data['actionClass'];
            $data['action'] = $action;

            $this->bakeViewModels($controllerName, $data, $args, $io);
            // $this->bakeTest($controllerName, $args, $io);
        }
    }

    /**
     * Generate the page code
     *
     * @param string $controllerName The name of the controller.
     * @param array $data The data to turn into code.
     * @param \Cake\Console\Arguments $args The console args
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return void
     */
    public function bakeViewModels(string $controllerName, array $data, Arguments $args, ConsoleIo $io): void
    {
        $data += [
            'name' => null,
            'controllerName' => $controllerName,
            'namespace' => null,
            'prefix' => null,
            'actionClass' => null,
            'plugin' => null,
            'pluginPath' => null,
        ];

        $renderer = new TemplateRenderer($this->theme);
        $renderer->set($data);

        $contents = $renderer->generate('CakePages.ViewModel/viewmodel');

        $path = $this->getPath($args);
        $filename = $path . $controllerName . DS . $data['name'] . '.php';
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
        $test = new TestCommand();
        $testArgs = new Arguments(
            ['controller', $className],
            $args->getOptions(),
            ['type', 'name']
        );
        $test->execute($testArgs, $io);
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
            'Bake a controller skeleton.'
        )->addArgument('name', [
            'help' => 'Name of the controller to bake (without the `Controller` suffix). ' .
                'You can use Plugin.name to bake controllers into plugins.',
        ])->addOption('prefix', [
            'help' => 'The namespace/routing prefix to use.',
        ])->addOption('actions', [
            'help' => 'The comma separated list of actions to generate. ' .
                      'You can include custom methods provided by your template set here.',
        ])->addOption('no-test', [
            'boolean' => true,
            'help' => 'Do not generate a test skeleton.',
        ])->addOption('no-actions', [
            'boolean' => true,
            'help' => 'Do not generate basic CRUD action methods.',
        ]);

        return $parser;
    }
}
