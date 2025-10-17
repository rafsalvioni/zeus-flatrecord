<?php

namespace Zeus\FlatRecord\FixedLength;

/**
 * Describe PAD types
 *
 * @author Rafael M. Salvioni
 */
enum PadType: int
{
    /**
     * Pad LEFT
     */
    case LEFT  = \STR_PAD_LEFT;
    /**
     * Pad RIGHT
     */
    case RIGHT = \STR_PAD_RIGHT;
    /**
     * Pad BOTH
     */
    case BOTH  = \STR_PAD_BOTH;
    
    /**
     * Pads a string until length given using a pad char
     * 
     * @param string $string
     * @param int $length
     * @param string $padChar
     * @return string
     */
    public function pad(string $string, int $length, string $padChar = ' '): string
    {
        return \str_pad($string, $length, $padChar, $this->value);
    }
    
    /**
     * Removes pad char from given string
     * 
     * Depends from pad type
     * 
     * @param string $string
     * @param string $padChar
     * @return string
     */
    public function unpad(string $string, string $padChar = ' '): string
    {
        $trim = match($this) {
            self::LEFT => 'ltrim',
            self::BOTH => 'trim',
            default    => 'rtrim',
        };
        return $trim($string, $padChar);
    }
    
    /**
     * Truncates a string until given length
     * 
     * Uses PadType to choice the preferred string side to truncate.
     * 
     * PadType::LEFT  => Prefer truncate from LEFT
     * PadType::RIGHT => Prefer truncate from RIGHT
     * PadType::BOTH  => Prefer truncate from BOTH sides equally
     * 
     * @param string $string
     * @param int $length
     * @return string
     */
    public function truncate(string $string, int $length): string
    {
        switch ($this) {
            case self::LEFT:
                return \substr($string, -$length);
            case self::BOTH:
                $len    = \strlen($string);
                $index  = (int)\max((($len - $length) / 2), 0);
                return \substr($string, $index, $length);
            default: // Right is default
                return \substr($string, 0, $length);
        }
    }
}
