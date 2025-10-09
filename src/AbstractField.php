<?php

namespace Zeus\FlatRecord;

use Zeus\FlatRecord\Decorator\DecoratorInterface;
use Zeus\FlatRecord\Decorator\DefaultDecorator;

/**
 * Represents a abstract field descriptor
 * 
 * @author Rafael M. Salvioni
 */
abstract class AbstractField
{
    /**
     * Target property
     * 
     * @var \ReflectionProperty
     */
    protected readonly \ReflectionProperty $target;
    /**
     * Field decorator
     * 
     * @var DecoratorInterface
     */
    protected readonly DecoratorInterface $decorator;
    /**
     * Defined setter (if property isnt public)
     * 
     * @var string|null
     */
    protected readonly ?string $setter;
    /**
     * Defined getter (if property isnt public)
     * 
     * @var string|null
     */
    protected readonly ?string $getter;
    
    /**
     * 
     * @param DecoratorInterface|null $decorator If null, uses DefaultDecorator
     */
    public function __construct(
        ?DecoratorInterface $decorator = null
    ) {
        $this->decorator = ($decorator ?? new DefaultDecorator());
    }
    
    /**
     * Field name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->target->getName();
    }
    
    /**
     * Defines target property
     * 
     * @param \ReflectionProperty $target
     */
    public function setTarget(\ReflectionProperty $target)
    {
        $this->target = $target;
        if (!$target->isPublic()) {
            $name   = $target->getName();
            $setter = "set" . \ucfirst($name);
            $getter = "get" . \ucfirst($name);
            $this->setter = $setter;
            $this->getter = $getter;
        }
        else {
            $this->setter = $this->getter = null;
        }
    }
    
    /**
     * Returns target type
     * 
     * @return string
     */
    public function getType(): string
    {
        $type = $this->target->getType();
        if ($type) {
            return (string)$type;
        }
        return 'mixed';
    }

    /**
     * Is field required?
     * 
     * @return bool
     */
    public function isRequired(): bool
    {
        $type = $this->target->getType();
        if ($type) {
            return !$type->allowsNull();
        }
        return false;
    }
    
    /**
     * Returns field value as string
     * 
     * It gets object value and given it to decorator
     * 
     * @param object $obj
     * @return string
     */
    public function getStringValue(object $obj): string
    {
        $val = $this->target->isInitialized($obj) ? $this->getValue($obj) : null;
        return $this->decorator->toString($val);
    }
    
    /**
     * Defines a field value with its string representation
     * 
     * String will be converted using decorator first
     * 
     * @param object $obj
     * @param string $str
     * @return void
     */
    public function setStringValue(object $obj, string $str): void
    {
        $val = $this->decorator->fromString($str, $this->getType());
        $this->setValue($obj, $val);
    }
    
    /**
     * Gets field value from getter or property
     * 
     * @param object $obj
     * @return mixed
     */
    protected function getValue(object $obj): mixed
    {
        if (isset($this->getter)) {
            return $obj->{$this->getter}();
        }
        return $this->target->getValue($obj);
    }
    
    /**
     * Sets field value using setter or property directally
     * 
     * @param object $obj
     * @param mixed $val
     * @return void
     */
    protected function setValue(object $obj, mixed $val): void
    {
        if (isset($this->setter)) {
            $obj->{$this->setter}($val);
        }
        else {
            $this->target->setValue($obj, $val);
        }
    }
}
