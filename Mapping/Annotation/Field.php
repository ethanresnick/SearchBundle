<?php
namespace ERD\SearchBundle\Mapping\Annotation;

/**
 * Description of SearchAnnotation
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 19, 2012 Ethan Resnick Design
 * 
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Field
{
    public $type;
    public $name = null;
    public $boost = null;
    
    public function __construct(array $data)
    {
        if(isset($data['value'])) { $this->type  = $data['value']; }
        if(isset($data['name']))  { $this->name  = $data['name']; }
        if(isset($data['boost'])) { $this->boost = $data['boost']; }
    }
}