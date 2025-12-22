<?php
declare(strict_types=1);

namespace TestPlugin\Controller\TestPlugin;

use CakePages\Page\PageTrait;
use TestPlugin\Controller\TestPluginAppController;

class ViewPage extends TestPluginAppController
{
    use PageTrait;

    public function onGet(): void
    {
        $this->response = $this->response->withStringBody('TestPlugin ViewPage');
    }
}
