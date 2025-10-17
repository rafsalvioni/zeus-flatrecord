<?php

namespace Zeus\FlatRecord\Decorator;

use Zeus\FlatRecord\FlatEngine;

/**
 * Implements a RecordDecorator
 * 
 * RecordDecorator is used to add embeded records inside other records
 *
 * @author Rafael M. Salvioni
 */
class RecordDecorator implements DecoratorInterface
{
    /**
     * 
     * @param string $class
     */
    public function __construct(
        /**
         * Record class
         * 
         * @var string
         */
        private string $class
    )
    {        
    }
    
    /**
     * 
     * @param string $string
     * @return mixed
     */
    public function fromString(string $string): mixed
    {
        return FlatEngine::createFrom($string, $this->class);
    }

    /**
     * 
     * @param mixed $value
     * @return string
     */
    public function toString(mixed $value): string
    {
        return FlatEngine::getStringFrom($value);
    }
}
