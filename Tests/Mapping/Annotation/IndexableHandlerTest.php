<?php
namespace ERD\SearchBundle\Tests\Mapping\Annotation;

use ERD\SearchBundle\Mapping\Annotation\IndexableHandler;
use Doctrine\Common\Annotations\AnnotationReader;
use ERD\SearchBundle\Search\FieldFactory;
use EWZ\Bundle\SearchBundle\Lucene\Document;
use ERD\SearchBundle\Mapping\Annotation as Annotation;

/**
 * Description of FieldHandlerTest
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 21, 2012 Ethan Resnick Design
 */
class IndexableHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Document
     */
    protected $document;

    /**
     * @var FieldHandler
     */
    protected $loader;

    protected static $factory;
    
    public static function setUpBeforeClass()
    {
        self::$factory = new FieldFactory();
    }

    public function setUp()
    {
        $this->document = new Document();
        $this->loader   = new IndexableHandler(new AnnotationReader(), self::$factory);
    }
    
    public function testValidEntityReturnsExpectedDocument()
    {
        $validEntity = new \ERD\SearchBundle\Tests\Stubs\AnnotatedEntityStub();
        $validEntity->setNested();
        $this->loader->loadFieldData($validEntity, $this->document);
        
        $expectedFields = array( //field name => field data (as property=>value)
            'boostDefaultTypeKeyword'=> array('type' =>'Keyword', 'value'=>'Text part one, Text part two, in nested!, stdClass'),
            'nameDefaultTypeUnstored'=> array('type' => 'UnStored'),
            'boostTwoTypeUnindexed'  => array('type' => 'UnIndexed', 'boost'=>2),
            'typeBinary'             => array('type' => 'Binary'), 
            'typeText'               => array('type' => 'Text', 'value'=>'Text part one, Text part two'), 
            'key'                    => array('type' => 'Key')
        );
        
        foreach($expectedFields as $fieldName=>$data)
        {
            try { $field = $this->document->getField($fieldName); }
            catch(\Exception $e) { $this->fail('The expected field must, but does not, exist in the document.'); }
            
            foreach($data as $k=>$v)
            {
                $this->assertEquals(($k=='type') ? $field->getType() : $field->$k, $v);
            }
        }
    }

    /**
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     */
    public function testNonIndexableThrowsExceptionForFieldData()
    {
        $nonIndexable = new \ERD\SearchBundle\Tests\Stubs\AnnotatedEntityMissingIndexableAnnotationStub();
        $this->loader->loadFieldData($nonIndexable, $this->document);
    }

    public function invalidEntityProvider()
    {
        $validFieldTypes = array('keyword', 'unindexed','binary','text','unstored', 'key');

        return array(
          array(new AnnotatedEntityDuplicateName(), "Only one field with the name name1 can exist on this object."),
          array(new AnnotatedEntityMissingType(), "Invalid field type '' type must be one of ".implode(', ', $validFieldTypes)),
          array(new AnnotatedEntityInvalidType(), "Invalid field type 'searchable' type must be one of ".implode(', ', $validFieldTypes)),
          array(new AnnotatedEntityNameTypeConflict(), "Only a field of type 'key' can be named 'key'."),
          array(new \ERD\SearchBundle\Tests\Stubs\AnnotatedEntityMissingIndexableAnnotationStub(), "Object isn't indexable; must have an Indexable annotation to be used with search.")
        );
    }
    
    /**
     * @dataProvider invalidEntityProvider
     */
    public function testInvalidEntitiesThrowExceptions($object, $expectedExceptionMsg)
    {
        try
        { 
            $this->loader->loadFieldData($object, $this->document); 
            $this->fail('Code should throw exception but doesn\'t.'); 
        }

        catch(\InvalidArgumentException $e) 
        {
            if(!$e->getMessage()==$expectedExceptionMsg) 
            {
                $this->fail("Expected different exception message."); 
            } 
            return;
        }
        
        catch(\Doctrine\Common\Annotations\AnnotationException $e)
        {
            if(!$e->getMessage()==$expectedExceptionMsg) 
            {
                $this->fail("Expected different exception message."); 
            } 
            return;
        }
        
        $this->fail("Exception was expected to be either an InvalidArgumentException or AnnatotaionException.");
    }
}

//prevents duplicate names, requires key field, requires type (except on key field)
/**
 * @Annotation\Indexable 
 */
class AnnotatedEntityMissingType
{
    /**
     * @Annotation\Field(name="bob")
     */
    protected $typeMissingRandomName;    
}

/**
 * @Annotation\Indexable 
 */
class AnnotatedEntityInvalidType
{
    /**
     * @Annotation\Field("searchable") 
     */
    protected $typeInvalid;
}

/**
 * @Annotation\Indexable 
 */
class AnnotatedEntityNameTypeConflict
{
    /**
     * @Annotation\Field("text", name="key") 
     */
    protected $keyNamedNotKey;    
}

/**
 * @Annotation\Indexable 
 */
class AnnotatedEntityDuplicateName
{
    /**
     * @Annotation\Field("text", name="name1")
     */
    protected $nameOne;
 
    /**
     * @Annotation\Field("text", name="name1")
     */   
    protected $duplicateName;
}