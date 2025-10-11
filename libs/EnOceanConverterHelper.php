<?php

declare(strict_types=1);

namespace EnOceanConverter;

use EnOceanConverter\EnOceanConverterConstants;


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

    static function FormatEepProfile(string $profile): string
    {
        if (!isset(self::PROFILE_DATA[$profile])) {
            return "Profile $profile is not defined.";
        }

        $data = self::PROFILE_DATA[$profile];
        $lines = ["Profile $profile details:"];

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'minTemp':
                    $lines[] = "Temperature: {$data['minTemp']} … {$data['maxTemp']} °C ({$data['bitsTemp']} bits)";
                    break;
                case 'minHum':
                    $lines[] = "Humidity: {$data['minHum']} … {$data['maxHum']} % ({$data['bitsHum']} bits)";
                    break;
                case 'motionNo':
                    $lines[] = "Motion: {$data['motionNo']} = no, {$data['motionYes']} = yes ({$data['bitsMotion']} bits)";
                    break;
                case 'buttonNo':
                    $lines[] = "Button: {$data['buttonNo']} = not pressed, {$data['buttonYes']} = pressed ({$data['bitsButton']} bits)";
                    break;
                case 'minLux':
                    $lines[] = "Illumination: {$data['minLux']} … {$data['maxLux']} lx ({$data['bitsLux']} bits)";
                    break;
                case 'minVolt':
                    $lines[] = "Voltage: {$data['minVolt']} … {$data['maxVolt']} V ({$data['bitsVolt']} bits)";
                    break;
            }
        }

        return implode("\n", $lines);
    }
}

trait MessagesHelper
{
	private function registerECMessage(int $vid, int $currentStatus): int
	{
		if ($vid > 0) {
			if ($this->RegisterMessage($vid, VM_UPDATE)) {
				// Status auf 102 nur, wenn noch nicht 201 gesetzt
				if ($currentStatus !== 201) $currentStatus = 102;
			} else {
				$currentStatus = 201; // Priorität: Fehler
			}
		} 
		return $currentStatus;
	}

    private function unregisterAllECMessages(): bool
    {
        return $this->UnregisterMessage(0, 0);
    }

    private function readValuesFromSourceAndRegisterMessage(array $profiles, int $status): int
    {
		foreach ($profiles as $profile) {
            if ($this->GetECBuffer($profile) == '0') {
                // Keine Variable in der Source gefunden: Backup-Wert eintragen
                $this->SetECValue($profile, $this->readECBackupProperty($profile));
            } else {
                $status = $this->registerECMessage(intval($this->GetECBuffer($profile)), $status);
            }
        }
        return $status;
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
			if(isset(IPS_GetInstance($Device)["ConnectionID"]) && IPS_GetInstance($Device)["ConnectionID"] == $Gateway){
				$config = json_decode(IPS_GetConfiguration($Device));
				if(!property_exists($config, 'DeviceID'))continue;
				if(is_integer($config->DeviceID)) $DeviceArray[] = $config->DeviceID;
			}
		}	
		for($ID = 1; $ID<=256; $ID++)if(!in_array($ID,$DeviceArray))break;
		return $ID == 256?0:$ID;
	}

    private function ShowFormPopup(string $text): void
    {
        // 1) Caption des Labels im Popup setzen
        $this->UpdateFormField("EchoPopupMessage", "caption", $text);
        // 2) Popup öffnen
        $this->UpdateFormField("EchoPopup", "visible", true);
    } 

    private function FormatFoundVariables(array $foundVars): string
    {
        $lines = ["Source variables checked. Found variables:"];
        foreach ($foundVars as $idx => $var) {
            $lines[] = sprintf(
                "%d) %s (ID: %d)",
                $idx,
                $var['Ident'],
                $var['VarID']
            );
        }
        $lines[] = "\nPlease check the missing values and set them manually if needed.";
        return implode("\n", $lines);
    }
}

trait VariableHelper{
    use EnOceanConverterConstants;
    // -----------------------------------------------
    // Interne Funktionen für den Zugriff auf die Variablen
    private function getECValue(array $varIdent)               { return $this->GetValue(self::VAR_PREFIX . $varIdent['Ident']);}
    private function setECValue(array $varIdent, $value): void { $this->SetValue(self::VAR_PREFIX . $varIdent['Ident'], $value); }

    // -----------------------------------------------
    // Verwaltung der Variablen
    private function maintainECVariable(array $variable, int $position, bool $visible): void
    {
        $this->MaintainVariable(self::VAR_PREFIX . $variable['Ident'], $variable['Name'], $variable['Type'], $variable['Profile'], $position, $visible);
        $this->SendDebug(__FUNCTION__, "Variable: " . $variable['Ident'] . " angelegt/aktualisiert", 0);
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
                IPS_DeleteVariable($childID);
            }
        }
    }
    
    // -----------------------------------------------
    // Hilfsfunktionen für Backup-Variablen
    private function registerECBackupProperties(array $variables): void
    {
        foreach ($variables as $variable) {
            switch ($variable['Type']) {
                case VARIABLETYPE_BOOLEAN:
                    $this->RegisterPropertyBoolean(self::PROP_PREFIX_BACKUP . $variable['Ident'], (bool)$variable['BackupValue']);
                    break;
                case VARIABLETYPE_INTEGER:
                    $this->RegisterPropertyInteger(self::PROP_PREFIX_BACKUP . $variable['Ident'], (int)$variable['BackupValue']);
                    break;
                case VARIABLETYPE_FLOAT:
                    $this->RegisterPropertyFloat(self::PROP_PREFIX_BACKUP . $variable['Ident'], (float)$variable['BackupValue']);
                    break;
                case VARIABLETYPE_STRING:
                    $this->RegisterPropertyString(self::PROP_PREFIX_BACKUP . $variable['Ident'], (string)$variable['BackupValue']);
                    break;
                default:
                    $this->SendDebug(__FUNCTION__, "Unbekannter Variablentyp für Backup-Variable: " . $variable['Type'], 0);
                    break;
            }
        }
    }

    private function readECBackupProperty(array $variable): mixed
    {
        switch ($variable['Type']) {
            case VARIABLETYPE_BOOLEAN:
                return (bool)$this->ReadPropertyBoolean(self::PROP_PREFIX_BACKUP . $variable['Ident']);
            case VARIABLETYPE_INTEGER:
                return (int)$this->ReadPropertyInteger(self::PROP_PREFIX_BACKUP . $variable['Ident']);
            case VARIABLETYPE_FLOAT:
                return (float)$this->ReadPropertyFloat(self::PROP_PREFIX_BACKUP . $variable['Ident']);
            case VARIABLETYPE_STRING:
                return (string)$this->ReadPropertyString(self::PROP_PREFIX_BACKUP . $variable['Ident']);
            default:
                $this->SendDebug(__FUNCTION__, "Unbekannter Variablentyp für Backup-Variable: " . $variable['Type'], 0);
                return null;
        }
    }

    private function analyseSourceAndWriteIdToBuffers(int $sourceID): void
    {
		if ($sourceID > 1) { // 0=root, 1=none
			$variables = IPS_GetChildrenIDs($sourceID);
			foreach ($variables as $vid) {
				$vinfo = IPS_GetVariable($vid);
				// nach Profil erkennen
				if (str_contains(strtoupper($vinfo['VariableProfile']), '_PIRS') || str_contains(strtoupper($vinfo['VariableProfile']), 'MOTION') || str_contains(strtoupper($vinfo['VariableProfile']), 'PRESENCE')) {
					$this->SetECBuffer(self::EEP_VARIABLES[self::MOTION], (string)$vid);
				}
				if (str_contains(strtoupper($vinfo['VariableProfile']), '_ILL') || str_contains(strtoupper($vinfo['VariableProfile']), 'ILLUMINATION')) {
					$this->SetECBuffer(self::EEP_VARIABLES[self::ILLUMINATION], (string)$vid);
				}
				if (str_contains(strtoupper($vinfo['VariableProfile']), '_SVC') || str_contains(strtoupper($vinfo['VariableProfile']), 'VOLT')) {
					$this->SetECBuffer(self::EEP_VARIABLES[self::VOLTAGE], (string)$vid);
				}
				if (str_contains(strtoupper($vinfo['VariableProfile']), '_TMP') || str_contains(strtoupper($vinfo['VariableProfile']), 'TEMPERATURE')) {
					$this->SetECBuffer(self::EEP_VARIABLES[self::TEMPERATURE], (string)$vid);
				}
				if (str_contains(strtoupper($vinfo['VariableProfile']), '_HUM') || str_contains(strtoupper($vinfo['VariableProfile']), 'HUMIDITY')) {
					$this->SetECBuffer(self::EEP_VARIABLES[self::HUMIDITY], (string)$vid);
				}
			}
		}
    }

    // -----------------------------------------------
    // Hilfsfunktionen für User-Infos
	private function CheckSourceVariables(string $propertySourceDevice, string $propertyTargetEEP): void
	{
		$sourceID = $this->ReadPropertyInteger($propertySourceDevice);
		if ($sourceID == 0) {
			return;
		}
		$this->analyseSourceAndWriteIdToBuffers($sourceID);
		// Popup mit gefundenen Variablen anzeigen
		$foundVars = $this->GetActiveVariables($this->ReadPropertyString($propertyTargetEEP));
		$this->ShowFormPopup($this->FormatFoundVariables($foundVars));
	}

	private function ShowEepDefinition(string $propertyTargetEEP): void
	{
        // Popup mit EEP-Definition anzeigen
        $this->ShowFormPopup(EEPConverter::FormatEepProfile($propertyTargetEEP));
	}
}

trait BufferHelper{
    use EnOceanConverterConstants;

    // Einheitliches Prefix für alle Variablen:
    private const BUFFER_PREFIX = "buffer";

    private function getECBuffer(array $buffer): string                     { return $this->GetBuffer(self::BUFFER_PREFIX . $buffer['Ident']);}
    private function setECBuffer(array $buffer, string $value = "0"): void  { $this->SetBuffer(self::BUFFER_PREFIX . $buffer['Ident'], $value);}

    private function maintainECBuffers(array $buffers): void
    {
        foreach ($buffers as $buffer) {
            $this->setECBuffer($buffer);
        }
    }

    private function checkECBuffersVsEepProfile(string $eepProfile): array
    {
        $result = [];
        // Vorbelegen mit allen bekannten Variablen aus EEP_VARIABLES, damit Formularfelder auch ausgeblendet werden, wenn das EEP Profil gar keine entsprechende Variable kennt
        // Wir tun so, als ob alle Variablen gefunden wurden (kein Backup nötig)
        foreach (self::EEP_VARIABLES as $ident => $def) {
            $result[$ident] = true;
        }
        // Dann prüfen wir alle Variablen, die das Profil benötigt gegen die, die gefunden wurden und im Buffer stehen
        $variables = self::EEP_VARIABLE_PROFILES[$eepProfile];
        foreach ($variables as $variableDef) {
            // Wenn der Buffer = 0 ist, wurde keine Variable gefunden und ein Backup-Feld soll angezeigt werden
            $ident = $variableDef['Ident'];
            $bufferValue = $this->getECBuffer(self::EEP_VARIABLES[$ident]);
            $result[$ident] = ($bufferValue !== "0");
        }
        return $result;
    }

    private function GetActiveVariables(string $eepProfile): array
    {
        $result = [];
        $index  = 1;
        // alle Variablen, die zu diesem Profil gehören
        if (!isset(self::EEP_VARIABLE_PROFILES[$eepProfile])) {
            return $result;
        }
        foreach (self::EEP_VARIABLE_PROFILES[$eepProfile] as $variableDef) {
            $bufferValue = $this->GetECBuffer($variableDef);
            // nur wenn Buffer != "0"
            if ($bufferValue !== "0" && $bufferValue !== null) {
                $result[$index++] = [
                    "Ident" => $variableDef['Ident'],
                    "VarID" => $bufferValue
                ];
            }
        }
        return $result;
    }
}