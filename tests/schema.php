<?php
declare(strict_types=1);

/**
 * Test database schema for BlazeCast plugin tests.
 *
 * This format resembles the existing fixture schema
 * and is converted to SQL via the Schema generation
 * features of the Database package.
 */
return [
    [
        'table' => 'users',
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'username' => [
                'type' => 'string',
                'length' => 50,
                'null' => false,
            ],
            'email' => [
                'type' => 'string',
                'length' => 100,
                'null' => false,
            ],
            'password' => [
                'type' => 'string',
                'length' => 255,
                'null' => false,
            ],
            'full_name' => [
                'type' => 'string',
                'length' => 100,
                'null' => true,
            ],
            'active' => [
                'type' => 'boolean',
                'default' => true,
                'null' => false,
            ],
            'created' => [
                'type' => 'datetime',
                'null' => true,
            ],
            'modified' => [
                'type' => 'datetime',
                'null' => true,
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
            'users_username_unique' => [
                'type' => 'unique',
                'columns' => [
                    'username',
                ],
            ],
            'users_email_unique' => [
                'type' => 'unique',
                'columns' => [
                    'email',
                ],
            ],
        ],
    ],
    [
        'table' => 'posts',
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'title' => [
                'type' => 'string',
                'length' => 255,
                'null' => false,
            ],
            'body' => [
                'type' => 'text',
                'null' => true,
            ],
            'user_id' => [
                'type' => 'integer',
                'null' => true,
            ],
            'created' => [
                'type' => 'datetime',
                'null' => true,
            ],
            'modified' => [
                'type' => 'datetime',
                'null' => true,
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
        ],
    ],
    [
        'table' => 'comments',
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'post_id' => [
                'type' => 'integer',
                'null' => false,
            ],
            'user_id' => [
                'type' => 'integer',
                'null' => true,
            ],
            'body' => [
                'type' => 'text',
                'null' => false,
            ],
            'approved' => [
                'type' => 'boolean',
                'default' => false,
                'null' => false,
            ],
            'created' => [
                'type' => 'datetime',
                'null' => true,
            ],
            'modified' => [
                'type' => 'datetime',
                'null' => true,
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
            'comments_post_id_fk' => [
                'type' => 'foreign',
                'columns' => ['post_id'],
                'references' => ['posts', 'id'],
                'update' => 'cascade',
                'delete' => 'cascade',
            ],
            'comments_user_id_fk' => [
                'type' => 'foreign',
                'columns' => ['user_id'],
                'references' => ['users', 'id'],
                'update' => 'cascade',
                'delete' => 'setNull',
            ],
        ],
    ],
    [
        'table' => 'tags',
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'name' => [
                'type' => 'string',
                'length' => 50,
                'null' => false,
            ],
            'slug' => [
                'type' => 'string',
                'length' => 50,
                'null' => false,
            ],
            'created' => [
                'type' => 'datetime',
                'null' => true,
            ],
            'modified' => [
                'type' => 'datetime',
                'null' => true,
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
            'tags_slug_unique' => [
                'type' => 'unique',
                'columns' => [
                    'slug',
                ],
            ],
        ],
    ],
    [
        'table' => 'posts_tags',
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'post_id' => [
                'type' => 'integer',
                'null' => false,
            ],
            'tag_id' => [
                'type' => 'integer',
                'null' => false,
            ],
            'created' => [
                'type' => 'datetime',
                'null' => true,
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
            'posts_tags_post_id_fk' => [
                'type' => 'foreign',
                'columns' => ['post_id'],
                'references' => ['posts', 'id'],
                'update' => 'cascade',
                'delete' => 'cascade',
            ],
            'posts_tags_tag_id_fk' => [
                'type' => 'foreign',
                'columns' => ['tag_id'],
                'references' => ['tags', 'id'],
                'update' => 'cascade',
                'delete' => 'cascade',
            ],
            'posts_tags_unique' => [
                'type' => 'unique',
                'columns' => [
                    'post_id',
                    'tag_id',
                ],
            ],
        ],
    ],
    [
        'table' => 'orders',
        'columns' => [
            'id' => [
                'type' => 'integer',
                'autoIncrement' => true,
            ],
            'order_number' => [
                'type' => 'string',
                'length' => 50,
                'null' => false,
            ],
            'total' => [
                'type' => 'decimal',
                'precision' => 10,
                'scale' => 2,
                'null' => false,
            ],
            'status' => [
                'type' => 'string',
                'length' => 20,
                'null' => false,
            ],
            'created' => [
                'type' => 'datetime',
                'null' => true,
            ],
            'modified' => [
                'type' => 'datetime',
                'null' => true,
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ],
            'orders_order_number_unique' => [
                'type' => 'unique',
                'columns' => [
                    'order_number',
                ],
            ],
        ],
    ],
];
