<?php
namespace ERD\SearchBundle\Tests\Stubs;
use ERD\SearchBundle\Mapping\Annotation as Annotation;

/**
 * Stub with annotations to test the 
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 18, 2012 Ethan Resnick Design
 * 
 * @Annotation\Indexable 
 * @Annotation\Field() 
 */
class AnnotatedEntityStub
{
    /**
     * @Annotation\Field("keyword")
     */
    private $boostDefaultTypeKeyword;
    
    /**
     * @Annotation\Field("unstored")
     */
    protected $nameDefaultTypeUnstored;
    
    /**
     * @Annotation\Field("unindexed", boost=2) 
     */
    protected $boostTwoTypeUnindexed;
    
    /**
     * @Annotation\Field("binary") 
     */
    protected $typeBinary;

    /**
     * @Annotation\Field("text") 
     */
    protected $typeText = array("Text part one", "Text part two");
    
    /**
     * @Annotation\Field("key")
     */
    protected $typeKey = 'key';
    
    //for our tests
    public function setNested()
    {
        $nested = new AnnotatedEntityStub();
        $nested->typeText = array_merge($this->typeText, array("in nested!"));

        $this->boostDefaultTypeKeyword = array($nested, new \stdClass());
    }
}
?>