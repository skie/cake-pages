<?php
declare(strict_types=1);

namespace TestApp\Controller\Cakes;

use CakePages\Page\PageTrait;
use TestApp\Controller\AppController;

class ViewPage extends AppController
{
    use PageTrait;

    public function onGet(int $id): void
    {
        $this->response = $this->response->withStringBody(json_encode(['id' => $id]));
    }
}
