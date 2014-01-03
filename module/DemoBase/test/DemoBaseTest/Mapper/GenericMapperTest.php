<?php 
namespace DemoBaseTest\Mapper;

use PHPUnit_Framework_TestCase;
use Mockery;
use DemoBase\Mapper\GenericMapper;

/**
 * Mock DocumentManager and Repository for the most part, except for real life tests
 * for things like Unicode support
 * 
 * @author Steve
 *
 */
class GenericMapperTest extends PHPUnit_Framework_TestCase
{
	protected $mockDocumentManager;
	protected $mockRepository;
	
	public function tearDown()
	{
		Mockery::close();
	}
	
	public function setup()
	{
		$this->mockDocumentManager = Mockery::mock(
			'Doctrine\ODM\MongoDB\DocumentManager'
		);
		$this->mockRepository = Mockery::mock(
			'Doctrine\ODM\MongoDB\DocumentRepository'		
		);
	}
	
	public function testSave()
	{
		$lead = new \stdClass();
		
		$this->mockDocumentManager->shouldReceive('contains')->times(1)->andReturn(true);
		$this->mockDocumentManager->shouldReceive('persist')->times(1)->with($lead);
		$this->mockDocumentManager->shouldReceive('flush')->times(1);

		$mapper = new GenericMapper($this->mockDocumentManager, $this->mockRepository);
		$mapper->save($lead);
	}
	
	/**
	 * Fix a bug where utf8 entities were being saved in the db
	 * This will catch that it is fixed by trying to save a non-ut8 entity
	 * and it is converted to utf-8
	 */
	public function testSaveEncoding()
	{
	    $lead = new \stdClass();
	    $lead->value = utf8_decode("ó");
	    $lead->deep = new \stdClass();
	    $lead->deep->value = utf8_decode("ó");
	    
	    $this->assertFalse(mb_detect_encoding($lead->value, "UTF-8", true));
	    $this->assertFalse(mb_detect_encoding($lead->deep->value, "UTF-8", true));

	    $this->mockDocumentManager->shouldReceive('contains')->times(1)->andReturn(true);
	    $this->mockDocumentManager->shouldReceive('persist')->times(1)->with($lead);
	    $this->mockDocumentManager->shouldReceive('flush')->times(1);
	
	    $mapper = new GenericMapper($this->mockDocumentManager, $this->mockRepository);
	    $mapper->save($lead);
	    
	    $this->assertEquals('UTF-8', mb_detect_encoding($lead->value, "UTF-8", true));
	    $this->assertEquals('UTF-8', mb_detect_encoding($lead->deep->value, "UTF-8", true));
	}
	
	public function testSaveEvent()
	{
		$lead = new \stdClass();

		$this->mockDocumentManager->shouldReceive('contains')->times(1)->andReturn(true);
		$this->mockDocumentManager->shouldReceive('persist')->times(1)->with($lead);
		$this->mockDocumentManager->shouldReceive('flush')->times(1);
		
		$mapper = new GenericMapper($this->mockDocumentManager, $this->mockRepository);
		
		$savePreFired = false;
		$mapper->getEventManager()->attach('stdClass::save.pre', function($e) use (&$savePreFired) {
			$savePreFired = $e->getParams();
		});
		$savePostFired = false;
		$mapper->getEventManager()->attach('stdClass::save.post', function($e) use (&$savePostFired) {
			$savePostFired = $e->getParams();
		});
		
		$mapper->save($lead);
		$this->assertFalse($savePreFired['new']);
		$this->assertFalse($savePostFired['new']);
		$this->assertEquals($lead, $savePreFired['entity'], 'WfxApiLead save.pre event not fired');
		$this->assertEquals($lead, $savePostFired['entity'], 'WfxApiLead save.post event not fired');
	}
	
	public function testSaveNewEvent()
	{
	    $lead = new \stdClass();

	    $this->mockDocumentManager->shouldReceive('contains')->times(1)->andReturn(false);
	    $this->mockDocumentManager->shouldReceive('persist')->times(1)->with($lead);
	    $this->mockDocumentManager->shouldReceive('flush')->times(1);
	    
	    $mapper = new GenericMapper($this->mockDocumentManager, $this->mockRepository);
	    
	    $savePreFired = false;
	    $mapper->getEventManager()->attach('stdClass::save.pre', function($e) use (&$savePreFired) {
	        $savePreFired = $e->getParams();
	    });
	    $savePostFired = false;
	    $mapper->getEventManager()->attach('stdClass::save.post', function($e) use (&$savePostFired) {
	        $savePostFired = $e->getParams();
	    });
	    
        $mapper->save($lead, true);
        $this->assertTrue($savePreFired['new']);
        $this->assertTrue($savePostFired['new']);
        $this->assertEquals($lead, $savePreFired['entity'], 'WfxApiLead save.pre event not fired');
        $this->assertEquals($lead, $savePostFired['entity'], 'WfxApiLead save.post event not fired');
	}
	
	public function testRemove()
	{
		$lead = new \stdClass();
		
		$this->mockDocumentManager->shouldReceive('remove')->times(1)->with($lead);
		$this->mockDocumentManager->shouldReceive('flush')->times(1);
	
		$mapper = new GenericMapper($this->mockDocumentManager, $this->mockRepository);
		$mapper->remove($lead);
	}
	
	public function testRemoveEvent()
	{
		$lead = new \stdClass();
		
		$this->mockDocumentManager->shouldReceive('remove')->times(1)->with($lead);
		$this->mockDocumentManager->shouldReceive('flush')->times(1);
		
		$mapper = new GenericMapper($this->mockDocumentManager, $this->mockRepository);
		
		$preFired = false;
		$mapper->getEventManager()->attach('stdClass::remove.pre', function($e) use (&$preFired) {
			$preFired = $e->getParams();
		});
		$postFired = false;
		$mapper->getEventManager()->attach('stdClass::remove.post', function($e) use (&$postFired) {
			$postFired = $e->getParams();
		});
		
		$mapper->remove($lead);
		
		$this->assertEquals($lead, $preFired['entity'], 'WfxApiLead remove.pre event not fired');
		$this->assertEquals($lead, $postFired['entity'], 'WfxApiLead remove.post event not fired');
	}
	
	public function testFind()
	{
		$lead = new \stdClass();
		$id = 'ASD12';
		
		$this->mockRepository->shouldReceive('find')->times(1)->with($id)->andReturn($lead);
	
		$mapper = new GenericMapper($this->mockDocumentManager, $this->mockRepository);
		$res = $mapper->find($id);
	}	
	
	public function testFindAll()
	{
		$this->mockRepository->shouldReceive('findBy')->with([], []);
	
		$mapper = new GenericMapper($this->mockDocumentManager, $this->mockRepository);
		$res = $mapper->findAll();
	}
}