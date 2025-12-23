<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

class Post extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'body' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
    ];

    protected array $_hidden = [];

    protected array $_virtual = [];
}
