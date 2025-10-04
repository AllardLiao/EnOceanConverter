<?php

declare(strict_types=1);

// IPS-Stubs nur in der Entwicklungsumgebung laden

if (substr(__DIR__,0, 10) == "/Users/kai") {
    // Development
	include_once __DIR__ . '/../.ips_stubs/autoload.php';
}

use EnOceanConverter\EEPProfiles;
use EnOceanConverter\EEPConverter;
use EnOceanConverter\CRC8;	

/**
 * Include Controme helper classes.
 */
require_once __DIR__ . '/../libs/EnOceanConverterConstants.php';
require_once __DIR__ . '/../libs/EnOceanConverterHelper.php';

class EnOceanConvertersTemperatureSensor extends IPSModuleStrict
{
	public function Create():void
	{
		//Never delete this line!
		parent::Create();

		$this->RegisterPropertyString("SourceEEP", "1");
		$this->RegisterPropertyBoolean("AutoDetectEEP", true);
		$this->RegisterPropertyInteger("SourceDevice", 0);
		$this->SetBuffer("SourceVarTemp", "0");
		$this->SetBuffer("SourceVarHum", "0");
		$this->RegisterPropertyString("TargetEEP", "2");
		$this->RegisterPropertyBoolean("ResendActive", false);
		$this->RegisterPropertyString("TargetDeviceID", "EC-00-A5-01");

		$this->SetStatus(104);
	}

	public function Destroy():void
	{
		//Never delete this line!
		parent::Destroy();
	}

	public function ApplyChanges():void
	{
		//Never delete this line!
		parent::ApplyChanges();
		
        // Alte Nachrichten abmelden
        $this->UnregisterMessage(0, 0);

        $sourceID = $this->ReadPropertyInteger('SourceDevice');

		if ($sourceID > 0) {
			$variables = IPS_GetVariableList($sourceID);
			foreach ($variables as $vid) {
				$vinfo = IPS_GetVariable($vid);
				// z.B. nach Profil erkennen
				if ($vinfo['VariableProfile'] === 'Temperature') {
					$this->SetBuffer('SourceVarTemp', (string)$vid);
				}
				if ($vinfo['VariableProfile'] === 'Humidity') {
					$this->SetBuffer('SourceVarHum', (string)$vid);
				}
			}
		}

		$sourceIDTemp = intval($this->GetBuffer('SourceVarTemp'));
		$sourceIDHum  = intval($this->GetBuffer('SourceVarHum'));

		$status = 104; // Standard: Quelle nicht gesetzt

		// Temp Variable
		if ($sourceIDTemp > 0) {
			if ($this->RegisterMessage($sourceIDTemp, VM_UPDATE)) {
				$this->SendDebug('RegisterMessage', 'Temp variable registered: ' . $sourceIDTemp, 0);
				$status = 102; // Verbindung erfolgreich
			} else {
				$this->SendDebug('RegisterMessage', 'Failed to register Temp variable: ' . $sourceIDTemp, 0);
				$status = 201; // Keine Verbindung
			}
		} else {
			$this->SendDebug('RegisterMessage', 'Temp variable ID not set', 0);
		}

		// Humidity Variable
		if ($sourceIDHum > 0) {
			if ($this->RegisterMessage($sourceIDHum, VM_UPDATE)) {
				$this->SendDebug('RegisterMessage', 'Humidity variable registered: ' . $sourceIDHum, 0);
				// Status auf 102 nur, wenn noch nicht 201 gesetzt
				if ($status !== 201) $status = 102;
			} else {
				$this->SendDebug('RegisterMessage', 'Failed to register Humidity variable: ' . $sourceIDHum, 0);
				$status = 201; // Priorität: Fehler
			}
		} else {
			$this->SendDebug('RegisterMessage', 'Humidity variable ID not set', 0);
		}

		// Status setzen
		$this->SetStatus($status);
	}

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data): void
    {
        if ($Message == VM_UPDATE) {
            // Wert aus der Quellinstanz holen
            $value = $Data[0];

            $sourceProfile = $this->ReadPropertyString('SourceEEP');
            $targetProfile = $this->ReadPropertyString('TargetEEP');

            // Konvertierung je nach Typ
            $convertedTemp = $this->encodeTemperature($targetProfile, $this->decodeTemperature($sourceProfile, (float)$value));
            $convertedHum  = $this->encodeHumidity($targetProfile, $this->decodeHumidity($sourceProfile, (float)$value));

            $this->SendEnOceanTelegram($convertedTemp, $convertedHum);
        }
    }

	private function sendEnOceanTelegram(float $temp, float $hum): void
	{
		if (!$this->ReadPropertyBoolean('ResendActive')) {
			return;
		}
		$profile = $this->ReadPropertyString('TargetEEP');
		$deviceId = $this->ReadPropertyString('TargetDeviceID');

		// Umrechnung der Temperatur- und Feuchtigkeitswerte in Rohwerte
		$rawTemp = $this->encodeTemperature($profile, $temp);
		$rawHum = $this->encodeHumidity($profile, $hum);

		// Bestimmung der Datenbytes je nach Profil
		switch ($profile) {
			case 'A5-04-01':
			case 'A5-04-02':
				$db0 = 0;
				$db1 = $rawHum;
				$db2 = $rawTemp;
				$db3 = 0;
				break;
			case 'A5-04-03':
				$db0 = ($rawTemp >> 2) & 0xFF;
				$db1 = (($rawTemp & 0x03) << 6) | (($rawHum >> 7) & 0x03);
				$db2 = ($rawHum >> 2) & 0xFF;
				$db3 = 0;
				break;
			case 'A5-04-04':
				$db0 = ($rawTemp >> 4) & 0xFF;
				$db1 = (($rawTemp & 0x0F) << 4) | (($rawHum >> 4) & 0x0F);
				$db2 = ($rawHum << 4) & 0xF0;
				$db3 = 0;
				break;
			default:
				$this->SendDebug('sendEnOceanTelegram', 'Unbekanntes Profil: ' . $profile, 0);
				return;
		}
		// Erstellung des Telegramms
		$telegram = sprintf(
			"A5 04 04 %02X %02X %02X %02X %s 00",
			$db0, $db1, $db2, $db3, $deviceId
		);
		// Berechnung der CRC8-Prüfziffer
		$crc = CRC8::calculate($telegram);
		// Zusammenstellung des vollständigen Telegramms
		$sendText = strtoupper("55 " . CRC8::getHeader(strlen($telegram), strlen($crc)) . " " . $telegram . " " . $crc);
		// Senden des Telegramms
		$socketID = $this->ReadPropertyInteger('ClientSocketID');
		if ($socketID > 0) {
			//CSCK_SendText($socketID, $sendText);
			$this->SendDebug('sendEnOceanTelegram', 'Telegram gesendet: ' . $sendText, 0);
		} else {
			$this->SendDebug('sendEnOceanTelegram', 'Kein Client Socket konfiguriert', 0);
		}
	}

	function decodeTemperature($profile, $raw) {
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

	function decodeHumidity($profile, $raw) {
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

	function encodeTemperature($profile, float $temperature): int {
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
				throw new \Exception("Unbekanntes Profil: $profile");
		}
	}

	function encodeHumidity($profile, float $humidity): int {
		switch ($profile) {
			case EEPProfiles::A5_04_01:
			case EEPProfiles::A5_04_02:
			case EEPProfiles::A5_04_04:
				return (int)round($humidity * 255 / 100);
			case EEPProfiles::A5_04_03:
				return (int)round($humidity * 127 / 100);
			default:
				throw new \Exception("Unbekanntes Profil: $profile");
		}
	}
}