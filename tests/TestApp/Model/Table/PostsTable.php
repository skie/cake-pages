<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class PostsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('posts');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'className' => 'Users',
            'foreignKey' => 'user_id',
        ]);

        $this->hasMany('Comments', [
            'className' => 'Comments',
            'foreignKey' => 'post_id',
        ]);

        $this->belongsToMany('Tags', [
            'className' => 'Tags',
            'joinTable' => 'posts_tags',
            'foreignKey' => 'post_id',
            'targetForeignKey' => 'tag_id',
        ]);
    }
}
