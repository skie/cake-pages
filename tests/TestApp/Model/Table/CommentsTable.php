<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class CommentsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('comments');
        $this->setDisplayField('body');
        $this->setPrimaryKey('id');

        $this->belongsTo('Posts', [
            'className' => 'Posts',
            'foreignKey' => 'post_id',
        ]);

        $this->belongsTo('Users', [
            'className' => 'Users',
            'foreignKey' => 'user_id',
        ]);
    }
}
