<?php

namespace Zeus\FlatRecord;

use Zeus\FlatRecord\Delimited\DelimitedRecord;
use Zeus\FlatRecord\Delimited\IndexedField;
use Zeus\FlatRecord\Positional\PositionalField;
use Zeus\FlatRecord\Positional\PositionalRecord;

/**
 * Flat engine
 * 
 * It loads and stores statically classes records descriptors and
 * centralizes parse and toString methods
 * 
 * The records descritors working with class hierarchy and traits too.
 *
 * @author Rafael M. Salvioni
 */
final class FlatEngine
{
    /**
     * Classes' descripptors
     * 
     * @var FlatRecordInterface[]
     */
    private static array $descriptors = [];
    
    /**
     * Parses a string into object given
     * 
     * @param string $line
     * @param object $obj
     * @return void
     */
    public static function parseInto(string $line, object $obj): void
    {
        self::load($obj)->parseInto($line, $obj);
    }
    
    /**
     * Gets the object record string representation
     * 
     * @param object $obj
     * @return string
     */
    public static function toString(object $obj): string
    {
        return self::load($obj)->toString($obj);
    }
    
    /**
     * Creates a record descriptor to given object
     * 
     * @param object $obj
     * @return FlatRecordInterface
     * @throws \Exception
     */
    private static function load(object $obj): FlatRecordInterface
    {
        $class = \get_class($obj);
        if (!isset(self::$descriptors[$class])) {
            $parser = self::loadDescriptor($obj, PositionalRecord::class, PositionalField::class);
            $parser = $parser ?? self::loadDescriptor($obj, DelimitedRecord::class, IndexedField::class);
            if (!$parser) {
                throw new \Exception(\sprintf('%s class doenst have a FlatRecord attribute', \get_class($obj)));
            }
            self::$descriptors[$class] = $parser;
        }
        return self::$descriptors[$class];
    }

    /**
     * Try to load a record descriptor with specific Attributes
     * 
     * Considers class hierarchy
     * 
     * Returns null if cant
     * 
     * @param object $obj
     * @param string $attrRecord Record attribute
     * @param string $attrField Field attribute
     * @return FlatRecordInterface|null
     */
    private static function loadDescriptor(
        object $obj, string $attrRecord, string $attrField
    ): ?FlatRecordInterface {
        $class = new \ReflectionClass($obj);
        foreach ($class->getAttributes($attrRecord) as $attrib) {
            $parser = $attrib->newInstance();
            break;
        }
        if (!isset($parser)) {
            return null;
        }
        foreach ($class->getProperties() as $prop) {
            foreach ($prop->getAttributes($attrField) as $attrib) {
                $field = $attrib->newInstance();
                $field->setTarget($prop);
                $parser->addField($field);
            }
        }
        return $parser;
    }
}
