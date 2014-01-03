<?php

namespace Demo\Mapper;

use Demo\Entity\Entry;
use DemoBase\Mapper\GenericMapperInterface;

/**
 * This interface has all required functionality as the GenericMapperInterface
 * provides, and can define more.
 * 
 * @author Steve
 *
 */
interface EntryMapperInterface extends GenericMapperInterface
{
    /**
     * A custom function which is just an example
     * @param $custom
     * @param $params
     */
    public function findByCustom($custom, $params);
}