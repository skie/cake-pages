<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');

        $this->hasMany('Posts', [
            'className' => 'Posts',
            'foreignKey' => 'user_id',
        ]);

        $this->hasMany('Comments', [
            'className' => 'Comments',
            'foreignKey' => 'user_id',
        ]);
    }
}
