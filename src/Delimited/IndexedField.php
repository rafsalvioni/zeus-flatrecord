<?php

namespace Zeus\FlatRecord\Delimited;

use Attribute;
use Zeus\FlatRecord\AbstractFieldConfig;

/**
 * Represents a indexed field configuration
 * 
 * A indexed field are fields ordering by index. Theirs positions are mandatory
 * 
 * @author Rafael M.Salvioni
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class IndexedField extends AbstractFieldConfig
{
}
