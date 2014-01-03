<?php

namespace Demo\Entity;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use DemoBase\Entity\BaseEntity;

/**
 * Basic Entity which just contains publicly accessible variables.
 * Validation will be covered in future tutorials
 * 
 * @ODM\Document(collection="entry")
 * @author Steve
 */
class Entry extends BaseEntity
{
    /**
     * @ODM\Id
     */
    public $_id;
    
    /**
     * @ODM\Field(type="string")
     */
    public $text;
    
    /**
     * @ODM\PrePersist @ODM\PreUpdate
     */
    public function validate()
    {
        if(empty($this->text))
            throw new \Exception(__CLASS__ . "->text is empty");
        
        return true;
    }
}