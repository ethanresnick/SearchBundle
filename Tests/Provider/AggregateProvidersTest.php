<?php
namespace ERD\SearchBundle\Tests\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Description of AggregateProvidersTest
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 23, 2012 Ethan Resnick Design
 */
class AggregateProvidersTest extends \PHPUnit_Framework_TestCase
{
    public function aggregateProvidersInfoProvider()
    {
        return array(
            array('ERD\SearchBundle\Provider\AggregateEntityProvider', 'ERD\SearchBundle\Provider\EntityProviderInterface', array('getAllEntities')), 
            array('ERD\SearchBundle\Provider\AggregateSynonymProvider', 'ERD\SearchBundle\Provider\SynonymProviderInterface', array('getSynonyms'))
        );
    }

    /**
     * @dataProvider aggregateProvidersInfoProvider
     */
    public function testConstructorRejectsInvalidProviders($providerClass, $interface, $methods)
    {
        $mock = $this->getMock($interface);

        try { new $providerClass(array($mock)); }
        catch(\InvalidArgumentException $e) { $this->fail('No Exception should be thrown trying to use a legit service'); }
        
        try { new $providerClass(array(new \stdClass())); }
        catch(\InvalidArgumentException $e) {  return; }
        
        $this->fail('Trying to use an invalid provider should have raised an exception but didn\'t.');
    }

    /**
     * @dataProvider aggregateProvidersInfoProvider
     */    
    public function testClassImplementsProviderInterface($providerClass, $interface, $methods)
    {
        $this->assertContains($interface, class_implements($providerClass));
    }
    
    /**
     * @dataProvider aggregateProvidersInfoProvider
     */
    public function testMethodsCallProvidersProperly($providerClass, $interface, $methods)
    {
        $mock = $this->getMock($interface);

        $test = new $providerClass(array($mock)); 
        
        foreach($methods as $method)
        {
            $mock->expects($this->once())->method($method)->will($this->returnValue(array()));
            $test->$method();
        }    
    }

    /**
     * @dataProvider aggregateProvidersInfoProvider
     */    
    public function testMethodsMergeResultsCorrectly($providerClass, $interface, $methods)
    {   
        $mock = $this->getMock($interface);
        $test = new $providerClass(array($mock, $mock));
        
        foreach($methods as $method)
        {
            $mock->expects($this->exactly(2))->method($method)->will($this->onConsecutiveCalls(array('glob_1'=>1), array('glob_2'=>2)));    
            $this->assertEquals($test->$method(), array('glob_1'=>1, 'glob_2'=>2));
        }    
    }
}