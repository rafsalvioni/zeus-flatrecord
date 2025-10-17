<?php

namespace Zeus\FlatRecord;

/**
 * Implements a abstract Record Parser
 *
 * @author Rafael M. Salvioni
 */
abstract class AbstractRecordParser implements RecordParserInterface
{
    /**
     * 
     * @param string $string
     * @return array
     */
    public function parse(string $string): array 
    {
        $obj = new \stdClass();
        $this->parseInto($string, $obj);
        return (array)$obj;
    }
}
