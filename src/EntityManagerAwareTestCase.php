<?php
/**
 * Reddogs (https://github.com/reddogs-at)
 *
 * @see https://github.com/reddogs-at/reddogs-doctrine-test for the canonical source repository
 * @license https://github.com/reddogs-at/reddogs-doctrine-test/blob/master/LICENSE MIT License
 */
declare(strict_types = 1);
namespace Reddogs\Doctrine\Test;

use Doctrine\ORM\EntityManager;
use Reddogs\Test\ServiceManagerAwareTestCase;

abstract class EntityManagerAwareTestCase extends ServiceManagerAwareTestCase
{

    /**
     * Get entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->getContainer()->get(EntityManager::class);
    }

    /**
     * Truncate entities
     *
     * @param array $entityClassnames
     */
    protected function truncateEntities(array $entityClassnames)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $sql = '';
        foreach ($entityClassnames as $entityClassname) {
            $metadata = $em->getClassMetadata($entityClassname);
            $tablename = $metadata->getTableName();
            $sql .= $platform->getTruncateTableSQL($tablename) . ';';
        }
        $connection->executeQuery($sql);
    }

    /**
     * Truncate tables
     *
     * @param array $tableNames
     */
    protected function truncateTables(array $tableNames)
    {
        $connection = $this->getEntityManager()->getConnection();
        $platform = $connection->getDatabasePlatform();

        $sql = '';
        foreach ($tableNames as $tablename) {
            $sql .= $platform->getTruncateTableSQL($tablename) . ';';
        }
        $connection->executeQuery($sql);
    }
}