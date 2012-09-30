<?php
namespace ERD\SearchBundle\Doctrine\Event;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * Description of PHPTypographySubscriber
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 17, 2012 Ethan Resnick Design
 */
class SearchSubscriber implements \Doctrine\Common\EventSubscriber
{
    /**
     * @var ERD\SearchBundle\Search\IndexManager
     */
    protected $indexer;

    public function __construct($indexer)
    {
        $this->indexer = $indexer;
    }
    
    public function getSubscribedEvents()
    {
        return array(Events::onFlush);
    }
    
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        $this->indexer->update($uow->getScheduledEntityInsertions(), $uow->getScheduledEntityUpdates(), $uow->getScheduledEntityDeletions());

        /** I don't think the below are needed 
        foreach ($uow->getScheduledCollectionDeletions() AS $col) {}
        foreach ($uow->getScheduledCollectionUpdates() AS $col) {} */
    }  
}