<?php
declare(strict_types=1);

namespace CakePages\View\Helper;

use Cake\ORM\Table;
use Cake\View\Helper;

/**
 * Pages bake helper
 */
class PageBakeHelper extends Helper
{

    /**
     * Get associated table insance.
     *
     * @param \Cake\ORM\Table $modelObj Model object.
     * @param string $assoc Association name.
     * @return \Cake\ORM\Table
     */
    public function getAssociatedTable(Table $modelObj, string $assoc): Table
    {
        $association = $modelObj->getAssociation($assoc);

        return $association->getTarget();
    }

    /**
     * Get object fqn.
     *
     * @param object $object An object.
     * @return ?string
     */
    public function getOjectFqn($object): ?string
    {
        if (!is_object($object)) {
            return null;
        }

        return get_class($object);
    }
}
