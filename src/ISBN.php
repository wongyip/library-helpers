<?php namespace Wongyip\LibraryHelpers;

/**
 * Validate and Convert ISBN-10 / ISBN-13 numbers.
 *   
 * ISBN-13 & ISBN-10 Conversion
 *  - Since ISBN-13 = 978 + [First-9-digits-of-ISBN-10] + [Check-digit-of-ISBN-13].
 * 	- So, extract the [First-9-digits-of-ISBN-10] from ISBN-13.
 *	- Then, calculate the checksum of ISBN-10.
 *  - Thats all!
 *
 * References:
 *  1.	http://en.wikipedia.org/wiki/International_Standard_Book_Number
 *  2.	http://www.hahnlibrary.net/libraries/isbncalc.html
 *      (by Joel Hahn, Niles Public Library District)
 * 
 * @author wongyip@outlook.com
 */
class ISBN
{
    /**
     * Convert to ISBN-10.
     * 
     * Strict mode: turn on to return FALSE if input is not a valid ISBN-13, turn
     * off to return the input ISBN of it is already a valid ISBN-10. 
     * 
     * @param string  $isbn
     * @param boolean $strict
     * @return string|false
     */
    static function convertTo10($isbn, $strict = false)
    {
        if (self::isValid13($isbn)){
            // Normalize
            $isbn = (string) preg_replace("/\-/", "", $isbn);
            // Convert
            return (substr($isbn, 3, 9) . self::checksum10(substr($isbn, 3, 9)));
        }
        elseif (!$strict) {
            if (self::isValid10($isbn)) {
                return $isbn;
            }
        }
        return false;
    }
    
    /**
     * Convert ISBN-13.
     * 
     * Strict mode: turn on to return FALSE if input is not a valid ISBN-10, turn
     * off to return the input ISBN of it is already a valid ISBN-13. 
     * 
     * @param string  $isbn
     * @param boolean $strict
     */
    static function convertTo13($isbn, $strict = false)
    {
        if (self::isValid10($isbn)) {
            // Normalize
            $isbn = (string) preg_replace("/\-/", "", $isbn);
            // Some ISBN-10 just shorter!
            while (strlen($isbn) < 10){
                $isbn = "0$isbn";
            }
            // Add the 978 prefix and remove the ISBN-10 check digit.
            $isbn = "978" . substr($isbn, 0, 9);
            return ($isbn . self::checksum13($isbn));
        }
        elseif (!$strict) {
            if (self::isValid13($isbn)) {
                return $isbn;
            }
        }
        return false;
    }
    
    /**
     * Calculate ISBN-13 check-digit, i.e. find the checksum of ISBN-10 if the 
     * first 9 digits are given.
     * 
     * @param string $isbn
     * @return boolean
     */
    static function checksum10($isbn)
    {
        // Normalize (Remove hyphens)
        $isbn = (string) preg_replace("/\-/", "", $isbn);
        // Validate
        if (!preg_match("/^[\d]*[\dxX]$/", $isbn))
            return false;
            // You know what is ISBN-10?
            if (strlen($isbn) > 10){
                return false;
            }
            // Return the checksum digit if an valid 10-digit ISBN-10 is given.
            else if (strlen($isbn) == 10){
                if (self::isValid10($isbn))
                    return substr($isbn, 9, 1);
                    else
                        return false;
            }
            // OK, have to do the caculation...
            else {
                // Some ISBN-10 is shorter, like humans...
                while (strlen($isbn) < 9){
                    $isbn = "0$isbn";
                }
                // Caculate the checksum
                // Ref: http://en.wikipedia.org/wiki/International_Standard_Book_Number#ISBN-10_check_digit_calculation
                $chksum = 0;
                for ($strPos = 0; $strPos <= 8; $strPos++){
                    $chksum = $chksum + (($strPos+1) * (int) (substr($isbn, $strPos, 1)));
                }
                $chksum = $chksum % 11;
                if ($chksum == 10)
                    return "X";
                    else
                        return $chksum;
            }
            return false;
    }
    
    /**
     * Calculate ISBN-13 check-digit.
     * 
     * @param string $isbn
     * @return boolean
     */
    static function checksum13($isbn)
    {
        // Normalize (Remove hyphens)
        $isbn = (string) preg_replace("/\-/", "", $isbn);
        // Validate [Starts with 978, total 13-digit or 12-digit]
        if (!preg_match("/^978[\d]{10}$/", $isbn) && !preg_match("/^978[\d]{9}$/", $isbn))
            return false;
            // Return the checksum digit if an valid ISBN-13 is given.
            if (strlen($isbn) == 13){
                if (self::isValid13($isbn))
                    return substr($isbn, 12, 1);
                    else
                        return false;
            }
            // OK, have to do the caculation
            else {
                // Caculate the checksum
                // Ref: http://en.wikipedia.org/wiki/International_Standard_Book_Number#ISBN-13_check_digit_calculation
                $chksum = 0;
                $multipier = 1;
                for ($strPos = 0; $strPos <= 11; $strPos++){
                    $chksum = $chksum + ((int)(substr($isbn, $strPos, 1)) * $multipier);
                    $multipier = ($multipier == 1) ? 3 : 1;
                }
                $chksum = 10 - ($chksum % 10);
                $chksum = $chksum == 10 ? 0 : $chksum;
                return $chksum ;
            }
            return false;
    }
    
    /**
     * Some ISBN-10 is just shorter, and nobody like a short ISBN.
     * 
     * @param string $isbn
     * @return string|boolean
     */
    static function patch10($isbn)
    {
        if (self::isValid10($isbn)){
            while (strlen($isbn) < 9){
                $isbn = "0$isbn";
            }
            return $isbn;
        }
        else {
            return false;
        }
    }
    
    /**
     * Valdate ISBN-10 for correct format and check-digit.
     * 
     * @param string $isbn
     * @return boolean
     */
    static function isValid10($isbn)
    {
        // Normalize (Remove hyphens)
        $isbn = (string) preg_replace("/\-/", "", $isbn);
        // Validate [Starts with digit, digits only except checksum (which can be X), at lease 2-char, maximum 10-char]
        if (!preg_match("/^[\d]*[\dxX]$/", $isbn) || strlen($isbn) < 2 || strlen($isbn) > 10) {
            return false;
        }
        // Return true if checksum correct
        else {
            return (self::checksum10(substr($isbn, 0, strlen($isbn) - 1)) == substr($isbn, strlen($isbn) - 1, 1));
        }
    }
    
    /**
     * Valdate ISBN-13 for correct format and check-digit.
     * 
     * @param string $isbn
     * @return boolean
     */
    static function isValid13($isbn)
    {
        // Normalize (Remove hyphens)
        $isbn = (string) preg_replace("/\-/", "", $isbn);
        // Validate [Exact 13-digit, starts with 978]
        if (!preg_match("/^978[\d]{10}$/", $isbn)) {
            return false;
        }
        // Return true if checksum correct
        else {
            return (self::checksum13(substr($isbn, 0, 12)) == substr($isbn, 12, 1));
        }
    }
}