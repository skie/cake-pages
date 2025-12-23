<?php
declare(strict_types=1);

namespace CakePages\View\Helper;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\View\Helper;
use Exception;
use function Cake\Core\namespaceSplit;

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
    public function getOjectFqn(object $object): ?string
    {
        return get_class($object);
    }

    /**
     * Get entity class FQN from table class FQN
     *
     * @param string $tableClass Table class FQN (e.g., TestApp\Model\Table\UsersTable)
     * @return string Entity class FQN (e.g., TestApp\Model\Entity\User)
     */
    public function getEntityClassFromTable(string $tableClass): string
    {
        if (strpos($tableClass, '\\Model\\Table\\') === false) {
            return Entity::class;
        }

        $parts = explode('\\', $tableClass);
        $tableName = array_pop($parts);
        $entityName = substr($tableName, 0, -5);
        $namespace = implode('\\', array_slice($parts, 0, -2));

        return sprintf('%s\Model\Entity\%s', $namespace, $entityName);
    }

    /**
     * Get entity class FQN from table object
     *
     * @param \Cake\ORM\Table $table Table object
     * @return string Entity class FQN
     */
    public function getEntityClassFromTableObject(Table $table): string
    {
        try {
            $entityClass = $table->getEntityClass();
            if (class_exists($entityClass)) {
                return $entityClass;
            }
        } catch (Exception $e) {
        }

        $tableClass = get_class($table);

        return $this->getEntityClassFromTable($tableClass);
    }

    /**
     * Check if entity class exists
     *
     * @param string $entityClass Entity class FQN
     * @return bool
     */
    public function entityClassExists(string $entityClass): bool
    {
        return class_exists($entityClass);
    }

    /**
     * Get entity short name from FQN
     *
     * @param string $entityClass Entity class FQN
     * @return string Short class name
     */
    public function getEntityShortName(string $entityClass): string
    {
        [, $shortName] = namespaceSplit($entityClass);

        return $shortName;
    }
}
