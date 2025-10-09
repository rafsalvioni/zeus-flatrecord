<?php

namespace Zeus\FlatRecord;

/**
 * Represents a Flat Record descriptor
 * 
 * @author Rafael M. Salvioni
 */
interface FlatRecordInterface
{
    /**
     * Attach a field to record
     * 
     * @param AbstractField $field
     * @return self
     */
    public function addField(AbstractField $field): self;
    
    /**
     * Returns a field descriptor
     * 
     * @param string $name
     * @return AbstractField|null
     */
    public function getField(string $name): ?AbstractField;
    
    /**
     * Parses a string and fills given object
     * 
     * @param string $string
     * @param object $obj
     * @return void
     */
    public function parseInto(string $string, object $obj): void;
    
    /**
     * Returns object data as a flat string
     * 
     * @param object
     * @return string
     */
    public function toString(object $obj): string;
}
