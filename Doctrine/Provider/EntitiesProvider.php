<?php
namespace ERD\SearchBundle\Doctrine\Provider;
use ERD\SearchBundle\Provider\EntityProviderInterface;
use ERD\DoctrineHelpersBundle\Provider as DoctrineProvider;

/**
 * Will be constructed to target the Indexable annotation.
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 25, 2012 Ethan Resnick Design
 */
class EntitiesProvider extends DoctrineProvider\AnnotatedEntitiesProvider implements EntityProviderInterface
{
    public function getAllEntities()
    {
        return parent::getAllEntities(); //fix stupid php interface handling
    }
}