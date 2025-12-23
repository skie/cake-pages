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
        $this->assertFileContains('extends ViewModel', $this->generatedFiles[0]);

        $indexContent = file_get_contents($this->generatedFiles[0]);
        $this->assertStringContainsString('declare(strict_types=1);', $indexContent, 'File should have strict types');
        $this->assertStringContainsString('use Cake\Collection\CollectionInterface;', $indexContent, 'File should have CollectionInterface import');
        $this->assertStringContainsString('use TestApp\Model\Entity\Post;', $indexContent, 'File should have Post entity import');
        $this->assertStringContainsString('public ?CollectionInterface $posts = null;', $indexContent, 'File should have nullable typed property');
        $this->assertStringContainsString('@var', $indexContent, 'File should have docblock');
        $this->assertStringContainsString('CollectionInterface<', $indexContent, 'File should have generic in docblock');

        $viewContent = file_get_contents($this->generatedFiles[1]);
        $this->assertStringContainsString('public ?Post $post = null;', $viewContent, 'View should have nullable typed property');
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
        $this->assertFileContains('extends ViewModel', $this->generatedFile);
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
     * Test baking a viewmodel when entity class does not exist (Orders)
     *
     * @return void
     */
    public function testBakeViewModelWithNonExistentEntity(): void
    {
        $this->generatedFiles = [
            APP . 'ViewModel/Orders/EntityIndex.php',
            APP . 'ViewModel/Orders/EntityView.php',
        ];
        $this->exec('bake viewmodel Orders --connection test --actions index,view');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $indexContent = file_get_contents($this->generatedFiles[0]);
        $this->assertStringContainsString('use Cake\ORM\Entity;', $indexContent, 'File should use Entity when entity class does not exist');
        $this->assertStringContainsString('public ?CollectionInterface $orders = null;', $indexContent, 'File should have nullable CollectionInterface typed property');
        $this->assertStringContainsString('@var \\Cake\\ORM\\Entity[]', $indexContent, 'File should have Entity[] in docblock');
        $this->assertStringNotContainsString('use TestApp\Model\Entity\Order;', $indexContent, 'File should not import non-existent Order entity');

        $viewContent = file_get_contents($this->generatedFiles[1]);
        $this->assertStringContainsString('use Cake\ORM\Entity;', $viewContent, 'View should use Entity when entity class does not exist');
        $this->assertStringContainsString('public ?Entity $order = null;', $viewContent, 'View should have nullable Entity typed property');
        $this->assertStringContainsString('@var \\Cake\\ORM\\Entity', $viewContent, 'View should have Entity in docblock');
    }

    /**
     * Test baking a viewmodel with associations (Posts with BelongsTo Users, HasMany Comments, BelongsToMany Tags)
     *
     * @return void
     */
    public function testBakeViewModelWithAssociations(): void
    {
        $this->generatedFiles = [
            APP . 'ViewModel/Posts/PostAdd.php',
            APP . 'ViewModel/Posts/PostEdit.php',
        ];
        $this->exec('bake viewmodel Posts --connection test --actions add,edit');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $addContent = file_get_contents($this->generatedFiles[0]);
        $this->assertStringContainsString('use Cake\Collection\CollectionInterface;', $addContent, 'Add should import CollectionInterface for associations');
        $this->assertStringContainsString('public ?CollectionInterface $users = null;', $addContent, 'Add should have nullable typed users collection');
        $this->assertStringContainsString('public ?CollectionInterface $tags = null;', $addContent, 'Add should have nullable typed tags collection');
        $this->assertStringContainsString('@var \\TestApp\\Model\\Entity\\User[]', $addContent, 'Add should have User[] in docblock');
        $this->assertStringContainsString('@var \\TestApp\\Model\\Entity\\Tag[]', $addContent, 'Add should have Tag[] in docblock');
        $this->assertStringNotContainsString('use TestApp\Model\Entity\User;', $addContent, 'Add should not import User entity (only in docblock)');
        $this->assertStringNotContainsString('use TestApp\Model\Entity\Tag;', $addContent, 'Add should not import Tag entity (only in docblock)');

        $editContent = file_get_contents($this->generatedFiles[1]);
        $this->assertStringContainsString('use Cake\Collection\CollectionInterface;', $editContent, 'Edit should import CollectionInterface for associations');
        $this->assertStringContainsString('public ?CollectionInterface $users = null;', $editContent, 'Edit should have nullable typed users collection');
        $this->assertStringContainsString('public ?CollectionInterface $tags = null;', $editContent, 'Edit should have nullable typed tags collection');
        $this->assertStringContainsString('@var \\TestApp\\Model\\Entity\\User[]', $editContent, 'Edit should have User[] in docblock');
        $this->assertStringContainsString('@var \\TestApp\\Model\\Entity\\Tag[]', $editContent, 'Edit should have Tag[] in docblock');
    }

    /**
     * Test baking a viewmodel with BelongsTo associations (Comments with BelongsTo Posts and Users)
     *
     * @return void
     */
    public function testBakeViewModelWithBelongsToAssociations(): void
    {
        $this->generatedFiles = [
            APP . 'ViewModel/Comments/CommentAdd.php',
            APP . 'ViewModel/Comments/CommentEdit.php',
        ];
        $this->exec('bake viewmodel Comments --connection test --actions add,edit');

        $this->assertExitCode(CommandInterface::CODE_SUCCESS);
        $this->assertFilesExist($this->generatedFiles);

        $addContent = file_get_contents($this->generatedFiles[0]);
        $this->assertStringContainsString('use Cake\Collection\CollectionInterface;', $addContent, 'Add should import CollectionInterface for associations');
        $this->assertStringContainsString('public ?CollectionInterface $posts = null;', $addContent, 'Add should have nullable typed posts collection');
        $this->assertStringContainsString('public ?CollectionInterface $users = null;', $addContent, 'Add should have nullable typed users collection');
        $this->assertStringContainsString('@var \\TestApp\\Model\\Entity\\Post[]', $addContent, 'Add should have Post[] in docblock');
        $this->assertStringContainsString('@var \\TestApp\\Model\\Entity\\User[]', $addContent, 'Add should have User[] in docblock');
        $this->assertStringNotContainsString('use TestApp\Model\Entity\Post;', $addContent, 'Add should not import Post entity (only in docblock)');
        $this->assertStringNotContainsString('use TestApp\Model\Entity\User;', $addContent, 'Add should not import User entity (only in docblock)');
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
