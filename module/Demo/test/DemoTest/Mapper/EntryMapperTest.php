<?php 
namespace DemoTest\Mapper;

use PHPUnit_Framework_TestCase;
use Mockery;
use Demo\Mapper\EntryMapper;

/**
 * Basic test for the EntryMapper new functionality and Service Config
 * 
 * @author Steve
 *
 */
class EntryMapperTest extends PHPUnit_Framework_TestCase
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
	
	public function testFindByCustom()
	{
	    $custom = "Custom Param";
	    $param2 = "Another Param";
	    
		$this->mockRepository->shouldReceive('findBy')
		  ->with(['custom'=>$custom, 'params'=>$param2], ['text'=>'ASC']);
	
		$mapper = new EntryMapper($this->mockDocumentManager, $this->mockRepository);
		$res = $mapper->findByCustom($custom, $param2);
	}
	
	/**
	 * Make sure the service manager doesn't error and returns correct instance type
	 */
	public function testServiceManager()
	{
	    $sm = \DemoTest\Bootstrap::getServiceManager();
	    $mapper = $sm->get('DemoEntryMapper');
	    
	    $this->assertInstanceOf('Demo\Mapper\EntryMapper', $mapper);
	}
}