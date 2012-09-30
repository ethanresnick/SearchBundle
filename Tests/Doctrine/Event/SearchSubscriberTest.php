<?php
namespace ERD\SearchBundle\Tests\Doctrine\Event;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 17, 2012 Ethan Resnick Design
 */
class SearchSubscriberTest extends WebTestCase
{
    protected static $kernel;
    
    private static $container;

    private static $em;

    private static $subscriberClass;
    
    public static function setUpBeforeClass()
    {
        static::$kernel = static::createKernel(); //make a test kernel
        static::$kernel->boot();
        static::$container = static::$kernel->getContainer();
        static::$em = static::$container->get('doctrine.orm.entity_manager');
        static::$subscriberClass = static::$container->getParameter('erd_search.doctrine_subscriber.class');
    }

    public static function tearDownAfterClass()
    {
        static::$em->getConnection()->close();
        static::$em = null;
        static::$container = null;
        static::$kernel->shutdown();
    }


    /**
     * Since we're distributing this bundle to have it mixed in with other people's code, 
     * and they may try to run our tests with god knows what setup, it's too risky to actually
     * create new entity classes or objects or actually do persistence. So we just do a check
     * that doctrine agrees things are registered.
     */
    public function eventsProvider()
    {   
        return array(array(Events::onFlush, '\Doctrine\ORM\Event\OnFlushEventArgs'));
    }

    /**
     * @dataProvider eventsProvider
     */
    public function testSubscriberListensToProperEvents($event, $argsClass)
    {
        //we can't get the service itself (that's private), so we check for a listener of its class.
        foreach(static::$em->getEventManager()->getListeners($event) as $listener)
        {
            if($listener instanceof static::$subscriberClass)
            {
                return true;
            }
        }

        $this->fail('No listener of the proper class is registered for the '.$event.' event.');        
    }
    
    /**
     * @dataProvider eventsProvider 
     */
    public function testSearchApplierRunsOnProperEvents($event, $argsClass)
    {   
        $stubCollection = array(new \stdClass());

        $applierMock = $this->getMockBuilder('\ERD\SearchBundle\Search\IndexManager')->disableOriginalConstructor()->getMock();
        $applierMock->expects($this->once())->method('update');
       
        //below, law of demeter violation forces me to pollute the test body with these fixtures
        $unitOfWorkMock = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')->disableOriginalConstructor()->getMock();
        $unitOfWorkMock->expects($this->once())->method('getScheduledEntityInsertions')->will($this->returnValue($stubCollection));
        $unitOfWorkMock->expects($this->once())->method('getScheduledEntityUpdates')->will($this->returnValue($stubCollection));
        $unitOfWorkMock->expects($this->once())->method('getScheduledEntityDeletions')->will($this->returnValue($stubCollection));
        
        $emMock = $this->getMockBuilder('\Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $emMock->expects($this->any())->method('getUnitOfWork')->will($this->returnValue($unitOfWorkMock));
        
        $eventArgsStub = $this->getMockBuilder($argsClass)->disableOriginalConstructor()->getMock();
        $eventArgsStub->expects($this->any())->method('getEntityManager')->will($this->returnValue($emMock));

        $subscriber = new static::$subscriberClass($applierMock); 
        
        //dispatch the event manually, just to this subscriber, to see if our mocks/stubs work.
        $subscriber->$event($eventArgsStub);
    }
}