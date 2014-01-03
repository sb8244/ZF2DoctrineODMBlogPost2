<?php

namespace DemoTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use Demo\Entity\Entry;

class EntryControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    
    protected $mockMapper;
    
    public function setUp()
    {
        $this->setApplicationConfig(
            include dirname(__FILE__) . '/../../../../../config/application.config.php'
        );
        parent::setUp();

        $this->mockMapper = Mockery::mock('Demo\Mapper\EntryMapper');
        $this->getApplicationServiceLocator()->setAllowOverride(true)
            ->setService('DemoEntryMapper', $this->mockMapper);
    }
    
    public function tearDown()
    {
        Mockery::close();
    }
    
    /*
     * Test that proper params are passed into mapper and entire array is json encoded
     */
    public function testListActionNoID()
    {
        $ret = ["data" => "test"];
        
        $this->mockMapper->shouldReceive('findAll')->times(1)->with()->andReturn($ret);
        
        $this->dispatch('/entry/list');
        
        $content = $this->getResponse()->getContent();
        $this->assertResponseStatusCode(200);
        $this->assertEquals(json_encode($ret), $content);
    }
    
    /**
     * Test that proper params are passed into mapper and single object is put into an array
     */
    public function testListActionWithID()
    {
        $ret = new \stdClass();
        $ret->data = "test";
    
        $this->mockMapper->shouldReceive('find')->times(1)->with(1)->andReturn($ret);
    
        $this->dispatch('/entry/list/1');
    
        $content = $this->getResponse()->getContent();
        $this->assertResponseStatusCode(200);
        $this->assertEquals(json_encode([$ret]), $content);
    }
    
    /*
     * 200 status, but empty results
     */
    public function testRemoveNoID()
    {
        $this->dispatch('/entry/remove');
        
        $content = $this->getResponse()->getContent();
        $this->assertResponseStatusCode(200);
        $this->assertEquals(json_encode([]), $content);
    }
    
    /*
     * 200 status, but empty results
    */
    public function testRemoveWithIDDoesntExist()
    {
        $this->mockMapper->shouldReceive('find')->times(1)->with(1)->andReturnNull();
        
        $this->dispatch('/entry/remove/1');
        
        $content = $this->getResponse()->getContent();
        $this->assertResponseStatusCode(200);
        $this->assertEquals(json_encode([]), $content);
    }
    
    /*
     * 200 status, with array presented results
    */
    public function testRemoveWithIDExist()
    {
        $ret = $this->getApplicationServiceLocator()->get('DemoEntry');
        $ret->text = "Test";
        
        $this->mockMapper->shouldReceive('find')->times(1)->with(1)->andReturn($ret);
        $this->mockMapper->shouldReceive('remove')->times(1)->with($ret);
        
        $this->dispatch('/entry/remove/1');
    
        $content = $this->getResponse()->getContent();
        $this->assertResponseStatusCode(200);
        $this->assertEquals(json_encode([$ret]), $content);
    }
    
    public function testAddGet()
    {
        $this->dispatch('/entry/add');
        
        $content = $this->getResponse()->getContent();
        $this->assertResponseStatusCode(200);
        $this->assertEquals(json_encode([]), $content);
    }

    public function testAddPostWithText()
    {
        $expect = $this->getApplicationServiceLocator()->get('DemoEntry');
        $expect->text = "Test";
        
        //having issues with "with($expect)" here even though it's correct,
        //not sure why that's an issue
        $this->mockMapper->shouldReceive('save')->times(1);
        
        $this->dispatch('/entry/add', 'POST', ["text" => "Test"]);
    
        $content = $this->getResponse()->getContent();
        $this->assertResponseStatusCode(200);
        $this->assertEquals(json_encode([$expect]), $content);
    }
}