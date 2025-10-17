<?php

namespace Zeus\FlatRecord\Delimited;

use ArrayIterator;
use Attribute;
use DomainException;
use InvalidArgumentException;
use Traversable;
use Zeus\FlatRecord\AbstractRecordParser;
use Zeus\FlatRecord\Exception\FieldOverwriteException;
use Zeus\FlatRecord\FieldConfigInterface;
use Zeus\FlatRecord\RecordParserInterface;
use function object_getter;
use function object_setter;
use function str_putcsv;

/**
 * Represents a record delimited by a specific char, like a CSV
 * 
 * @author Rafael M. Salvioni
 */
#[Attribute(Attribute::TARGET_CLASS)]
class DelimitedRecord extends AbstractRecordParser
{
    /**
     * Fields, indexed by names
     * 
     * @var IndexedField[]
     */
    private array $fields  = [];
    /**
     * Fields order
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
     * @param bool   $lenient
     */
    public function __construct(
        /**
         * Delimiter (or separator)
         * 
         * @var string
         */
        public readonly string $delimiter = ',',
        /**
         * Enclosure char
         * 
         * @var string
         */
        public readonly string $enclosure = '"',
        /**
         * Escape char
         * 
         * @var string
         */
        public readonly string $escape    = '\\',
        /**
         * EOL chars
         * 
         * @var string
         */
        public readonly string $eol       = \PHP_EOL,
        /**
         * Lenient mode
         * 
         * @var bool
         */
        public readonly bool   $lenient = true
    ) {
    }

    /**
     * 
     * @param string $name
     * @param FieldConfigInterface $field
     * @return RecordParserInterface
     * @throws InvalidArgumentException
     * @throws FieldOverwriteException If field already exists (non lenient mode)
     */
    public function addField(string $name, FieldConfigInterface $field): RecordParserInterface
    {
        if (!($field instanceof IndexedField)) {
            throw new InvalidArgumentException('Field should be a IndexedField object');
        }
        if (isset($this->fields[$name]) && !$this->lenient) {
            throw new FieldOverwriteException("There was a field \"$name\" defined");
        }
        if (isset($this->indexes[$field->index]) && !$this->lenient) {
            throw new FieldOverwriteException("There was a index \"{$field->index}\" defined");
        }
        $this->fields[$name] = $field;
        $this->indexes[$field->index] = $name;
        \ksort($this->indexes);
        return $this;
    }
    
    /**
     * Adds fields configuration using a header line
     * 
     * @param string $headerLine Header line
     * @return RecordParserInterface
     */
    public function addHeader(string $headerLine): RecordParserInterface
    {
        $headers = \str_getcsv($headerLine, $this->delimiter[0], $this->enclosure[0], $this->escape[0]);
        foreach ($headers as $index => &$name) {
            $config = new IndexedField($index);
            $this->addField($name, $config);
        }
        return $this;
    }
    
    /**
     * 
     * @param string $string
     * @param object $obj
     * @return void
     */
    public function parseInto(string $string, object $obj): void
    {
        $parsed = \str_getcsv($string, $this->delimiter[0], $this->enclosure[0], $this->escape[0]);
        foreach ($parsed as $i => $str) {
            if (isset($this->indexes[$i])) {
                $name  =& $this->indexes[$i];
                $field =& $this->fields[$name];
                $val   = empty($str) ? $field->empty : $field->getDecorator()->fromString($str);
                object_setter($obj, $name, $val);
            }
        }
    }
    
    /**
     * 
     * @param object|array $obj
     * @return string
     */
    public function getStringFrom(object|array $obj): string
    {
        $size   = \count($this->indexes) > 0 ? \max(\array_keys($this->indexes)) + 1 : 0;
        $fields = \array_fill(0, $size, '');
        $obj    = (object)$obj;

        foreach ($this->indexes as $i => &$name) {
            $field  =& $this->fields[$name];
            $val    = object_getter($obj, $name);
            $strVal = $field->getDecorator()->toString($val);
            $fields[$i] = $strVal;
        }
        
        return str_putcsv(
            $fields, $this->delimiter[0], $this->enclosure[0],
            $this->escape[0], $this->eol
        );
    }

    /**
     * 
     * @return int
     */
    public function count(): int
    {
        return \count($this->fields);
    }

    /**
     * 
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fields);
    }
}
