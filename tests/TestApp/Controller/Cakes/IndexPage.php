<?php
declare(strict_types=1);

namespace TestApp\Controller\Cakes;

use CakePages\Page\PageTrait;
use TestApp\Controller\AppController;

class IndexPage extends AppController
{
    use PageTrait;

    public function onGet(): void
    {
        $this->response = $this->response->withStringBody('Hello Jane');
    }
}
