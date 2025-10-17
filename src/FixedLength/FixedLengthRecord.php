<?php

namespace Zeus\FlatRecord\FixedLength;

use ArrayIterator;
use Attribute;
use InvalidArgumentException;
use Traversable;
use Zeus\FlatRecord\AbstractRecordParser;
use Zeus\FlatRecord\Exception\FieldOverwriteException;
use Zeus\FlatRecord\FieldConfigInterface;
use Zeus\FlatRecord\RecordParserInterface;
use function is_between;
use function object_getter;
use function object_setter;

/**
 * Represents a Fixed-Length Record
 * 
 * A fixed-length record uses fields with fixed length in sequence and
 * the resultant always has the same length
 * 
 * @author Rafael M. Salvioni
 */
#[Attribute(Attribute::TARGET_CLASS)]
class FixedLengthRecord extends AbstractRecordParser
{
    /**
     * Total record length
     * 
     * @var int
     */
    private int $length    = 0;
    /**
     * Fields, indexed by name
     * 
     * @var FixedLengthField[]
     */
    private array $fields  = [];
    /**
     * Fields order. Needed to write by offset order
     * 
     * @var string[]
     */
    private array $order  = [];
    
    /**
     * 
     * @param string $padChar Char to fill fields gaps
     * @param string $eol EOL sequence
     * @param bool $lenient Lenient mode allows short lines to parse and fields overlaps
     */
    public function __construct(
       /**
        * Char to fill fields gaps
        * 
        * @var string
        */
       public readonly string $padChar = '?',
       /**
        * EOL
        * 
        * @var string
        */
       public readonly string $eol = \PHP_EOL,
       /**
        * Lenient mode
        * 
        * @var string
        */
       public readonly bool   $lenient = true
    ) {
    }

    /**
     * 
     * @param string $name
     * @param FieldConfigInterface $field
     * @param type $f
     * @return RecordParserInterface
     * @throws InvalidArgumentException Expects a FieldLengthField object
     * @throws FieldOverwriteException If field already exists (non lenient mode)
     * @throws FieldOvelapException If field overlaps another (non lenient mode)
     */
    public function addField(string $name, FieldConfigInterface $field): RecordParserInterface
    {
        if (!($field instanceof FixedLengthField)) {
            throw new InvalidArgumentException('Field should be a FixedLenghtField object');
        }
        if (isset($this->fields[$name])) {
            if (!$this->lenient) {
                throw new FieldOverwriteException("There was a field \"$name\" defined");
            }
            $this->length = 0; // Field overwrited... Need to recalc all...
            unset($this->fields[$name]);
            unset($this->order[$this->fields[$name]->index]);
        }
        if (!$this->lenient) {
            $overlaps = array_filter($this->fields, fn($f) => is_between($field->index, $f->index, $f->stop, true));
            if (!empty($overlaps)) {
                throw new FieldOverlapException("\"$name\" is overlapping");
            }
        }
        
        $this->fields[$name] = $field;
        $this->order[$field->index] = $name;
        \ksort($this->order);
        
        $toCalc = $this->length ? [$field] : $this->fields;
        foreach ($toCalc as &$f) {
            $this->length += \max($f->index - $this->length + $f->length, 0);
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
        if (!$this->lenient && $this->length && !isset($string[$this->length - 1])) {
            throw new \LengthException('Given line is short');
        }
        foreach ($this->order as &$name) {
            $field  =& $this->fields[$name];
            $strVal = \substr($string, $field->index, $field->length);
            $strVal = $field->padType->unpad($strVal, $field->padChar[0]);
            $val    = empty($strVal)
                ? $field->empty
                : $field->getDecorator()->fromString($strVal);
            object_setter($obj, $name, $val);
        }
    }

    /**
     * 
     * @param object|array $obj
     * @return string
     * @throws FieldLenghtException When field length is exceeded a cant be truncated
     */
    public function getStringFrom(object|array $obj): string
    {
        $string = \str_repeat($this->padChar[0] ?? '?', $this->length) . $this->eol;
        $obj    = (object)$obj; // Converting a array to obj
        
        foreach ($this->order as &$name) {
            $field  =& $this->fields[$name];
            $val    = object_getter($obj, $name);
            $strVal = $field->getDecorator()->toString($val);
            $strVal = $field->padType->pad($strVal, $field->length, $field->padChar);
            
            if (isset($strVal[$field->length])) { // Is length exceeded?
                if (!$field->trunc) {
                    throw new FieldLenghtException(\sprintf('"%s" exceeds length limit', $name));
                }
                $strVal = $field->padType->truncate($strVal, $field->length);
            }
            
            $string = \substr_replace($string, $strVal, $field->index, $field->length);
        }
        return $string;
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
    
    /**
     * Returns current record length
     * 
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }
}
