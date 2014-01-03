<?php

namespace DemoBase\Mapper;

use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Generic Doctrine mapper which provides some simple and common features. Allows
 * single entry point for save and encoding objects in utf8
 * 
 * @author Steve Bussey
 *
 */
class GenericMapper implements GenericMapperInterface, EventManagerAwareInterface
{
	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $documentManager;
	
	/**
	 * @var \Doctrine\Common\Persistence\ObjectRepository
	 */
	protected $repository;
	
	/**
	 * Array of criteria to sort by (pass to findBy always)
	 * Sadly, can't use a filter for this so define in mapper implementations
	 * @var array in format [ ["first"=>"ASC"], ["second"=>"DESC"] ]
	 */
	protected $sortBy = array();
	
	/**
	 * @var Zend\EventManager\EventManager
	 */
	protected $eventManager;
	
	public function __construct(ObjectManager $documentManager,	ObjectRepository $repository)
	{
		$this->documentManager = $documentManager;
		$this->repository = $repository;
	}
	
	public function find($id)
	{
		return $this->repository->find($id);
	}
	
	public function findAll()
	{
		return $this->repository->findBy([], $this->sortBy);
	}

	public function existsBy(array $criteria)
	{
	    $resultSet = $this->repository->findBy($criteria);
	    return $resultSet->count() > 0;
	}
	
	public function save($entity)
	{
		try
		{
		    $new = $this->documentManager->contains($entity) === false;
		    $this->utf8_encode_deep($entity);
			$this->getEventManager()->trigger(get_class($entity) . "::" . __FUNCTION__ . '.pre' , $this, ['entity'=>$entity, 'new' => $new]);
			$this->documentManager->persist($entity);
			$this->documentManager->flush();
			$this->getEventManager()->trigger(get_class($entity) . "::" . __FUNCTION__ . '.post' , $this, ['entity'=>$entity, 'new' => $new]);
			return true;
		} catch (\MongoCursorException $e)
		{
			$duplicates = array();
			$sm = $this->documentManager->getSchemaManager();
			$indexes = $sm->getDocumentIndexes($this->repository->getClassName());
			foreach($indexes as $index)
			{
				if($index['options']['unique'] == true)
				{
					foreach($index['keys'] as $key=>$value)
					{
						$index['keys'][$key] = $entity->$key;
					}
					if($this->existsBy($index['keys']))
					{
						$duplicates[] = $index['keys'];
					}
				}
			}
			return $duplicates;
		}
	}
	
	public function remove($entity)
	{
		$this->getEventManager()->trigger(get_class($entity) . "::" . __FUNCTION__ . '.pre' , $this, ['entity'=>$entity]);
		$this->documentManager->remove($entity);
		$this->documentManager->flush();
		$this->getEventManager()->trigger(get_class($entity) . "::" . __FUNCTION__ . '.post' , $this, ['entity'=>$entity]);
	}
	
	public function setEventManager(\Zend\EventManager\EventManagerInterface $eventManager)
	{
		$eventManager->setIdentifiers(array(
				__CLASS__,
				get_called_class(),
		));
		$this->eventManager = $eventManager;
		return $this;
	}
	
	public function getEventManager()
	{
		if (null === $this->eventManager) {
			$this->setEventManager(new EventManager());
		}
		return $this->eventManager;
	}
	
	public function getSortByCritera()
	{
	    return $this->sortBy;
	}
	
	/**
	 * In-place utf8 encode an object and all property strings
	 * 
	 * @see https://gist.github.com/oscar-broman/3653399
	 * @param unknown $input
	 */
	private function utf8_encode_deep(&$input) 
	{
	    if (is_string($input)) 
	    {
	        $input = utf8_encode($input);
	    } 
	    else if (is_array($input)) 
	    {
	        foreach ($input as &$value) 
	        {
	            $this->utf8_encode_deep($value);
	        }
	
	        unset($value);
	    } 
	    else if (is_object($input)) 
	    {
	        $vars = array_keys(get_object_vars($input));
	
	        foreach ($vars as $var) 
	        {
	            $this->utf8_encode_deep($input->$var);
	        }
	    }
	}
}