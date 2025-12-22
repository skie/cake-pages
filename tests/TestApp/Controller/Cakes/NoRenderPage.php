<?php
declare(strict_types=1);

namespace TestApp\Controller\Cakes;

use CakePages\Page\PageTrait;
use TestApp\Controller\AppController;

class NoRenderPage extends AppController
{
    use PageTrait;

    public function onGet(): void
    {
        $this->autoRender = false;
        $this->response = $this->response->withStringBody('autoRender false body');
    }
}
