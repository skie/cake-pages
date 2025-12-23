<?php
declare(strict_types=1);

namespace TestApp\Controller\Dependencies;

use CakePages\Page\PageTrait;
use stdClass;
use TestApp\Controller\AppController;

class OptionalDepPage extends AppController
{
    use PageTrait;

    public function onGet(mixed $any = null, ?string $str = null, ?stdClass $dep = null): void
    {
        $this->autoRender = false;
        $this->response = $this->response->withStringBody(json_encode(compact('dep', 'any', 'str')));
    }
}
