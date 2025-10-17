<?php

namespace Zeus\FlatRecord\Exception;

use OutOfRangeException;

/**
 * 
 * @author Rafael M. Salvioni
 */
class InvalidIndexException extends OutOfRangeException implements
    FieldExceptionInterface
{
}
