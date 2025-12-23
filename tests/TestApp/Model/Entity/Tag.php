<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

class Tag extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'slug' => true,
        'created' => true,
        'modified' => true,
    ];

    protected array $_hidden = [];

    protected array $_virtual = [];
}
