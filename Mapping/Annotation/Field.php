<?php
namespace ERD\SearchBundle\Mapping\Annotation;

/**
 * Description of SearchAnnotation
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 19, 2012 Ethan Resnick Design
 * 
 * @Annotation
 * @Target({"PROPERTY","CLASS"})
 */
class Field
{
    public $type;
    public $name = null;
    public $boost = null;

    /**
     * @var string Name of the property this annotation is for. Only applicable when this is used as a class
     * annotation to annotate a property that can't be annotated directly because, e.g., it's from a trait.
     */
    public $for;

    public function __construct(array $data)
    {
        if(isset($data['value'])) { $this->type  = $data['value']; }

        foreach(array('name','boost', 'for') as $allowedKey)
        {
            if(isset($data[$allowedKey])) { $this->{$allowedKey} = $data[$allowedKey]; }
        }
    }
}