<?php
namespace ERD\SearchBundle\Tests\DependencyInjection\Compiler;

use ERD\SearchBundle\DependencyInjection\ERDSearchExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ERD\SearchBundle\DependencyInjection\Compiler\LoadProvidersCompilerPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;


/**
 * Description of LoadProvidersCompilerPassTest
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright May 25, 2012 Ethan Resnick Design
 */
class LoadProvidersCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    protected $testContainer;
    
    protected $pass;

    
    public function setUp()
    {
        $this->testContainer = new ContainerBuilder();
     
        $extension = new ERDSearchExtension();
        $extension->load(array(array('use_doctrine_entities'=>false)), $this->testContainer);
        
        $this->testContainer->setDefinition('erd_search.lucene', new Definition());
        
        $this->pass = new LoadProvidersCompilerPass();
    }
    
    public function serviceSettings()
    {
        return array(
            array('synonym_provider', 'aggregate_synonym_provider'),
            array('entity_provider', 'aggregate_entity_provider')
        );
    }

    /**
     * @dataProvider serviceSettings
     */
    public function testTaggedServicesAdded($tagName, $extensionName)
    {
        //create a dummy tagged service
        $testTaggedService = new Definition('stdClass');
        $testTaggedService->addTag('erd_search.'.$tagName);
        
        //add the service definition, run the compile pass.
        $this->testContainer->setDefinition('erd_search.test_service', $testTaggedService);
        $this->pass->process($this->testContainer); //not $testContainer->compile() b/c this is the only pass we want.
        
        //check that the service_id was added to the test_extension arguments
        $newDef = $this->testContainer->getDefinition('erd_search.'.$extensionName);
        
        $this->assertEquals(array(new Reference('erd_search.test_service')), $newDef->getArgument(0));    
    }
}