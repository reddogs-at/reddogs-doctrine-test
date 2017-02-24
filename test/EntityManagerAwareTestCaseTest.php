<?php
namespace ReddogsTest\Doctrine\Test;

use Reddogs\Doctrine\Test\EntityManagerAwareTestCase;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Mapping\ClassMetadata;

class EntityManagerAwareTestCaseTest extends EntityManagerAwareTestCase
{
    private $container;

    protected function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->setContainer($this->container);
    }

    public function testGetEntityManager()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo(EntityManager::class))
            ->will($this->returnValue($entityManager));
        $this->assertSame($entityManager, $this->getEntityManager());
    }

    public function testTruncateEntities()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo(EntityManager::class))
            ->will($this->returnValue($entityManager));

        $connection = $this->createMock(Connection::class);
        $entityManager->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $platform = $this->createMock(AbstractPlatform::class);
        $connection->expects($this->once())
            ->method('getDatabasePlatform')
            ->will($this->returnValue($platform));

        $metadata1 = $this->createMock(ClassMetadata::class);
        $metadata2 = $this->createMock(ClassMetadata::class);

        $entityManager->expects($this->at(1))
            ->method('getClassMetadata')
            ->with($this->equalTo('TestEntity1'))
            ->will($this->returnValue($metadata1));

        $entityManager->expects($this->at(2))
            ->method('getClassMetadata')
            ->with($this->equalTo('TestEntity2'))
            ->will($this->returnValue($metadata2));

        $metadata1->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('table1'));

        $metadata2->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('table2'));

        $platform->expects($this->at(0))
            ->method('getTruncateTableSQL')
            ->with($this->equalTo('table1'))
            ->will($this->returnValue('TRUNCATE table1'));
        $platform->expects($this->at(1))
            ->method('getTruncateTableSQL')
            ->with($this->equalTo('table2'))
            ->will($this->returnValue('TRUNCATE table2'));

        $expectedQuery = 'TRUNCATE table1;TRUNCATE table2;';
        $connection->expects($this->once())
            ->method('executeQuery')
            ->with($this->equalTo($expectedQuery));

        $this->truncateEntities([
            'TestEntity1',
            'TestEntity2'
        ]);
    }

    public function testTruncateTables()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo(EntityManager::class))
            ->will($this->returnValue($entityManager));

        $connection = $this->createMock(Connection::class);
        $entityManager->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $platform = $this->createMock(AbstractPlatform::class);
        $connection->expects($this->once())
            ->method('getDatabasePlatform')
            ->will($this->returnValue($platform));

        $platform->expects($this->at(0))
            ->method('getTruncateTableSQL')
            ->with($this->equalTo('table1'))
            ->will($this->returnValue('TRUNCATE table1'));
        $platform->expects($this->at(1))
            ->method('getTruncateTableSQL')
            ->with($this->equalTo('table2'))
            ->will($this->returnValue('TRUNCATE table2'));

        $expectedQuery = 'TRUNCATE table1;TRUNCATE table2;';
        $connection->expects($this->once())
            ->method('executeQuery')
            ->with($this->equalTo($expectedQuery));
        $this->truncateTables(['table1', 'table2']);
    }
}