<?php

namespace Zeus\FlatRecord;

use Zeus\FlatRecord\Decorator\DecoratorInterface;

/**
 * Represents a field parser configuration
 * 
 * @author Rafael M.Salvioni
 */
interface FieldConfigInterface
{
    /**
     * Returns field's decorator
     * 
     * @return DecoratorInterface
     */
    public function getDecorator(): DecoratorInterface;
}
