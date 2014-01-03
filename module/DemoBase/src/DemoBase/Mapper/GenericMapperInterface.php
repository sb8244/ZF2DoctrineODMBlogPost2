<?php

namespace DemoBase\Mapper;

/**
 * Interface that all mappers should follow
 * 
 * @author Steve
 *
 */
interface GenericMapperInterface
{
    /**
     * @param string $id
     * @return Entity Single entity with the id given
     */
	public function find($id);
	
	/**
	 * @return array All entities in the object repository
	 */
	public function findAll();
	
	/**
	 * 
	 * @param array $criteria Criteria to check for existence by
	 * @return boolean Whether an entity exists by this criteria
	 */
	public function existsBy(array $criteria);
	
	/**
	 * Saves an Entity into the repository
	 * @param $entity
	 */
	public function save($entity);
	
	/**
	 * Removes an Entnity from the repository
	 * @param $entity
	 */
	public function remove($entity);
}