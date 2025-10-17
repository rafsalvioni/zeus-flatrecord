<?php

namespace Zeus\FlatRecord;

/**
 * Represents a Flat Record parser descriptor
 * 
 * @author Rafael M. Salvioni
 */
interface RecordParserInterface extends \Countable, \IteratorAggregate
{
    /**
     * Adds a field configuration
     * 
     * @param string $name
     * @param FieldConfigInterface $field
     * @return self
     */
    public function addField(string $name, FieldConfigInterface $field): self;
    
    /**
     * Parses a string and fills given object
     * 
     * @param string $string
     * @param object $obj
     * @return void
     */
    public function parseInto(string $string, object $obj): void;
    
    /**
     * Parses a string and returns a associative array
     * 
     * @param string $string
     * @return array
     */
    public function parse(string $string): array;
    
    /**
     * Returns object data as a flat string
     * 
     * @param object|array
     * @return string
     */
    public function getStringFrom(object|array $obj): string;
}
