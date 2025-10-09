<?php

namespace Zeus\FlatRecord\Delimited;

use Attribute;
use Zeus\FlatRecord\AbstractField;
use Zeus\FlatRecord\FlatRecordInterface;

#[Attribute(Attribute::TARGET_CLASS)]
/**
 * Represents a record delimited by a specific char, like a CSV
 * 
 * @author Rafael M. Salvioni
 */
class DelimitedRecord implements FlatRecordInterface
{
    /**
     * Fields
     * 
     * @var IndexedField[]
     */
    private array $fields  = [];
    /**
     * Fields indexes order
     * 
     * @var array
     */
    private array $indexes = [];
    
    /**
     * 
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @param string $eol
     * @throws \LogicException
     */
    public function __construct(
        private string $delimiter = ',',
        private string $enclosure = '"',
        private string $escape    = '\\',
        private string $eol       = \PHP_EOL,
    ) {
        $chars = \strlen(\implode('', \func_get_args()));
        if ($chars != \func_num_args()) {
            throw new \LogicException('Arguments should be 1 char each');
        }
    }
    
    /**
     * 
     * @param AbstractField $field
     * @return self
     * @throws \LogicException
     */
    public function addField(AbstractField $field): self
    {
        $name = $field->getName();
        $this->fields[$name] = $field;
        if (isset($this->indexes[$field->index])) {
            throw new \LogicException('There is another field in same index');
        }
        $this->indexes[$field->index] = $name;
        \ksort($this->indexes);
        return $this;
    }
    
    /**
     * 
     * @param string $name
     * @return AbstractField|null
     */
    public function getField(string $name): ?AbstractField
    {
        return $this->fields[$name] ?? null;
    }
    
    /**
     * 
     * @param string $string
     * @param object $obj
     * @return void
     */
    public function parseInto(string $string, object $obj): void
    {
        $parsed = \str_getcsv($string, $this->delimiter, $this->enclosure, $this->escape);
        foreach ($parsed as $i => $str) {
            if (isset($this->indexes[$i])) {
                $name  =& $this->indexes[$i];
                $field =& $this->fields[$name];
                $field->setStringValue($obj, $str);
            }
        }
    }
    
    /**
     * 
     * @param object $obj
     * @return string
     */
    public function toString(object $obj): string
    {
        $size = \count($this->indexes) > 0 ? \max(\array_keys($this->indexes)) + 1 : 0;
        $vals = \array_fill(0, $size, '');

        for ($i = 0; $i < $size; $i++) {
            if (isset($this->indexes[$i])) {
                $name  =& $this->indexes[$i];
                $field =& $this->fields[$name];
                $vals[$i] = (string)$field->getStringValue($obj);
            }
        }
        
        $fp  = \fopen('php://memory', 'r+');
        \fputcsv($fp, $vals, $this->delimiter, $this->enclosure, $this->escape, $this->eol);
        \rewind($fp);
        $str = \stream_get_contents($fp);
        \fclose($fp);
        return $str;
    }
}
