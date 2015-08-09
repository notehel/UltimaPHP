<?php

/**
 * Ultima PHP - OpenSource Ultima Online Server written in PHP
 * Version: 0.1 - Pre Alpha
 */

class Functions
{
    public static function getDword($hex) {
        static $max = '2147483647';
        $num = hexdec($hex);
        $int = intval($num);
        if ($num !== $int) return FALSE;
        $hex = str_pad($hex, 8, '0', STR_PAD_LEFT);
        $bin = base_convert($hex, 16, 2);
        $bin = str_pad($bin, 32, '0', STR_PAD_LEFT);
        $arr = str_split($bin);
        $neg = FALSE;
        if ($arr[0] == '1') $neg = TRUE;
        unset($arr[0]);
        $bin = '0' . implode(NULL, $arr);
        $ans = base_convert($bin, 2, 10);
        if ($neg) {
            $ans = $ans - $max - 1;
        }
        return $ans;
    }

    public static function strToHex($string){
        $hex = '';
        for ($i=0; $i<strlen($string); $i++){
            $hex .= substr('0'.dechex(ord($string[$i])), -2);
        }
        return strToUpper($hex);
    }

    public static function strToChr($string) {
        $ret = "";
        for ($i=0; $i < strlen($string); $i++) { 
            $ret .= chr(dechex(ord($string[$i])));
        }
        return $ret;
    }

    public static function hexToChr($data, $from = null, $to = null, $explodeOnChr0 = false){
        if (is_array($data)) {
            $hex = self::implodeByte($data, $from, $to);
        } else {
            $hex = $data;
        }

        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }

        if ($explodeOnChr0) {
            $string = explode(chr(0), $string);
            $string = $string[0];
        }
        
        return $string;
    }

    public static function implodeByte($byteArray = array(), $from, $to) {
        $ret = "";
        for ($i=$from; $i <= $to; $i++) { 
            $ret .= $byteArray[$i];
        }
        return $ret;
    }
}
?>