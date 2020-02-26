<?php namespace Wongyip\LibraryHelpers;

use BCLib\LCCallNumbers\RegExCallNumberParser;
use BCLib\LCCallNumbers\LCCallNumber;

class CallNumber
{
    /**
     * Normalize a Library of Congress Call Number (simplified use of 
     * LCCallNumber::normalize() method).
     *
     * Return false if call number is invalid.
     *
     * @param string $callNumber
     * @param string $sortChar
     * @return string|boolean
     */
    static function normalize($callNumber, $sortChar = null)
    {
        $sortChar = is_string($sortChar) ? $sortChar : LCCallNumber::LOW_SORT_CHAR;
        $callNumber = self::clean($callNumber);
        if (self::isValid($callNumber)) {
            $lccnParser = new RegExCallNumberParser();
            $lccnCallNo = new LCCallNumber();
            $lccnParser->parse(trim($callNumber), $lccnCallNo);
            return trim($lccnCallNo->normalize($sortChar));
        }
        return false;
    }
    
    /**
     * Normalize a Library of Congress Call Number Class (Simplified use of 
     * LCCallNumber::normalizeClass() method).
     * 
     * @param string $callNumber
     * @param string $sortChar
     * @return string
     */
    static function normalizeClass($class, $sortChar = null)
    {
        $sortChar = is_string($sortChar) ? $sortChar : LCCallNumber::LOW_SORT_CHAR;
        $lccnParser = new RegExCallNumberParser();
        $lccnCallNo = new LCCallNumber();
        $lccnParser->parse(trim($class), $lccnCallNo);
        return $lccnCallNo->normalizeClass($sortChar);
    }
    
    /**
     * Validate a Library of Congress Call Number.
     * 
     * @param string $callNumber
     * @return boolean
     */
    static function isValid($callNumber)
    {
        $callNumber = self::clean($callNumber);
        if (preg_match('/^[A-Z][0-9A-Z\s\.]*$/i', $callNumber)){
            return true;
        }
        return false;
    }
    
    /**
     * Clean up [QRT], volume no., copy no., etc. Note that dots (.) at the 
     * beginning will be keep to prevent the unwanted convertion of sub-class 
     * to a class.
     *
     * @param string $callNumber
     * @return string
     */
    static function clean($callNumber)
    {
        // Subfield Symbols to Spaces
        $callNumber = trim(preg_replace('/\$[a-z]|ǂ[a-z]|\|[a-z]/i', ' ', $callNumber));
        // Prefix
        $callNumber = trim(preg_replace('/^\[QRT\]\s?/i', '', $callNumber));
        // Copy number c1, c.2, c1-2, c.3-4
        $callNumber = trim(preg_replace('/\sc[0-9\-]+$|\sc\.[0-9\-]+$/', '', $callNumber));
        // eBook Suffix
        $callNumber = preg_replace('/eb$/', '', $callNumber);
        // Volume
        $callNumber = preg_replace('/v\.[0-9a-z\-\s]+/', '', $callNumber);
        // Issue
        $callNumber = preg_replace('/no\.[0-9a-z\-\s]+/', '', $callNumber);
        // Part
        $callNumber = preg_replace('/pt\.[0-9a-z\-\s]+/', '', $callNumber);
        // Chapter
        $callNumber = preg_replace('/ch\.[0-9a-z\-\s]+/', '', $callNumber);
        // Double space
        while (preg_match('/\s\s/', $callNumber)) {
            $callNumber = preg_replace('/\s\s/', ' ', $callNumber);
        }
        // Trim (Keep dots in the begining, as it may a partial call number
        $callNumber = trim(rtrim(trim($callNumber, " ,"), "."));
        return $callNumber;
    }
}