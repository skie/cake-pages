<?php
declare(strict_types=1);

namespace TestApp\Controller\Dependencies;

use Cake\Event\EventManagerInterface;
use Cake\Http\ServerRequest;
use CakePages\Page\PageTrait;
use stdClass;
use TestApp\Controller\AppController;

class ContainerDepPage extends AppController
{
    use PageTrait;

    public ?stdClass $inject;

    public function __construct(
        ?ServerRequest $request = null,
        ?string $name = null,
        ?EventManagerInterface $eventManager = null,
        ?stdClass $inject = null,
    ) {
        parent::__construct($request, $name, $eventManager);
        $this->inject = $inject;
    }

    public function onGet(): void
    {
        $this->autoRender = false;
        $data = ['inject' => $this->inject];
        $this->response = $this->response->withStringBody(json_encode($data));
    }
}
