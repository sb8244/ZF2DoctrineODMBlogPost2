<?php

namespace DemoTest\Hook;

use Mockery;
use Demo\Entity\Entry;
use Demo\Mapper\EntryMapper;
/**
 * Tests that a lead is added to a correct profile (or that profile created)
 * when a lead is saved into the system
 * 
 * @author Steve
 *
 * Run with:
 *  phpunit -c module/Demo/test/ --group hook
 */
class HookTest extends \PHPUnit_Framework_TestCase
{
    protected $mockDocumentManager;
    protected $mockRepository;
    protected $mockMapper;
    protected $sm;
    
    public function tearDown()
    {
        Mockery::close();
    }
    
    public function setUp()
    {
        $this->mockDocumentManager = Mockery::mock(
            'Doctrine\ODM\MongoDB\DocumentManager'
        );
        $this->mockRepository = Mockery::mock(
            'Doctrine\ODM\MongoDB\DocumentRepository'
        );
        $this->mockMapper = Mockery::mock(
            'Demo\Mapper\EntryMapper'
        );
        $this->sm = \DemoTest\Bootstrap::getServiceManager()->setAllowOverride(true);
        $this->sm->setService('DemoEntryMapper', $this->mockMapper);
    }
    
    /**
     * Hook needs to fire off when this is a new entry
     * @group hook
     */
    public function testHookHappensNew()
    {
        $entry = $this->getValidEntry();
        
        //Does not contain, so this is new
        $this->mockDocumentManager->shouldReceive('contains')->times(1)
            ->with($entry)->andReturn(false);
        $this->mockDocumentManager->shouldReceive('persist')->times(1)->with($entry);
        $this->mockDocumentManager->shouldReceive('flush')->times(1);
        
        $this->mockMapper->shouldReceive('save')->times(1)->with($entry);
        
        $entryMapper = new EntryMapper($this->mockDocumentManager, $this->mockRepository);
        $entryMapper->save($entry);
    }
   
    /**
     * Hook doesn't fire off when this isn't new, so no mocked dependency
     * @group hook
     */
    public function testHookHappensNotNew()
    {
        $entry = $this->getValidEntry();
        
        //Does contain, so this is not new
        $this->mockDocumentManager->shouldReceive('contains')->times(1)
        ->with($entry)->andReturn(true);
        $this->mockDocumentManager->shouldReceive('persist')->times(1)->with($entry);
        $this->mockDocumentManager->shouldReceive('flush')->times(1);
        
        $entryMapper = new EntryMapper($this->mockDocumentManager, $this->mockRepository);
        $entryMapper->save($entry);
    }
    
    private function getValidEntry()
    {
        $entry = new Entry();
        $entry->text = "Test";
        return $entry;
    }
}