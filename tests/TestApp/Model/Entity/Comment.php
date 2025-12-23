<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

class Comment extends Entity
{
    protected array $_accessible = [
        'post_id' => true,
        'user_id' => true,
        'body' => true,
        'approved' => true,
        'created' => true,
        'modified' => true,
    ];

    protected array $_hidden = [];

    protected array $_virtual = [];
}
