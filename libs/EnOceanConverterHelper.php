<?php

declare(strict_types=1);

namespace EnOceanConverter;

class EEPConverter
{
    private const PROFILE_DATA = [
        EEPProfiles::A5_04_01 => ['minTemp' => 0, 'maxTemp' => 40, 'bitsTemp' => 8, 'minHum' => 0, 'maxHum' => 100, 'bitsHum' => 8],
        EEPProfiles::A5_04_02 => ['minTemp' => -20, 'maxTemp' => 60, 'bitsTemp' => 8, 'minHum' => 0, 'maxHum' => 100, 'bitsHum' => 8],
        EEPProfiles::A5_04_03 => ['minTemp' => -20, 'maxTemp' => 60, 'bitsTemp' => 10, 'minHum' => 0, 'maxHum' => 100, 'bitsHum' => 7],
        EEPProfiles::A5_04_04 => ['minTemp' => -40, 'maxTemp' => 120, 'bitsTemp' => 12, 'minHum' => 0, 'maxHum' => 100, 'bitsHum' => 8],
    ];

    public static function convertTemperature(string $inputProfile, float $value, string $targetProfile): string
    {
        $in  = self::PROFILE_DATA[$inputProfile];
        $out = self::PROFILE_DATA[$targetProfile];

        // Normalisierung auf 0..1
        $norm = ($value - $in['minTemp']) / ($in['maxTemp'] - $in['minTemp']);
        $norm = max(0.0, min(1.0, $norm));

        // Skalierung auf Zielprofil
        $scaled = round($norm * ((1 << $out['bitsTemp']) - 1));
        return sprintf('%02X', $scaled);
    }

    public static function convertHumidity(string $inputProfile, float $value, string $targetProfile): string
    {
        $in  = self::PROFILE_DATA[$inputProfile];
        $out = self::PROFILE_DATA[$targetProfile];

        $norm = ($value - $in['minHum']) / ($in['maxHum'] - $in['minHum']);
        $norm = max(0.0, min(1.0, $norm));

        $scaled = round($norm * ((1 << $out['bitsHum']) - 1));
        return sprintf('%02X', $scaled);
    }
}

class CRC8{

	// Aus EnOcean Doku (ESP3 Spec V1.17, Aug. 2011)
	private $crc8_table = array(
		0x00, 0x07, 0x0e, 0x09, 0x1c, 0x1b, 0x12, 0x15,
		0x38, 0x3f, 0x36, 0x31, 0x24, 0x23, 0x2a, 0x2d,
		0x70, 0x77, 0x7e, 0x79, 0x6c, 0x6b, 0x62, 0x65,
		0x48, 0x4f, 0x46, 0x41, 0x54, 0x53, 0x5a, 0x5d,
		0xe0, 0xe7, 0xee, 0xe9, 0xfc, 0xfb, 0xf2, 0xf5,
		0xd8, 0xdf, 0xd6, 0xd1, 0xc4, 0xc3, 0xca, 0xcd,
		0x90, 0x97, 0x9e, 0x99, 0x8c, 0x8b, 0x82, 0x85,
		0xa8, 0xaf, 0xa6, 0xa1, 0xb4, 0xb3, 0xba, 0xbd,
		0xc7, 0xc0, 0xc9, 0xce, 0xdb, 0xdc, 0xd5, 0xd2,
		0xff, 0xf8, 0xf1, 0xf6, 0xe3, 0xe4, 0xed, 0xea,
		0xb7, 0xb0, 0xb9, 0xbe, 0xab, 0xac, 0xa5, 0xa2,
		0x8f, 0x88, 0x81, 0x86, 0x93, 0x94, 0x9d, 0x9a,
		0x27, 0x20, 0x29, 0x2e, 0x3b, 0x3c, 0x35, 0x32,
		0x1f, 0x18, 0x11, 0x16, 0x03, 0x04, 0x0d, 0x0a,
		0x57, 0x50, 0x59, 0x5e, 0x4b, 0x4c, 0x45, 0x42,
		0x6f, 0x68, 0x61, 0x66, 0x73, 0x74, 0x7d, 0x7a,
		0x89, 0x8e, 0x87, 0x80, 0x95, 0x92, 0x9b, 0x9c,
		0xb1, 0xb6, 0xbf, 0xb8, 0xad, 0xaa, 0xa3, 0xa4,
		0xf9, 0xfe, 0xf7, 0xf0, 0xe5, 0xe2, 0xeb, 0xec,
		0xc1, 0xc6, 0xcf, 0xc8, 0xdd, 0xda, 0xd3, 0xd4,
		0x69, 0x6e, 0x67, 0x60, 0x75, 0x72, 0x7b, 0x7c,
		0x51, 0x56, 0x5f, 0x58, 0x4d, 0x4a, 0x43, 0x44,
		0x19, 0x1e, 0x17, 0x10, 0x05, 0x02, 0x0b, 0x0c,
		0x21, 0x26, 0x2f, 0x28, 0x3d, 0x3a, 0x33, 0x34,
		0x4e, 0x49, 0x40, 0x47, 0x52, 0x55, 0x5c, 0x5b,
		0x76, 0x71, 0x78, 0x7f, 0x6A, 0x6d, 0x64, 0x63,
		0x3e, 0x39, 0x30, 0x37, 0x22, 0x25, 0x2c, 0x2b,
		0x06, 0x01, 0x08, 0x0f, 0x1a, 0x1d, 0x14, 0x13,
		0xae, 0xa9, 0xa0, 0xa7, 0xb2, 0xb5, 0xbc, 0xbb,
		0x96, 0x91, 0x98, 0x9f, 0x8a, 0x8D, 0x84, 0x83,
		0xde, 0xd9, 0xd0, 0xd7, 0xc2, 0xc5, 0xcc, 0xcb,
		0xe6, 0xe1, 0xe8, 0xef, 0xfa, 0xfd, 0xf4, 0xf3
	);

	private static function hexstr($hexstr) {
        $hexstr = str_replace(' ', '', $hexstr);
        $hexstr = str_replace('\x', '', $hexstr);
        $retstr = pack('H*', $hexstr);
        return $retstr;
	}

	private static function strhex($string) {
        $hexstr = unpack('H*', $string);
        return array_shift($hexstr);
	}

	private static function fill8digits($p){
		$ret = $p;
		for ($i=0; $i<(8-strlen($p)); $i++){
            $ret="0".$ret;
		}
		return $ret;
	}

	private static function binxor($a, $b) {
		$x = self::fill8digits(base_convert($a, 16, 2));
		$y = self::fill8digits(base_convert($b, 16, 2));
		$result = "";
		for ($i=0; $i<8; $i++){
            if (((substr($x, $i, 1)==1) and (substr($y, $i, 1)==0)) or ((substr($x, $i, 1)==0) and (substr($y, $i, 1)==1))){
                $result .= "1";
            }
			else{
                $result .= "0";
			}
		}
		//echo ($x)."^".($y)."=".$result;
		return $result;
	}

	public static function calculate($data1){
        $hex = self::hexstr($data1);
        $crc = dechex(0);
        for ($i=0;$i<strlen($hex);$i++){
            $crc = dechex(self::$crc8_table[bindec(self::binxor($crc, self::strhex($hex[$i])))]);
        }
        return $crc;
	}

	public static function addLeadingZero($p_Str){
        $ret = $p_Str;
        if (strlen($p_Str)<2){
            for ($i=0; $i<(2-strlen($p_Str)); $i++){
                $ret = "0".$ret;
            }
        }
        return $ret;
	}

	public static function getHeader($p_DataLen = 7, $p_DataOptLen = 0, $p_Type = "01"){
        $p_Type = (int)$p_Type; // String → int casten
        $header = "00 ".self::addLeadingZero(dechex($p_DataLen))." ".self::addLeadingZero(dechex($p_DataOptLen))." ".self::addLeadingZero(dechex($p_Type));
        return $header." ".self::calculate($header);
	}
}

