<?php
namespace ERD\SearchBundle\Tests\DependencyInjection;
use ERD\SearchBundle\DependencyInjection\ERDSearchExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of DependencyInjection
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 17, 2012 Ethan Resnick Design
 */
class ERDSearchExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $extension;
    
    /** @var ContainerBuilder */
    protected $container;
    
    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new ERDSearchExtension();
    }
    
    public function testContainerHasExpectedBasicServices()
    {
        $expectedBasicServices = array('erd_search.aggregate_entity_provider', 'erd_search.aggregate_synonym_provider');
        $this->extension->load(array(array('use_doctrine_events'=>false)), $this->container);

        $this->assertTrue(array_intersect($expectedBasicServices, $this->container->getServiceIds())===$expectedBasicServices);
    }
    
    public function testUseDoctrineOptionRegistersListenerService()
    {
        $this->extension->load(array(array('use_doctrine_events'=>true)), $this->container);
        $this->assertTrue(in_array("erd_search.doctrine_subscriber", $this->container->getServiceIds()));
    }

    public function testUseDoctrineListenerServiceTaggedCorrectly()
    {
        $this->extension->load(array(array('use_doctrine_events'=>true)), $this->container);
        $this->assertTrue($this->container->getDefinition("erd_search.doctrine_subscriber")->hasTag("doctrine.event_subscriber"));
    }
    
    public function testListenerServiceNotRegisteredWithoutUseDoctrineOption()
    {
        $this->extension->load(array(array('use_doctrine_events'=>false)), $this->container);
        $this->assertFalse(in_array("erd_search.doctrine_subscriber", $this->container->getServiceIds()));
    }
    
    
    public function testUseDoctrineEntitiesOptionRegistersProviderService()
    {
        $this->extension->load(array(array('use_doctrine_entities'=>true)), $this->container);
        $this->assertTrue(in_array("erd_search.doctrine_provider", $this->container->getServiceIds()));
    }

    public function testUseDoctrineEntitiesProviderServiceTaggedCorrectly()
    {
        $this->extension->load(array(array('use_doctrine_entities'=>true)), $this->container);
        $this->assertTrue($this->container->getDefinition("erd_search.doctrine_provider")->hasTag("erd_search.entity_provider"));
    }
    
    public function testProviderServiceNotRegisteredWithoutUseDoctrineEntitiesOption()
    {
        $this->extension->load(array(array('use_doctrine_entities'=>false)), $this->container);
        $this->assertFalse(in_array("erd_search.doctrine_provider", $this->container->getServiceIds()));
    }
}