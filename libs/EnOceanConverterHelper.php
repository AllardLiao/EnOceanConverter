<?php

declare(strict_types=1);

namespace EnOceanConverter;

class EEPConverter
{
    private const PROFILE_DATA = [
        EEPProfiles::A5_02_13 => [  'minTemp' => -30, 'maxTemp' => 50,      'bitsTemp' => 8],
        EEPProfiles::A5_04_01 => [  'minTemp' => 0,   'maxTemp' => 40,      'bitsTemp' => 8,
                                    'minHum' => 0,    'maxHum' => 100,      'bitsHum' => 8],
        EEPProfiles::A5_04_02 => [  'minTemp' => -20, 'maxTemp' => 60,      'bitsTemp' => 8, 
                                    'minHum' => 0,    'maxHum' => 100,      'bitsHum' => 8],
        EEPProfiles::A5_04_03 => [  'minTemp' => -20, 'maxTemp' => 60,      'bitsTemp' => 10, 
                                    'minHum' => 0,    'maxHum' => 100,      'bitsHum' => 7],
        EEPProfiles::A5_04_04 => [  'minTemp' => -40, 'maxTemp' => 120,     'bitsTemp' => 12, 
                                    'minHum' => 0,    'maxHum' => 100,      'bitsHum' => 8],
        EEPProfiles::A5_07_01 => [  'motionNo' => 127, 'motionYes' => 255,  'bitsMotion' => 8,
                                    'minVolt' => 0,   'maxVolt' => 5,       'bitsVolt' => 8],
        EEPProfiles::A5_07_02 => [  'motionNo' => 0,  'motionYes' => 1,     'bitsMotion' => 1,
                                    'minVolt' => 0,   'maxVolt' => 5,       'bitsVolt' => 8],
        EEPProfiles::A5_07_03 => [  'motionNo' => 0,  'motionYes' => 1,     'bitsMotion' => 1,
                                    'minVolt' => 0,   'maxVolt' => 5,       'bitsVolt' => 8,
                                    'minLux' => 0,    'maxLux' => 1000,     'bitsLux' => 10],
        EEPProfiles::A5_08_01 => [  'motionNo' => 1,  'motionYes' => 0,     'bitsMotion' => 1,
                                    'buttonNo' => 0,  'buttonYes' => 1,     'bitsButton' => 1,
                                    'minTemp' => 0,   'maxTemp' => 51,      'bitsTemp' => 8,
                                    'minLux' => 0,    'maxLux' => 510,      'bitsLux' => 8,
                                    'minVolt' => 0,   'maxVolt' => 5.1,     'bitsVolt' => 8],
        EEPProfiles::A5_08_02 => [  'motionNo' => 1,  'motionYes' => 0,     'bitsMotion' => 1,
                                    'buttonNo' => 0,  'buttonYes' => 1,     'bitsButton' => 1,
                                    'minTemp' => 0,   'maxTemp' => 51,      'bitsTemp' => 8,
                                    'minLux' => 0,    'maxLux' => 1020,     'bitsLux' => 8,
                                    'minVolt' => 0,   'maxVolt' => 5.1,     'bitsVolt' => 8],
        EEPProfiles::A5_08_03 => [  'motionNo' => 1,  'motionYes' => 0,     'bitsMotion' => 1,
                                    'buttonNo' => 0,  'buttonYes' => 1,     'bitsButton' => 1,
                                    'minTemp' => -30, 'maxTemp' => 50,      'bitsTemp' => 8,
                                    'minLux' => 0,    'maxLux' => 1530,     'bitsLux' => 8,
                                    'minVolt' => 0,   'maxVolt' => 5.1,     'bitsVolt' => 8]
    ];

    static function decodeTemperature(string $profile, float $raw): float {
		switch($profile) {
            case EEPProfiles::A5_02_13: // 8 Bit, -30…50°C
                return -30 + (50 - -30) * ((255 - $raw) / 255.0);
			case EEPProfiles::A5_04_01: // 8 Bit, 0…40°C
				return 0 + (40 - 0) * ($raw / 250);
			case EEPProfiles::A5_04_02: // 8 Bit, -20…60°C
				return -20 + (60 - -20) * ($raw / 250);
			case EEPProfiles::A5_04_03: // 10 Bit, -20…60°C
				return -20 + (60 - -20) * ($raw / 1023);
			case EEPProfiles::A5_04_04: // 12 Bit, -40…120°C
				return -40 + (120 - -40) * ($raw / 4095);
            case EEPProfiles::A5_08_01: // 8 Bit, 0…51°C
                return 0 + (51 - 0) * ($raw / 255);
            case EEPProfiles::A5_08_02: // 8 Bit, 0…51°C
                return 0 + (51 - 0) * ($raw / 255);
            case EEPProfiles::A5_08_03: // 8 Bit, -30…50°C
                return -30 + (50 - -30) * ($raw / 255);
			default:
				return NAN;
		}
	}

	static function encodeTemperature(string $profile, float $temperature): int {
		switch ($profile) {
            case EEPProfiles::A5_02_13:
                return (int)round(255 - (int)round((($temperature - -30) * 255) / 80));
			case EEPProfiles::A5_04_01:
				return (int)round(($temperature - 0) * 250 / 40);
			case EEPProfiles::A5_04_02:
				return (int)round(($temperature + 20) * 250 / 80);
			case EEPProfiles::A5_04_03:
				return (int)round(($temperature + 20) * 1023 / 80);
			case EEPProfiles::A5_04_04:
				return (int)round(($temperature + 40) * 4095 / 160);
            case EEPProfiles::A5_08_01:
                return (int)round(($temperature - 0) * 255 / 51);
            case EEPProfiles::A5_08_02:
                return (int)round(($temperature - 0) * 255 / 51);
            case EEPProfiles::A5_08_03:
                return (int)round(($temperature + 30) * 255 / 80);
			default:
                return 0;
				//throw new \Exception("Unbekanntes EEP Temperatur-Profil: $profile");
		}
	}

	static function decodeHumidity(string $profile, float $raw): float {
		switch($profile) {
			case EEPProfiles::A5_04_01:
			case EEPProfiles::A5_04_02:
			case EEPProfiles::A5_04_04:
				return 0 + (100 - 0) * ($raw / 250);
			case EEPProfiles::A5_04_03: // 7 Bit
				return 0 + (100 - 0) * ($raw / 255);
			default:
				return 0.0;
		}
	}

	static function encodeHumidity(string $profile, float $humidity): int {
		switch ($profile) {
			case EEPProfiles::A5_04_01:
			case EEPProfiles::A5_04_02:
			case EEPProfiles::A5_04_04:
				return (int)round($humidity * 250 / 100);
			case EEPProfiles::A5_04_03:
				return (int)round($humidity * 255 / 100);
			default:
				return 0;
		}
	}

    static function decodeMotion(string $profile, float $raw): bool {
        switch($profile) {
            case EEPProfiles::A5_07_01:
                return $raw > 127;
            case EEPProfiles::A5_07_02:
            case EEPProfiles::A5_07_03:
                return $raw == 1;
            case EEPProfiles::A5_08_01:
            case EEPProfiles::A5_08_02:
            case EEPProfiles::A5_08_03:
                return $raw == 0;
            default:
                return false;
        }
    }

    static function encodeMotion(string $profile, bool $motion): int {
        switch ($profile) {
            case EEPProfiles::A5_07_01:
                return $motion ? 255 : 0;
            case EEPProfiles::A5_07_02:
            case EEPProfiles::A5_07_03:
                return $motion ? 1 : 0;
            case EEPProfiles::A5_08_01:
            case EEPProfiles::A5_08_02:
            case EEPProfiles::A5_08_03:
                return $motion ? 0 : 1;
            default:
                return 0;
        }
    }

    static function decodeButtonPressed(string $profile, float $raw): bool {
        switch($profile) {
            case EEPProfiles::A5_08_01:
            case EEPProfiles::A5_08_02:
            case EEPProfiles::A5_08_03:
                return $raw == 0;
            default:
                return false;
        }
    }
    static function decodeVoltage(string $profile, float $raw): float {
        switch($profile) {
            case EEPProfiles::A5_07_01:
            case EEPProfiles::A5_07_02:
            case EEPProfiles::A5_07_03:
                return 0 + (5 - 0) * ($raw / 250);
            case EEPProfiles::A5_08_01:
                return 0 + (5.1 - 0) * ($raw / 255);
            case EEPProfiles::A5_08_02:
                return 0 + (5.1 - 0) * ($raw / 255);
            case EEPProfiles::A5_08_03:
                return 0 + (5.1 - 0) * ($raw / 255);
            default:
                return 0.0;
        }
    }

    static function encodeVoltage(string $profile, float $voltage): int {
        switch ($profile) {
            case EEPProfiles::A5_07_01:
            case EEPProfiles::A5_07_02:
            case EEPProfiles::A5_07_03:
                return (int)round(($voltage - 0) * 250 / 5);
            case EEPProfiles::A5_08_01:
            case EEPProfiles::A5_08_02:
            case EEPProfiles::A5_08_03:
                return (int)round(($voltage - 0) * 255 / 5.1);
            default:
                return 0;
        }
    }

    static function decodeLux(string $profile, float $raw): float {
        switch($profile) {
            case EEPProfiles::A5_07_03:
                return 0 + (1000 - 0) * ($raw / 1000);
            case EEPProfiles::A5_08_01:
                return 0 + (510 - 0) * ($raw / 255);
            case EEPProfiles::A5_08_02:
                return 0 + (1020 - 0) * ($raw / 255);
            case EEPProfiles::A5_08_03:
                return 0 + (1530 - 0) * ($raw / 255);
            default:
                return 0.0;
        }
    }

    static function encodeLux(string $profile, float $lux): int {
        switch ($profile) {
            case EEPProfiles::A5_07_03:
                return (int)round($lux * 1000 / 1000);
            case EEPProfiles::A5_08_01:
                return (int)round($lux * 255 / 510);
            case EEPProfiles::A5_08_02:
                return (int)round($lux * 255 / 1020);
            case EEPProfiles::A5_08_03:
                return (int)round($lux * 255 / 1530);
            default:
                return 0;
        }
    }
}

trait MessagesHelper
{
	private function registerECMessage(string $name, int $vid, int $currentStatus): int
	{
		if ($vid > 0) {
			if ($this->RegisterMessage($vid, VM_UPDATE)) {
				$this->SendDebug(__FUNCTION__, $name . ' messages registered: ' . $vid, 0);
				// Status auf 102 nur, wenn noch nicht 201 gesetzt
				if ($currentStatus !== 201) $currentStatus = 102;
			} else {
				$this->SendDebug(__FUNCTION__, 'Failed to register ' . $name .	 ' messages: ' . $vid, 0);
				$currentStatus = 201; // Priorität: Fehler
			}
		} else {
			$this->SendDebug(__FUNCTION__, $name . ' message ID not set',	 0);
			// $this->SetValue($name, 0); // Variable zurücksetzen - denn es konnte keine Quell-Variable gefunden werden.
			// Status unverändert: einige Profile haben nicht alle Variablen, ist also ok
		}
		return $currentStatus;
	}

    private function unregisterAllECMessages(): bool
    {
        return $this->UnregisterMessage(0, 0);
    }
}

trait DeviceIDHelper
{
	private function selectFreeDeviceID()
	{
		$Gateway = @IPS_GetInstance($this->InstanceID)["ConnectionID"];
		if($Gateway == 0) return;
		$Devices = IPS_GetInstanceListByModuleType(3);             # alle Geräte
		$DeviceArray = array();
		foreach ($Devices as $Device){
            $this->SendDebug(__FUNCTION__, 'Check Device: ' . print_r(IPS_GetInstance($Device), true), 0);
			if(isset(IPS_GetInstance($Device)["ConnectionID"]) && IPS_GetInstance($Device)["ConnectionID"] == $Gateway){
				$config = json_decode(IPS_GetConfiguration($Device));
				if(!property_exists($config, 'DeviceID'))continue;
				if(is_integer($config->DeviceID)) $DeviceArray[] = $config->DeviceID;
			}
		}	
		for($ID = 1; $ID<=256; $ID++)if(!in_array($ID,$DeviceArray))break;
		return $ID == 256?0:$ID;
	}
}

trait VariableHelper{

    // Einheitliche Idents und Namen für die Variablen in allen Modulen:
    public const EEP_VARIABLES = [
        "Humidity"      => ["Ident" => "varHumidity",             "Name" => "Luftfeuchtigkeit"],
        "Temperature"   => ["Ident" => "varTemperature",          "Name" => "Temperatur"],
        "Motion"        => ["Ident" => "varMotion",               "Name" => "PIR-Status"],
        "Illumination"  => ["Ident" => "varIllumination",         "Name" => "Helligkeit"],
        "Voltage"       => ["Ident" => "varVoltage",              "Name" => "Versorgungsspannung"],
        "Button"        => ["Ident" => "varButton",               "Name" => "Taster-Status"]
    ];

    public const EEP_VARIABLE_PROFILES = 
    [
        EEPProfiles::A5_02_13 => [
            self::EEP_VARIABLES["Temperature"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Temperature"]["Ident"],  "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature',   "Name"    => self::EEP_VARIABLES["Temperature"]["Name"]]
        ],
        EEPProfiles::A5_04_01 => [
            self::EEP_VARIABLES["Temperature"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Temperature"]["Ident"],  "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature',   "Name"    => self::EEP_VARIABLES["Temperature"]["Name"]],
            self::EEP_VARIABLES["Humidity"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Humidity"]["Ident"],     "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Humidity.F',    "Name"    => self::EEP_VARIABLES["Humidity"]["Name"]]
        ],
        EEPProfiles::A5_04_02 => [
            self::EEP_VARIABLES["Temperature"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Temperature"]["Ident"],  "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature',   "Name"    => self::EEP_VARIABLES["Temperature"]["Name"]],
            self::EEP_VARIABLES["Humidity"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Humidity"]["Ident"],     "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Humidity.F',    "Name"    => self::EEP_VARIABLES["Humidity"]["Name"]]
        ],
        EEPProfiles::A5_04_03 => [
            self::EEP_VARIABLES["Temperature"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Temperature"]["Ident"],  "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature',   "Name"    => self::EEP_VARIABLES["Temperature"]["Name"]],
            self::EEP_VARIABLES["Humidity"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Humidity"]["Ident"],     "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Humidity.F',    "Name"    => self::EEP_VARIABLES["Humidity"]["Name"]]
        ],
        EEPProfiles::A5_04_04 => [
            self::EEP_VARIABLES["Temperature"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Temperature"]["Ident"],  "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature',   "Name"    => self::EEP_VARIABLES["Temperature"]["Name"]],
            self::EEP_VARIABLES["Humidity"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Humidity"]["Ident"],     "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Humidity.F',    "Name"    => self::EEP_VARIABLES["Humidity"]["Name"]]
        ],
        EEPProfiles::A5_07_01 => [
            self::EEP_VARIABLES["Motion"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Motion"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Motion',        "Name"    => self::EEP_VARIABLES["Motion"]["Name"]],
            self::EEP_VARIABLES["Voltage"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Voltage"]["Ident"],      "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Volt',          "Name"    => self::EEP_VARIABLES["Voltage"]["Name"]]
        ],
        EEPProfiles::A5_07_02 => [
            self::EEP_VARIABLES["Motion"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Motion"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Motion',        "Name"    => self::EEP_VARIABLES["Motion"]["Name"]],
            self::EEP_VARIABLES["Voltage"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Voltage"]["Ident"],      "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Volt',          "Name"    => self::EEP_VARIABLES["Voltage"]["Name"]]
        ],
        EEPProfiles::A5_07_03 => [
            self::EEP_VARIABLES["Motion"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Motion"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Motion',        "Name"    => self::EEP_VARIABLES["Motion"]["Name"]],
            self::EEP_VARIABLES["Illumination"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Illumination"]["Ident"], "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Illumination',  "Name"    => self::EEP_VARIABLES["Illumination"]["Name"]],
            self::EEP_VARIABLES["Voltage"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Voltage"]["Ident"],      "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Volt',          "Name"    => self::EEP_VARIABLES["Voltage"]["Name"]]
        ],
        EEPProfiles::A5_08_01 => [
            self::EEP_VARIABLES["Motion"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Motion"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Motion',        "Name"    => self::EEP_VARIABLES["Motion"]["Name"]],
            self::EEP_VARIABLES["Temperature"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Temperature"]["Ident"],  "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature',   "Name"    => self::EEP_VARIABLES["Temperature"]["Name"]],
            self::EEP_VARIABLES["Illumination"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Illumination"]["Ident"], "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Illumination',  "Name"    => self::EEP_VARIABLES["Illumination"]["Name"]],
            self::EEP_VARIABLES["Voltage"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Voltage"]["Ident"],      "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Volt',          "Name"    => self::EEP_VARIABLES["Voltage"]["Name"]],
            self::EEP_VARIABLES["Button"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Button"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Switch',        "Name"    => self::EEP_VARIABLES["Button"]["Name"]]
        ],
        EEPProfiles::A5_08_02 => [
            self::EEP_VARIABLES["Motion"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Motion"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Motion',        "Name"    => self::EEP_VARIABLES["Motion"]["Name"]],
            self::EEP_VARIABLES["Temperature"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Temperature"]["Ident"],  "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature',   "Name"    => self::EEP_VARIABLES["Temperature"]["Name"]],
            self::EEP_VARIABLES["Illumination"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Illumination"]["Ident"], "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Illumination',  "Name"    => self::EEP_VARIABLES["Illumination"]["Name"]],
            self::EEP_VARIABLES["Voltage"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Voltage"]["Ident"],      "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Volt',          "Name"    => self::EEP_VARIABLES["Voltage"]["Name"]],
            self::EEP_VARIABLES["Button"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Button"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Switch',        "Name"    => self::EEP_VARIABLES["Button"]["Name"]]
        ],
        EEPProfiles::A5_08_03 => [
            self::EEP_VARIABLES["Motion"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Motion"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Motion',        "Name"    => self::EEP_VARIABLES["Motion"]["Name"]],
            self::EEP_VARIABLES["Temperature"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Temperature"]["Ident"],  "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature',   "Name"    => self::EEP_VARIABLES["Temperature"]["Name"]],
            self::EEP_VARIABLES["Illumination"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Illumination"]["Ident"], "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Illumination',  "Name"    => self::EEP_VARIABLES["Illumination"]["Name"]],
            self::EEP_VARIABLES["Voltage"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Voltage"]["Ident"],      "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Volt',          "Name"    => self::EEP_VARIABLES["Voltage"]["Name"]],
            self::EEP_VARIABLES["Button"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Button"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Switch',        "Name"    => self::EEP_VARIABLES["Button"]["Name"]]
        ],
        EEPProfiles::A5_08_02 => [
            self::EEP_VARIABLES["Motion"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Motion"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Motion',        "Name"    => self::EEP_VARIABLES["Motion"]["Name"]],
            self::EEP_VARIABLES["Temperature"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Temperature"]["Ident"],  "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature',   "Name"    => self::EEP_VARIABLES["Temperature"]["Name"]],
            self::EEP_VARIABLES["Illumination"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Illumination"]["Ident"], "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Illumination',  "Name"    => self::EEP_VARIABLES["Illumination"]["Name"]],
            self::EEP_VARIABLES["Voltage"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Voltage"]["Ident"],      "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Volt',          "Name"    => self::EEP_VARIABLES["Voltage"]["Name"]],
            self::EEP_VARIABLES["Button"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Button"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Switch',        "Name"    => self::EEP_VARIABLES["Button"]["Name"]]
        ],
        EEPProfiles::A5_08_03 => [
            self::EEP_VARIABLES["Motion"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Motion"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Motion',        "Name"    => self::EEP_VARIABLES["Motion"]["Name"]],
            self::EEP_VARIABLES["Temperature"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Temperature"]["Ident"],  "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature',   "Name"    => self::EEP_VARIABLES["Temperature"]["Name"]],
            self::EEP_VARIABLES["Illumination"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Illumination"]["Ident"], "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Illumination',  "Name"    => self::EEP_VARIABLES["Illumination"]["Name"]],
            self::EEP_VARIABLES["Voltage"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Voltage"]["Ident"],      "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Volt',          "Name"    => self::EEP_VARIABLES["Voltage"]["Name"]],
            self::EEP_VARIABLES["Button"]["Ident"] => ["Ident" => self::EEP_VARIABLES["Button"]["Ident"],       "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Switch',        "Name"    => self::EEP_VARIABLES["Button"]["Name"]]
        ]
    ];
    
    private function maintainECVariable(array $variable, int $position, bool $visible): void
    {
        $this->MaintainVariable($variable['Ident'], $variable['Name'], $variable['Type'], $variable['Profile'], $position, $visible);
    }

    private function maintainECVariables(array $variables): void
    {
        $this->deleteAllVariables();
        $position = 1;
        foreach ($variables as $variable) {
            $this->maintainECVariable($variable, $position, isset($variable['Keep']) ? $variable['Keep'] : true);
            $position++;
        }
    }

    private function deleteAllVariables(): void
    {
        foreach (IPS_GetChildrenIDs($this->InstanceID) as $childID) {
            if (IPS_VariableExists($childID)) {
                $ident = IPS_GetObject($childID)['ObjectIdent'];
                $this->SendDebug(__FUNCTION__, "Lösche Variable: " . $ident, 0);
                IPS_DeleteVariable($childID);
            }
        }
    }
    private function getBufferKeyFromVarIdent(string $varIdent): ?string
    {
        foreach (self::EEP_VARIABLES as $key => $def) {
            if ($def['Ident'] === $varIdent) {
                return $key; // z.B. "Temperature"
            }
        }
        return null; // falls nicht gefunden
    }    
}

trait BufferHelper{

    public const EEP_BUFFERS = [
        "Humidity"      => ["Ident" => "bufferHumidity",             "Name" => "Luftfeuchtigkeit"],
        "Temperature"   => ["Ident" => "bufferTemperature",          "Name" => "Temperatur"],
        "Motion"        => ["Ident" => "bufferMotion",               "Name" => "PIR-Status"],
        "Illumination"  => ["Ident" => "bufferIllumination",         "Name" => "Helligkeit"],
        "Voltage"       => ["Ident" => "bufferVoltage",              "Name" => "Versorgungsspannung"],
        "Button"        => ["Ident" => "bufferButton",               "Name" => "Taster-Status"]
    ];

    private function maintainECBuffer(array $buffer): void
    {
        $this->SetBuffer($buffer['Ident'], "0");
    }

    private function maintainECBuffers(array $buffers): void
    {
        foreach ($buffers as $buffer) {
            $this->maintainECBuffer($buffer);
        }
    }
}