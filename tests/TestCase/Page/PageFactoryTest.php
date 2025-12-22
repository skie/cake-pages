<?php
declare(strict_types=1);

namespace CakePages\Test\TestCase\Page;

use Cake\Controller\Exception\InvalidParameterException;
use Cake\Core\Container;
use Cake\Http\ControllerFactoryInterface;
use Cake\Http\Exception\MissingControllerException;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use CakePages\Page\PageFactory;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
use TestApp\Controller\Admin\Posts\ViewPage as AdminPostsViewPage;
use TestApp\Controller\Cakes\ViewPage as CakesViewPage;
use TestApp\Controller\Dependencies\ContainerDepPage;
use TestApp\Service\UsersService;
use TestPlugin\Controller\TestPlugin\ViewPage as TestPluginViewPage;

class PageFactoryTest extends TestCase
{
    protected PageFactory $factory;

    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->factory = new PageFactory($this->container);
    }

    public function testApplicationPage(): void
    {
        $request = new ServerRequest([
            'url' => 'cakes/view',
            'params' => [
                'controller' => 'Cakes',
                'action' => 'view',
            ],
        ]);
        $result = $this->factory->create($request);
        $this->assertInstanceOf(CakesViewPage::class, $result);
        $this->assertSame($request, $result->getRequest());
    }

    public function testPrefixedApplicationPage(): void
    {
        $request = new ServerRequest([
            'url' => 'admin/posts/view',
            'params' => [
                'prefix' => 'Admin',
                'controller' => 'Posts',
                'action' => 'view',
            ],
        ]);
        $result = $this->factory->create($request);
        $this->assertInstanceOf(AdminPostsViewPage::class, $result);
        $this->assertSame($request, $result->getRequest());
    }

    public function testPluginPage(): void
    {
        $request = new ServerRequest([
            'url' => 'test_plugin/test_plugin/view',
            'params' => [
                'plugin' => 'TestPlugin',
                'controller' => 'TestPlugin',
                'action' => 'view',
            ],
        ]);
        $result = $this->factory->create($request);
        $this->assertInstanceOf(TestPluginViewPage::class, $result);
        $this->assertSame($request, $result->getRequest());
    }

    public function testMissingControllerException(): void
    {
        $this->expectException(MissingControllerException::class);
        $request = new ServerRequest([
            'url' => 'nonexistent/view',
            'params' => [
                'controller' => 'Nonexistent',
                'action' => 'view',
            ],
        ]);
        $this->factory->create($request);
    }

    public function testSlashedControllerFailure(): void
    {
        $this->expectException(MissingControllerException::class);
        $request = new ServerRequest([
            'url' => 'admin/posts/view',
            'params' => [
                'controller' => 'Admin/Posts',
                'action' => 'view',
            ],
        ]);
        $this->factory->create($request);
    }

    public function testAbsoluteReferenceFailure(): void
    {
        $this->expectException(MissingControllerException::class);
        $request = new ServerRequest([
            'url' => 'test/view',
            'params' => [
                'controller' => 'TestApp\Controller\Cakes\ViewPage',
                'action' => 'view',
            ],
        ]);
        $this->factory->create($request);
    }

    public function testRequestHandlerInterface(): void
    {
        $this->assertInstanceOf(
            RequestHandlerInterface::class,
            $this->factory,
        );
    }

    public function testControllerFactoryInterface(): void
    {
        $this->assertInstanceOf(
            ControllerFactoryInterface::class,
            $this->factory,
        );
    }

    public function testCreateWithContainerDependenciesNoPage(): void
    {
        $this->container->add(stdClass::class, json_decode('{"key":"value"}'));

        $request = new ServerRequest([
            'url' => 'dependencies/containerDep',
            'params' => [
                'plugin' => null,
                'controller' => 'Dependencies',
                'action' => 'containerDep',
            ],
        ]);
        $page = $this->factory->create($request);
        $this->assertInstanceOf(ContainerDepPage::class, $page);
        $this->assertNull($page->inject);
    }

    public function testCreateWithContainerDependenciesWithPage(): void
    {
        $request = new ServerRequest([
            'url' => 'dependencies/containerDep',
            'params' => [
                'plugin' => null,
                'controller' => 'Dependencies',
                'action' => 'containerDep',
            ],
        ]);
        $this->container->add(stdClass::class, json_decode('{"key":"value"}'));
        $this->container->add(ServerRequest::class, $request);
        $this->container->add(ContainerDepPage::class)
            ->addArgument(ServerRequest::class)
            ->addArgument(null)
            ->addArgument(null)
            ->addArgument(stdClass::class);

        $page = $this->factory->create($request);
        $this->assertInstanceOf(ContainerDepPage::class, $page);
        $this->assertSame($page->inject, $this->container->get(stdClass::class));
    }

    public function testInvokeInjectOptionalParameterDefined(): void
    {
        $this->container->add(stdClass::class, json_decode('{"key":"value"}'));
        $request = new ServerRequest([
            'url' => 'dependencies/optionalDep',
            'params' => [
                'plugin' => null,
                'controller' => 'Dependencies',
                'action' => 'optionalDep',
            ],
        ]);
        $page = $this->factory->create($request);
        $result = $this->factory->invoke($page);
        $data = json_decode((string)$result->getBody());

        $this->assertNotNull($data);
        $this->assertNull($data->any);
        $this->assertNull($data->str);
        $this->assertSame('value', $data->dep->key);
    }

    public function testInvokeInjectParametersRequiredNotDefined(): void
    {
        $request = new ServerRequest([
            'url' => 'dependencies/requiredDep',
            'params' => [
                'plugin' => null,
                'controller' => 'Dependencies',
                'action' => 'requiredDep',
            ],
        ]);
        $page = $this->factory->create($request);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage(
            'Failed to inject dependency from service container for parameter `dep` with type `stdClass` in action `Dependencies::requiredDep()`',
        );
        $this->factory->invoke($page);
    }

    public function testInvokePassedParametersCoercion(): void
    {
        $request = new ServerRequest([
            'url' => 'dependencies/requiredTyped',
            'params' => [
                'plugin' => null,
                'controller' => 'Dependencies',
                'action' => 'requiredTyped',
                'pass' => ['1.0', '2', '0', '8,9'],
            ],
        ]);
        $page = $this->factory->create($request);

        $result = $this->factory->invoke($page);
        $data = json_decode((string)$result->getBody(), true);
        $this->assertSame(['one' => 1.0, 'two' => 2, 'three' => false, 'four' => ['8', '9']], $data);
    }

    public function testInvokeInjectServiceIntoActionMethod(): void
    {
        $this->container->add(UsersService::class);
        $request = new ServerRequest([
            'url' => 'users/ssoCallback',
            'params' => [
                'plugin' => null,
                'controller' => 'Users',
                'action' => 'ssoCallback',
            ],
        ]);
        $request = $request->withMethod('POST')
            ->withParsedBody(['email' => 'user@example.com']);

        $page = $this->factory->create($request);
        $result = $this->factory->invoke($page);
        $data = json_decode((string)$result->getBody(), true);

        $this->assertNotNull($data);
        $this->assertSame(123, $data['id']);
        $this->assertSame('user@example.com', $data['email']);
    }
}
