<?php

namespace Zeus\FlatRecord;

use ReflectionClass;
use Zeus\FlatRecord\Exception\ParserDefinitionException;

/**
 * Flat engine
 * 
 * It loads record parsers from classes with parse/field Attributes and store
 * them statically, centralizing parse and "to string" operations
 * 
 * The records descritors working with class hierarchy and traits too.
 *
 * @author Rafael M. Salvioni
 */
final class FlatEngine
{
    /**
     * Creates a new object using line given
     * 
     * @param string $line
     * @param string $class
     * @return object
     */
    public static function createFrom(string $line, string $class): object
    {
        $obj = new $class();
        self::parseInto($line, $obj);
        return $obj;
    }
    
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
    public static function getStringFrom(object $obj): string
    {
        return self::load($obj)->getStringFrom($obj);
    }
    
    /**
     * Loads the record descriptor to given object or class
     * 
     * Use class' Attributes to mount record parser
     * 
     * First, it try do find a attribute child of RecordParserInterface to determine
     * main parser. If found, it iterates in class properties to gets attributes child
     * of FieldConfigInterface and adds them to the parser
     * 
     * So, result parser is cached to next uses
     * 
     * @param object|string $objOrClass
     * @return RecordParserInterface
     * @throws ParserDefinitionException Couldnt determine record parser
     */
    public static function load(object|string $objOrClass): RecordParserInterface
    {
        static $loaded = [];
        $class = \is_object($objOrClass) ? \get_class($objOrClass) : $objOrClass;
        if (!isset($loaded[$class])) {
            $parser = self::loadDescriptor($objOrClass);
            if (!$parser) {
                throw new ParserDefinitionException(
                    \sprintf('%s class doenst have a %s attribute', $class, RecordParserInterface::class)
                );
            }
            $loaded[$class] = $parser;
        }
        return $loaded[$class];
    }

    /**
     * Try to load a record descriptor with specific Attributes
     * 
     * Considers class hierarchy
     * 
     * Returns null if cant
     * 
     * @param object|string $objOrClass
     * @param string $attrRecord Record attribute
     * @param string $attrField Field attribute
     * @return RecordParserInterface|null
     */
    private static function loadDescriptor(object|string $objOrClass): ?RecordParserInterface
    {
        $class = new ReflectionClass($objOrClass);
        foreach ($class->getAttributes() as $attrib) {
            $attrClass = new ReflectionClass($attrib->getName());
            if ($attrClass->isSubclassOf(RecordParserInterface::class)) {
                $parser = $attrib->newInstance();
                break;
            }
        }
        if (!isset($parser)) {
            return null;
        }
        foreach ($class->getProperties() as $prop) {
            foreach ($prop->getAttributes() as $attrib) {
                $attrClass = new ReflectionClass($attrib->getName());
                if ($attrClass->isSubclassOf(FieldConfigInterface::class)) {
                    $field = $attrib->newInstance();
                    $parser->addField($prop->getName(), $field);
                }
            }
        }
        return $parser;
    }
}
