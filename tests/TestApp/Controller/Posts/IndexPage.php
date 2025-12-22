<?php
declare(strict_types=1);

namespace TestApp\Controller\Posts;

use CakePages\Page\PageTrait;
use TestApp\Controller\AppController;

class IndexPage extends AppController
{
    use PageTrait;

    public function onGet(): void
    {
        $this->set('posts', ['post1', 'post2']);
        $this->set('_serialize', ['posts']);
    }
}
