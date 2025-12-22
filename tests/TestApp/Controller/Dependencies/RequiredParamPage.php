<?php
declare(strict_types=1);

namespace TestApp\Controller\Dependencies;

use CakePages\Page\PageTrait;
use TestApp\Controller\AppController;

class RequiredParamPage extends AppController
{
    use PageTrait;

    public function onGet($one): void
    {
        $this->autoRender = false;
        $this->response = $this->response->withStringBody(json_encode(compact('one')));
    }
}
