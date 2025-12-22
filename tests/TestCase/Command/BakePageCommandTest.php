<?php
declare(strict_types=1);

namespace CakePages\Test\TestCase\Command;

use Cake\Console\CommandInterface;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;

/**
 * BakePageCommand Test
 */
class BakePageCommandTest extends TestCase
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
        $this->exec('bake page');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
    }

    /**
     * Test baking a page with default actions
     *
     * @return void
     */
    public function testBakePageWithDefaultActions(): void
    {
        $this->generatedFiles = [
            APP . 'Controller/Posts/IndexPage.php',
            APP . 'Controller/Posts/ViewPage.php',
            APP . 'Controller/Posts/AddPage.php',
            APP . 'Controller/Posts/EditPage.php',
            APP . 'Controller/Posts/DeletePage.php',
        ];
        $this->exec('bake page Posts --connection test --no-test');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);
        $this->assertFileContains('class IndexPage', $this->generatedFiles[0]);
        $this->assertFileContains('class ViewPage', $this->generatedFiles[1]);
    }

    /**
     * Test baking a page with specific actions
     *
     * @return void
     */
    public function testBakePageWithSpecificActions(): void
    {
        $this->generatedFile = APP . 'Controller/Posts/IndexPage.php';
        $this->exec('bake page Posts --connection test --no-test --actions index');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('class IndexPage', $this->generatedFile);
    }

    /**
     * Test baking a page with no actions
     *
     * @return void
     */
    public function testBakePageWithNoActions(): void
    {
        $this->exec('bake page Posts --connection test --no-test --no-actions');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
    }

    /**
     * Test baking AppPage base class
     *
     * @return void
     */
    public function testBakeBase(): void
    {
        $this->generatedFile = APP . 'Controller/AppPage.php';
        $this->exec('bake page --base --connection test');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFileExists($this->generatedFile);
        $this->assertFileContains('class AppPage', $this->generatedFile);
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

