<?php
namespace ERD\SearchBundle\Mapping\Annotation;
use ERD\AnnotationHelpers\PowerReaderInterface;
use Doctrine\Common\Annotations\AnnotationException;
use EWZ\Bundle\SearchBundle\Lucene\Document;
use ERD\SearchBundle\Mapping\Annotation\Field as FieldAnnotation;
use ERD\SearchBundle\Search\FieldFactory;

/**
 * Reads the search field annotations on an object and fills in data not specified in the annotation.
 * 
 * That data is read and accessible through getFields()
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jun 19, 2012 Ethan Resnick Design
 */
class IndexableHandler
{
 
    protected $fieldAnnotationClass    = 'ERD\\SearchBundle\\Mapping\\Annotation\\Field';
    protected $documentAnnotationClass = 'ERD\\SearchBundle\\Mapping\\Annotation\\Indexable';
    private $reader;
    private $fieldFactory;

    public function __construct(PowerReaderInterface $reader, FieldFactory $fieldFactory)
    {
        $this->reader = $reader;
        $this->fieldFactory = $fieldFactory;
    }
    
    /**
     * Reads the objects contents and its annotations to build a Document object for Lucene.
     *
     * @param Object $object The object whose ERD\SearchBundle\Mapping\Annotation\Field annotations to read.
     * @return Document The Lucene document representing it otherwise.
     * @throws \InvalidArgumentException If no Field annotation with the type "key" exists (b/c lucene identifies documents by their "key" field)
     * @throws \InvalidArgumentException If multiple fields with the same name exist on the object
     * @throws \InvalidArgumentException If the entity isn't indexable (i.e. doesn't have an Indexable annotation)
     * 
     */
    public function loadFieldData($object, Document $document)
    {
        if(!$this->reader->hasClassAnnotation(new \ReflectionClass($object), $this->documentAnnotationClass, true))
        {
            throw new \InvalidArgumentException("Object isn't indexable; must have an Indexable annotation to be used with search.");
        }

        $reflectionObject = new \ReflectionObject($object);
        $properties = $reflectionObject->getProperties();

        
        $fieldNames = array();
        foreach ($properties as $reflectionProp) 
        {
            //only looking at the property in the class where it was defined, read any annotations from
            //its docblock or class level-annotations referring to the property (to support traits).
            $annotations = $this->reader->getPropertyAnnotationsFromClass($reflectionProp, $reflectionProp->getDeclaringClass(), $this->fieldAnnotationClass);

            //handle the annotation if it's been found.
            if ((null !== $annotations) && count($annotations)>0)
            {
                //we don't support merging annotations yet, so we just use the annotation with the highest precedence
                $annotation = $annotations[0];

                $reflectionProp->setAccessible(true);
                $field = $this->buildField($object, $annotation, $reflectionProp);

                if(in_array($field->name, $fieldNames))
                {
                    throw new \InvalidArgumentException("Only one field with the name ".$field->name.' can exist on this object.');
                }
                
                $document->addField($field);
                $fieldNames[] = $field->name;
            }

        }
        
        if(!in_array('key', $fieldNames))
        {
            throw new \InvalidArgumentException('The object must have an annotation designating a "key" field for the document.');
        }

        //clean up
        unset($reflectionObject);
        unset($properties);
        unset($fieldNames);
        
        return $document;
    }
    
    /**
     * @param Object $object Object whose data to use for the field value
     * @param FieldAnnotation $annotation Annotation whose data to use for the field properties
     * @param \ReflectionProperty $reflectionProp Whose name to use for the field's default name
     * @return Field The field definition
     * @throws AnnotationException When the field type is invalid.
     * @throws AnnotationException When a field isn't a "key" type but is named "key".
     */
    protected function buildField($object, FieldAnnotation $annotation, \ReflectionProperty $reflectionProp)
    {
        $reflectionProp->setAccessible(true);

        $fieldName = ($annotation->name) ? $annotation->name : $reflectionProp->getName();
        
        $fieldValue = $reflectionProp->isStatic() ? $reflectionProp->getValue() : $reflectionProp->getValue($object);
        $fieldValue = $this->buildFieldValue($fieldValue);

        $fieldType = strtolower($annotation->type);
        $validFieldTypes = $this->fieldFactory->getValidFieldTypes();
        foreach($validFieldTypes as &$type) { $type = strtolower($type); }

        //types (except key_ copied from the zend field class which, of course, has no interface or getFieldTypes method
        if(!in_array($fieldType, $validFieldTypes)) 
        { 
            throw new AnnotationException("Invalid field type '".$fieldType."'; type must be one of ".implode(', ', $validFieldTypes));     
        }
        
        //handle type=key fields separately, as it's setter doesn't give you the option to specify a name (it's always key).
        if($fieldType=='key')
        { 
            if(empty($fieldValue)) { throw new AnnotationException("The Key field cannot be empty!"); }
            $fieldValue .= get_class($object); //help ensure that the user's keys are unique system-wide.
            return $this->fieldFactory->key($fieldValue); 
        }

        //remove html from fields whose values won't ever be shown directly so it doesn't pollute the index
        if ($fieldType == 'unstored') { $fieldValue = strip_tags($fieldValue); }

        //build preliminary field object
        $field = $this->fieldFactory->$fieldType($fieldName, $fieldValue);
        
        //add boost if necessary
        if ($annotation->boost !== null) { $field->boost = $annotation->boost; }

        return $field;
    }
    
    /**
     * Takes the property's value,which could be an array, object, etc, and turns it into a string for Lucene.
     * 
     * @param mixed $rawValue The property's raw value, which could be any type
     * 
     * @todo Maybe introduce an annotation or callback mechanism for procesing fields whose values are objects.
     */
    protected function buildFieldValue($rawValue)
    {
        if ($rawValue instanceof \DateTime) { return $rawValue->getTimestamp(); }
        
        else if (is_array($rawValue) || $rawValue instanceof \Traversable)
        {
            $newValue = array();
            
            foreach($rawValue as $value)
            {
                $newValue[] = $this->buildFieldValue($value);
            }

            $newValue = implode(', ', $newValue);

            return $newValue;
        }
        
        else if(is_object($rawValue))
        {
            $reflectionObject = new \ReflectionObject($rawValue);
            $ownFields = array(); //does this object have its own search fields (will be an array of them or empty)     
            
            //note that an object can have its own search fields below without having a 
            //@Search\Indexable() annotation. E.g. objects that are only embedded in other 
            //objects but don't have urls of their own to point to from the reults.
            foreach ($reflectionObject->getProperties() as $reflectionProp)
            {
                $annotation = $this->reader->getPropertyAnnotation($reflectionProp, $this->fieldAnnotationClass);
                
                if (null !== $annotation)
                {
                   $ownFields[] = $this->buildField($rawValue, $annotation, $reflectionProp);
                }
            }
            
            if(count($ownFields))
            {
                $newValue = '';
                foreach($ownFields as $field) { $newValue .= ($field->isStored && $field->getType()!='Key') ? $field->value : ''; }
                
                return $newValue;
            }

            else
            {
                try { $newValue = (string) $rawValue; }
                catch(\Exception $e) { $newValue = get_class($rawValue); }

                return $newValue;
            }
        }

        else //boolean, string, or whatever
        {
            return (string) $rawValue;
        }
    }
}