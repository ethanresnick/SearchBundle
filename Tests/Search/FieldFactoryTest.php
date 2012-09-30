<?php
namespace ERD\SearchBundle\Tests\Search;

/**
 * Description of FieldFactoryTest
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 23, 2012 Ethan Resnick Design
 */
class FieldFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ERD\SearchBundle\Search\FieldFactory
     */
    private static $factory;
    
    public static function setUpBeforeClass()
    {
        static::$factory = new \ERD\SearchBundle\Search\FieldFactory();
    }
    
    public function testFactorySupportsAllTypesProperly()
    {
        foreach(static::$factory->getValidFieldTypes() as $type)
        {
            $this->assertTrue(method_exists(static::$factory, lcfirst($type)));
        }
    }
    
    public function testFactoryReturnsFieldOfProperClassAndType()
    {
        foreach(static::$factory->getValidFieldTypes() as $type)
        {
            $method = lcfirst($type);

            $field = ($method=='key') ? static::$factory->$method('dummyName') : static::$factory->$method('dummyName', 'dummyValue');
            $this->assertEquals($field->getType(), $type);
            $this->assertTrue($field instanceof \ERD\SearchBundle\Search\Field);
        }
    }
    

}