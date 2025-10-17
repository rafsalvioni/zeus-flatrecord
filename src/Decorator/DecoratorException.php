<?php

namespace Zeus\FlatRecord\Decorator;

use DomainException;
use Zeus\FlatRecord\Exception\RecordExceptionInterface;

/**
 * Description of DecoratorException
 *
 * @author rafael
 */
class DecoratorException extends DomainException implements
    RecordExceptionInterface
{
}
