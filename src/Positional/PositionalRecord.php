<?php

namespace Zeus\FlatRecord\Positional;

use Attribute;
use Zeus\FlatRecord\AbstractField;
use Zeus\FlatRecord\FlatRecordInterface;

/**
 * Represents a Positional Record
 * 
 * A positional record uses fields with fixed length in sequence
 * 
 * @author Rafael M. Salvioni
 */
#[Attribute(Attribute::TARGET_CLASS)]
class PositionalRecord implements FlatRecordInterface
{
    /**
     * Fields, indexed by name
     * 
     * @var \Zeus\FlatRecord\Delimited\IndexedField[]
     */
    private array $fields  = [];
    /**
     * Fields order index
     * 
     * @var array
     */
    private array $indexes = [];
    /**
     * Total record length
     * 
     * @var int
     */
    private int $length    = 0;
    
    /**
     * 
     * @param string $eol EOL
     */
    public function __construct(
       /**
        * EOL
        * 
        * @var string
        */
       private string $eol = \PHP_EOL
    )
    {
    }
    
    /**
     * 
     * @param AbstractField $field
     * @return self
     * @throws \LogicException If theres a field using same offset
     */
    public function addField(AbstractField $field): self
    {
        $other = \array_filter(
            $this->fields,
            fn($f) => $field->offset >= $f->offset && $field->offset <= $f->stop
        ); // Is there conflict?
        if (\count($other)) { // Yes!
            $other = \array_shift($other);
            throw new \LogicException(\sprintf('"%s" is in position conflict with "%s"', $field->getName(), $other->getName()));
        }
        $name = $field->getName();
        $this->fields[$name] = $field;
        $this->indexes[$field->offset] = $name;
        $this->length += $field->length;
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
        foreach ($this->indexes as &$name) {
            $field =& $this->fields[$name];
            $val = \substr($string, $field->offset, $field->length);
            $field->setStringValue($obj, $val);
        }
        $this->lastString = $string;
    }
    
    /**
     * 
     * @param object $obj
     * @return string
     */
    public function toString(object $obj): string
    {
        $string = \str_repeat(' ', $this->length) . $this->eol;
        foreach ($this->indexes as &$name) {
            $field  =& $this->fields[$name];
            $val    = $field->getStringValue($obj);
            $string = \substr_replace($string, $val, $field->offset, $field->length);
        }
        return $string;
    }
}
