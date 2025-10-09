<?php

declare(strict_types=1);

namespace EnOceanConverter;

class EEPConverter
{
    private const PROFILE_DATA = [
        EEPProfiles::A5_04_01 => [  'minTemp' => 0, 'maxTemp' => 40,    'bitsTemp' => 8, 
                                    'minHum' => 0,  'maxHum' => 100,    'bitsHum' => 8],
        EEPProfiles::A5_04_02 => [  'minTemp' => -20, 'maxTemp' => 60,  'bitsTemp' => 8, 
                                    'minHum' => 0,    'maxHum' => 100,  'bitsHum' => 8],
        EEPProfiles::A5_04_03 => [  'minTemp' => -20, 'maxTemp' => 60,  'bitsTemp' => 10, 
                                    'minHum' => 0,    'maxHum' => 100,  'bitsHum' => 7],
        EEPProfiles::A5_04_04 => [  'minTemp' => -40, 'maxTemp' => 120, 'bitsTemp' => 12, 
                                    'minHum' => 0,    'maxHum' => 100,  'bitsHum' => 8],
    ];

    	static function decodeTemperature(string $profile, float $raw): float {
		switch($profile) {
			case EEPProfiles::A5_04_01: // 8 Bit, 0…40°C
				return 0 + (40 - 0) * ($raw / 255);
			case EEPProfiles::A5_04_02: // 8 Bit, -20…60°C
				return -20 + (60 - -20) * ($raw / 255);
			case EEPProfiles::A5_04_03: // 10 Bit, -20…60°C
				return -20 + (60 - -20) * ($raw / 1023);
			case EEPProfiles::A5_04_04: // 12 Bit, -40…120°C
				return -40 + (120 - -40) * ($raw / 4095);
			default:
				return NAN;
		}
	}

	static function decodeHumidity(string $profile, float $raw): float {
		switch($profile) {
			case EEPProfiles::A5_04_01:
			case EEPProfiles::A5_04_02:
			case EEPProfiles::A5_04_04:
				return 0 + (100 - 0) * ($raw / 255);
			case EEPProfiles::A5_04_03: // 7 Bit
				return 0 + (100 - 0) * ($raw / 127);
			default:
				return NAN;
		}
	}

	static function encodeTemperature(string $profile, float $temperature): int {
		switch ($profile) {
			case EEPProfiles::A5_04_01:
				return (int)round(($temperature - 0) * 255 / 40);
			case EEPProfiles::A5_04_02:
				return (int)round(($temperature + 20) * 255 / 80);
			case EEPProfiles::A5_04_03:
				return (int)round(($temperature + 20) * 1023 / 80);
			case EEPProfiles::A5_04_04:
				return (int)round(($temperature + 40) * 4095 / 160);
			default:
				throw new \Exception("Unbekanntes EEP Profil: $profile");
		}
	}

	static function encodeHumidity(string $profile, float $humidity): int {
		switch ($profile) {
			case EEPProfiles::A5_04_01:
			case EEPProfiles::A5_04_02:
			case EEPProfiles::A5_04_04:
				return (int)round($humidity * 255 / 100);
			case EEPProfiles::A5_04_03:
				return (int)round($humidity * 127 / 100);
			default:
				throw new \Exception("Unbekanntes EEP Profil: $profile");
		}
	}

    public static function convertTemperature(string $inputProfile, float $value, string $targetProfile): string
    {
        // Raw = (Temp - minTemp) / (maxTemp - minTemp) * (2^bits - 1)
        $in = self::PROFILE_DATA[$inputProfile];
        $out = self::PROFILE_DATA[$targetProfile];
        // Normalisierung auf 0..1
        $norm = ($value - $in['minTemp']) / ($in['maxTemp'] - $in['minTemp']);
        $norm = max(0.0, min(1.0, $norm));
        // Skalierung auf Zielprofil
        $maxValue = (1 << $out['bitsTemp']) - 1;
        $scaled = (int)round($norm * $maxValue);
        // Hex-Format anpassen an Bit-Breite
        $hexDigits = (int)ceil($out['bitsTemp'] / 4);
        return sprintf('%0' . $hexDigits . 'X', $scaled);
    }

    public static function convertHumidity(string $inputProfile, float $value, string $targetProfile): string
    {
        // Raw = Humidity / 100 * (2^bits - 1)
        $in  = self::PROFILE_DATA[$inputProfile];
        $out = self::PROFILE_DATA[$targetProfile];
        $norm = ($value - $in['minHum']) / ($in['maxHum'] - $in['minHum']);
        $norm = max(0.0, min(1.0, $norm));
        $scaled = round($norm * ((1 << $out['bitsHum']) - 1));
        return sprintf('%02X', $scaled);
    }
}


