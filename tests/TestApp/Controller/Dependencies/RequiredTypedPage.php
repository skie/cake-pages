<?php
declare(strict_types=1);

namespace TestApp\Controller\Dependencies;

use CakePages\Page\PageTrait;
use TestApp\Controller\AppController;

class RequiredTypedPage extends AppController
{
    use PageTrait;

    /**
     * @param float $one
     * @param int $two
     * @param bool $three
     * @param array<int, mixed> $four
     * @return void
     */
    public function onGet(float $one, int $two, bool $three, array $four): void
    {
        $this->autoRender = false;
        $this->response = $this->response->withStringBody(json_encode(
            compact('one', 'two', 'three', 'four'),
            JSON_PRESERVE_ZERO_FRACTION,
        ));
    }
}
