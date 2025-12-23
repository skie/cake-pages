<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

class User extends Entity
{
    protected array $_accessible = [
        'username' => true,
        'email' => true,
        'password' => true,
        'full_name' => true,
        'active' => true,
        'created' => true,
        'modified' => true,
    ];

    protected array $_hidden = [
        'password',
    ];

    protected array $_virtual = [];
}
