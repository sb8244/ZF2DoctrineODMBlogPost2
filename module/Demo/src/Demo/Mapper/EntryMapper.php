<?php

namespace Demo\Mapper;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Demo\Entity\Entry;
use DemoBase\Mapper\GenericMapper;

/**
 * Entry Mapper extends the GenericMapper and follows the interface
 * given
 * 
 * @author Steve
 *
 */
class EntryMapper extends GenericMapper implements EntryMapperInterface
{
    //Custom defined sortBy
    protected $sortBy = [
        'text' => 'ASC'
    ];
    
    public function findCustomFunction($custom, $params)
    {
        $query = [ 'custom' => $custom , 'params' => $params ];
        return $this->repository->findBy($query, $this->sortBy);
    }
}