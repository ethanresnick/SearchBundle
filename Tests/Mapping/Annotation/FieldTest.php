<?php
namespace ERD\SearchBundle\Tests\Mapping\Annotation;

use ERD\SearchBundle\Tests\Stubs\AnnotatedEntityStub;
use ERD\SearchBundle\Mapping\Annotation\Field;

/**
 * Description of FIELD
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 20, 2012 Ethan Resnick Design
 */
class FieldTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Doctrine\Common\Annotations\AnnotationReader */
    protected $reader;

    /** @var \ReflectionClass */
    protected $mockReflectionEntity;
    
    /** @var array */
    protected $annotationDefaults;
    
    public function setUp()
    {
        $unconfiguredAnnotation = new Field(array());
        $this->annotationDefaults = array('name'=>$unconfiguredAnnotation->name, 'boost'=>$unconfiguredAnnotation->boost);
        $this->reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $this->mockReflectionEntity = new \ReflectionClass(get_class(new AnnotatedEntityStub()));
    }

    public function testAnnotationOnlyAllowedOnProperties()
    {
        //we want to try to load annotations on the class and methods and make sure we get
        //exceptions, but we can't do that with Doctrine Common 2.1.x since the TARGET feature
        //wasn't implemented til 2.2, which we're not using yet.
        $this->markTestIncomplete();
    }

    public function annotatedPropertiesProvider()
    {
        //can't get access to the setUp() stuff here, so remake the object.
        $unconfiguredAnnotation = new Field(array());
    
        return array(
            array('typeText', 'type', 'text'), 
            array('typeBinary', 'type', 'binary'), 
            array('boostDefaultTypeKeyword', 'type', 'keyword'),
            array('nameDefaultTypeUnstored', 'type', 'unstored'),
            array('boostTwoTypeUnindexed', 'type', 'unindexed'),
            array('boostTwoTypeUnindexed', 'boost', 2),
            array('boostDefaultTypeKeyword', 'boost', $unconfiguredAnnotation->boost),
            array('typeKey', 'type', 'key')
        );
    }

    /**
     * @dataProvider annotatedPropertiesProvider
     */
    public function testPropertiesHonored($propertyName, $annotationProp, $value)
    {
        $property = $this->mockReflectionEntity->getProperty($propertyName);
        $annotation = $this->reader->getPropertyAnnotation($property, 'ERD\SearchBundle\Mapping\Annotation\Field');
        $this->assertTrue(($annotation->$annotationProp===$value)); 
    }
}