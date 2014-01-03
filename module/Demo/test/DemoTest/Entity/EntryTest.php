<?php

namespace DemoTest\Entity;

use Demo\Entity\Entry;

/**
 * Simple test checks for validation of the object
 * 
 * @author Steve
 *
 */
class EntryTest extends \PHPUnit_Framework_TestCase
{
    public function testValidates()
    {
        $entry = $this->getValidEntry();
        $entry->validate();
    }
    
    /**
     * @expectedException \Exception
     */
    public function testNoText()
    {
        $entry = $this->getValidEntry();
        $entry->text = null;
        $entry->validate();
    }
    
    private function getValidEntry()
    {
        $entry = new Entry();
        $entry->text = "test";
        return $entry;
    }
}