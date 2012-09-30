<?php
namespace ERD\SearchBundle\Provider;

/**
 * Description of EntitiesProvider
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 16, 2012 Ethan Resnick Design
 */
interface EntityProviderInterface
{
    /**
     * @return An array of objects with annotations required to convert them to documents for lucene.
     */
    public function getAllEntities();
    
    //in the future could add getNewEntitiesSince(\DateTime), getRemovedEntitiesSince(\DateTime), getUpdatedEntitiesSince(\DateTime),
    //but those could be hard to support in some cases since often there's no record of the removed entities after they're deleted.
}