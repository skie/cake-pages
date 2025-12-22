<?php
declare(strict_types=1);

namespace TestApp\Controller\Users;

use CakePages\Page\PageTrait;
use TestApp\Controller\AppController;
use TestApp\Service\UsersService;

class SsoCallbackPage extends AppController
{
    use PageTrait;

    public function onPost(UsersService $users): void
    {
        $this->autoRender = false;
        $data = $this->request->getData();
        $user = $users->ensureExists($data);
        $this->response = $this->response->withStringBody(json_encode($user));
    }
}
