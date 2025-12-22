<?php
declare(strict_types=1);

namespace TestApp\Controller\Dependencies;

use CakePages\Page\PageTrait;
use TestApp\Controller\AppController;

class VariadicPage extends AppController
{
    use PageTrait;

    public function onGet(): void
    {
        $this->autoRender = false;
        $this->response = $this->response->withStringBody(json_encode(['args' => func_get_args()]));
    }
}
