<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class TagsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('tags');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsToMany('Posts', [
            'className' => 'Posts',
            'joinTable' => 'posts_tags',
            'foreignKey' => 'tag_id',
            'targetForeignKey' => 'post_id',
        ]);
    }
}
