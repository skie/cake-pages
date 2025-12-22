<?php
declare(strict_types=1);

namespace CakePages\Test\TestCase\Command;

use Cake\Console\CommandInterface;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;

/**
 * BakeViewModelCommand Test
 */
class BakeViewModelCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;
    use StringCompareTrait;

    /**
     * Generated file path
     *
     * @var string
     */
    protected string $generatedFile = '';

    /**
     * Generated files paths
     *
     * @var array<string>
     */
    protected array $generatedFiles = [];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setAppNamespace('TestApp');
        $this->configApplication('TestApp\Application', [CONFIG]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        if ($this->generatedFile && file_exists($this->generatedFile)) {
            unlink($this->generatedFile);
            $this->generatedFile = '';
        }

        if (count($this->generatedFiles)) {
            foreach ($this->generatedFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            $this->generatedFiles = [];
        }
    }

    /**
     * Test main listing available models
     *
     * @return void
     */
    public function testMainListAvailable(): void
    {
        $this->exec('bake viewmodel --connection test');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
    }

    /**
     * Test baking a viewmodel with default actions
     *
     * @return void
     */
    public function testBakeViewModelWithDefaultActions(): void
    {
        $this->generatedFiles = [
            APP . 'ViewModel/Posts/PostIndex.php',
            APP . 'ViewModel/Posts/PostView.php',
            APP . 'ViewModel/Posts/PostAdd.php',
            APP . 'ViewModel/Posts/PostEdit.php',
            APP . 'ViewModel/Posts/PostDelete.php',
        ];
        $this->exec('bake viewmodel Posts --connection test');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains('class PostIndex', $this->generatedFiles[0]);
        $this->assertFileContains('extends BaseViewModel', $this->generatedFiles[0]);
        $this->assertFileContains('implements ViewModelInterface', $this->generatedFiles[0]);
    }

    /**
     * Test baking a viewmodel with specific actions
     *
     * @return void
     */
    public function testBakeViewModelWithSpecificActions(): void
    {
        $this->generatedFile = APP . 'ViewModel/Posts/PostIndex.php';
        $this->exec('bake viewmodel Posts --connection test --actions index');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('class PostIndex', $this->generatedFile);
        $this->assertFileContains('extends BaseViewModel', $this->generatedFile);
        $this->assertFileContains('implements ViewModelInterface', $this->generatedFile);
    }

    /**
     * Test baking a viewmodel with no actions
     *
     * @return void
     */
    public function testBakeViewModelWithNoActions(): void
    {
        $this->exec('bake viewmodel Posts --connection test --no-actions');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
    }

    /**
     * Assert that a list of files exist
     *
     * @param array<string> $files The list of files to check
     * @param string $message The message to use if a check fails
     * @return void
     */
    protected function assertFilesExist(array $files, string $message = ''): void
    {
        foreach ($files as $file) {
            $this->assertFileExists($file, $message);
        }
    }

    /**
     * Assert that a file contains a substring
     *
     * @param string $expected The expected content
     * @param string $path The path to check
     * @param string $message The error message
     * @return void
     */
    protected function assertFileContains(string $expected, string $path, string $message = ''): void
    {
        $this->assertFileExists($path, 'Cannot test contents, file does not exist.');

        $contents = file_get_contents($path);
        $this->assertStringContainsString($expected, $contents, $message);
    }
}

