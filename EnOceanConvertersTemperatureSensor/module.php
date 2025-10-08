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
use EnOceanConverter\GUIDs;

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
		$this->RegisterPropertyString("TargetDeviceID", "EC:00:C8:01");

		$this->MaintainVariable("Temperature", "Temperatur", VARIABLETYPE_FLOAT, "~Temperature", 1, true);
		$this->MaintainVariable('Humidity', 'Luftfeuchtigkeit', VARIABLETYPE_FLOAT, '~Humidity.F', 2, true);
        // Timer für verzögertes Senden (2s nach letztem Update)
		$this->RegisterTimer("ECTSSendDelayed" . $this->InstanceID, 2 * 1000, 'IPS_RequestAction(' . $this->InstanceID . ', "SendTelegramDelayed", true);');

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
		//$this->SendDebug(__FUNCTION__, 'ApplyChanges: SourceDevice=' . $sourceID, 0);

		if ($sourceID > 1) { // 0=root, 1=none
			$variables = IPS_GetChildrenIDs($sourceID);
			foreach ($variables as $vid) {
				$vinfo = IPS_GetVariable($vid);
				// z.B. nach Profil erkennen
				if ($vinfo['VariableProfile'] === '~EEP_A50403_TMP' || $vinfo['VariableProfile'] === '~EEP_A50402_TMP' || $vinfo['VariableProfile'] === '~EEP_A50401_TMP' || $vinfo['VariableProfile'] === '~Temperature') {
					$this->SetBuffer('SourceVarTemp', (string)$vid);
				}
				if ($vinfo['VariableProfile'] === '~EEP_A50403_HUM' || $vinfo['VariableProfile'] === '~EEP_A50402_HUM' || $vinfo['VariableProfile'] === '~EEP_A50401_HUM' || $vinfo['VariableProfile'] === '~Humidity.F' || $vinfo['VariableProfile'] === '~Humidity') {
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

    public function RequestAction(string $ident, mixed $value): void
    {
		$this->SendDebug(__FUNCTION__, 'RequestAction: ' . $ident . ' → ' . print_r($value, true), 0);
        switch($ident) {
			case 'SendTelegramDelayed':
				$this->SendTelegramDelayed();
				break;
			case 'SendTeachIn':
				$this->sendTeachInTelegram();
				break;
			case 'sendTestTelegram':
				$this->SendDebug(__FUNCTION__, 'Send Test Telegram', 0);
				$this->sendTestTelegram();
				break;
			case 'selectAvailableDeviceId':
				$this->selectAvailableDeviceId();
				break;
            default:
                parent::RequestAction($ident, $value);
        }
    }

	/**
	 * Sendet ein Test-Telegramm (Temp=20°C, Hum=50%)
	 */
	public function sendTestTelegram(): void
	{
		$temp = 18.7;   // °C
		$hum  = 78.1;   // %
		$this->SendDebug(__FUNCTION__, "sending test: temp=" . $temp . ", hum=" . $hum, 0);
		$this->SendEnOceanTelegram($temp, $hum, false);
	}

	/**
	 * Sendet ein Teach-in-Telegramm
	 */
	public function sendTeachInTelegram(): void
	{
		$temp = 18.6;   // °C
		$hum  = 68.1;   // %
		$this->SendDebug(__FUNCTION__, "sending teach-in with: temp=" . $temp . ", hum=" . $hum, 0);
		$this->SendEnOceanTelegram($temp, $hum, true);
	}

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data): void
    {
		$senderIdInt = (int)$SenderID;
		$tempVarId   = (int)$this->GetBuffer('SourceVarTemp'); // vorher per SetBuffer gespeichert
		$humVarId    = (int)$this->GetBuffer('SourceVarHum');
		$this->SendDebug(__FUNCTION__, "sender={$senderIdInt} (tempVar={$tempVarId}, humVar={$humVarId}) with DATA-0: " . print_r($Data[0], true), 0);
		// Save received values in own variables
		if ($Message == VM_UPDATE) {
			$value = $Data[0];
			$sourceProfile = $this->ReadPropertyString('SourceEEP');
			// unterscheiden: kommt Wert aus Temp- oder Humidity-Quelle?
			if ($senderIdInt === $tempVarId) {
				$this->SetValue('Temperature', (float)$value);
			}
			// Falls Update der Humidity-Variable
			if ($senderIdInt === $humVarId) {
				$this->SetValue('Humidity', (float)$value);
			}
			// Timer setzen (5 Sekunden warten, dann send)
            $this->SetTimerInterval("ECTSSendDelayed" . $this->InstanceID, 2 * 1000);
		}
    }

	public function SendTelegramDelayed()
	{
		$this->SetTimerInterval("ECTSSendDelayed" . $this->InstanceID, 0); // Timer wieder stoppen

		$temp = $this->GetValue('Temperature');
		$hum  = $this->GetValue('Humidity');
		$this->SendDebug(__FUNCTION__, 'Timestamps: temp=' . $temp . ', hum=' . $hum, 0);

		// Senden (deine vorhandene Funktion)
		$this->SendEnOceanTelegram($temp, $hum);
	}

	private function isSocketActive(): bool
	{
		$parentID = @IPS_GetInstance($this->InstanceID)['ConnectionID'];
		if ($parentID > 0) {
			$status = IPS_GetInstance($parentID)['InstanceStatus'];
			return $status == IS_ACTIVE; // 102 = aktiv
		}
		return false;
	}

	// SendEnOceanTelegram: sanitizing device id + sauberes Packing mit encode*-Funktionen
	private function SendEnOceanTelegram(float $temperature, float $humidity, bool $teachIn = false): void
	{
		if (!$this->isSocketActive()) {
			$this->SendDebug(__FUNCTION__, 'Socket nicht verbunden oder nicht aktiv - Telegramm nicht gesendet.', 0);
			return;
		}

		$targetEEP  = $this->ReadPropertyString('TargetEEP');
		$deviceID   = $this->ReadPropertyString('TargetDeviceID'); // z. B. "EC:00:A5:01" oder "EC00A501"

		// -> benutze die vorhandenen encode-Funktionen, um die RAW-Integer zu bekommen
		try {
			$rawTemp = $this->encodeTemperature($targetEEP, $temperature); // z.B. 0..255 oder 0..1023 oder 0..4095
			$rawHum  = $this->encodeHumidity($targetEEP, $humidity);       // z.B. 0..255 oder 0..127
		} catch (\Exception $e) {
			$this->SendDebug(__FUNCTION__, 'Encode Fehler: ' . $e->getMessage(), 0);
			return;
		}

		// Default DBs
		$DB0 = 0x0F; // Status-Byte = Datentelegramm
		if ($teachIn) {
			$DB0 = 0x00; // Teach-in
		}
		$DB1 = 0;
		$DB2 = 0;
		$DB3 = 0;

		switch ($targetEEP) {
			case EEPProfiles::A5_04_01: // 8 Bit Temp, 8 Bit Hum
			case EEPProfiles::A5_04_02: // 8 Bit Temp, 8 Bit Hum
				$DB1 = ((int)$rawTemp) & 0xFF;
				$DB2 = ((int)$rawHum) & 0xFF;
				break;

			case EEPProfiles::A5_04_03: // 10 Bit Temp, 7 Bit Hum
				$rawTempFull = (int)$rawTemp; // 0..1023
				$rawHum7     = (int)$rawHum;  // 0..127
				$DB3 = $rawTempFull & 0xFF;                // low 8 bits
				$upper2 = ($rawTempFull >> 8) & 0x03;      // upper 2 bits (0..3)
				// DB2: bits 0..6 = humidity (7 bit), bits 7..6 = upper2  -> shift left by 6
				$DB2 = ($rawHum7 & 0x7F) | (($upper2 & 0x03) << 6);
				break;

			case EEPProfiles::A5_04_04: // 12 Bit Temp, 8 Bit Hum
				$rawTemp12 = (int)$rawTemp; // 0..4095
				$DB3 = $rawTemp12 & 0xFF;            // lower 8 bit
				$DB2 = ((int)$rawHum) & 0xFF;        // humidity full 8 bit
				$DB1 = ($rawTemp12 >> 8) & 0x0F;     // upper 4 bit of 12-bit temp into DB1
				break;

			default:
				$this->SendDebug(__FUNCTION__, 'Unknown TargetEEP: ' . $targetEEP, 0);
				return;
		}

		// Device ID: robust parsen (alle non-hex löschen, dann in 2er-chunks splitten)
		$clean = preg_replace('/[^0-9A-Fa-f]/', '', (string)$deviceID);
		if ($clean === '') {
			$this->SendDebug(__FUNCTION__, 'DeviceID leer oder nicht hex: ' . $deviceID, 0);
			return;
		}
		// evtl. führende 0 ergänzen, falls ungerade Anzahl Ziffern
		if (strlen($clean) % 2 !== 0) {
			$clean = '0' . $clean;
		}
		$chunks = str_split($clean, 2);
		$idBytes = array_map('hexdec', $chunks);

		// Wir brauchen genau 4 Bytes: wenn mehr -> rechte (letzte) 4, wenn weniger -> links mit 0 auffüllen
		if (count($idBytes) > 4) {
			$idBytes = array_slice($idBytes, -4);
		}
		while (count($idBytes) < 4) {
			array_unshift($idBytes, 0x00);
		}

		// Data Block (ERP1 4BS): RORG + DB3..DB0 + Sender-ID(4) + Status
		$data = [
			0xA5, // RORG 4BS
			(int)$DB3, (int)$DB2, (int)$DB1, (int)$DB0,
			(int)$idBytes[0], (int)$idBytes[1], (int)$idBytes[2], (int)$idBytes[3],
			0x00  // status
		];
		// OptData
		$optData = [0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0x00];
		$type = 0x01;                    // RPS/4BS telegram
		$dataLength = count($data);      // 10
	    // Data Length in 2 Bytes (Big Endian)
		$dataLen2Bytes = [($dataLength >> 8) & 0xFF, $dataLength & 0xFF];
		$optLength  = count($optData);   // 7
		// Header
		$header = [$dataLen2Bytes[0], $dataLen2Bytes[1], $optLength, $type];
		//$header = [0x00, 0x0A, 0x07, 0x01];
		$headerCRC8 = CRC8::calculate($header);
		// Telegram zusammensetzen
		$telegram = array_merge([0x55], $header, [$headerCRC8], $data, $optData);
		//$telegram[] = CRC8::crc8(array_merge($data, $optData));
		$telegram[] = CRC8::calculate(array_merge($data, $optData));
		// Telegram-Bytes in Binärstring packen
		$this->SendDebug(__FUNCTION__, 'Telegram array: ' . implode(' ', array_map(fn($b)=>sprintf('%02X',$b), $telegram)), 0);
		//$this->SendDebug(__FUNCTION__, 'Header CRC=' . sprintf('%02X', $headerCRC8), 0);
		//$this->SendDebug(__FUNCTION__, 'Data CRC=' . sprintf('%02X', end($telegram)), 0);
		$binaryData = pack('C*', ...$telegram);
		//$this->SendDebug(__FUNCTION__, 'Binary length: ' . strlen($binaryData), 0);
		$parentID = @IPS_GetInstance($this->InstanceID)['ConnectionID'];
		if ($parentID > 1) {
			$data = [
				'DataID' => GUIDs::DATAFLOW_TRANSMIT,
				'Buffer' => bin2hex($binaryData) 
			];
			$this->SendDataToParent(json_encode($data));
			//CSCK_SendText($parentID, $binaryData);
			$this->SendDebug(__FUNCTION__, 'Sent Telegram to Socket (len='.strlen($binaryData).')', 0);
		} else {
			$this->SendDebug(__FUNCTION__, 'Kein Parent verbunden!', 0);
		}
	}

	function decodeTemperature(string $profile, $raw): float {
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

	function decodeHumidity(string $profile, $raw): float {
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

	function encodeTemperature(string $profile, float $temperature): int {
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
				throw new Exception("Unbekanntes EEP Profil: $profile");
		}
	}

	function encodeHumidity(string $profile, float $humidity): int {
		switch ($profile) {
			case EEPProfiles::A5_04_01:
			case EEPProfiles::A5_04_02:
			case EEPProfiles::A5_04_04:
				return (int)round($humidity * 255 / 100);
			case EEPProfiles::A5_04_03:
				return (int)round($humidity * 127 / 100);
			default:
				throw new Exception("Unbekanntes EEP Profil: $profile");
		}
	}

	public function GetConfigurationForm(): string {
        // 5. HTML Template laden & Platzhalter ersetzen
        $form = file_get_contents(__DIR__ . '/form.json');
        // Unterstützte Devices einfügnen
		$validModules = GUIDs::allTemperatureIpsGuids();
		$form = str_replace('<!---VALID_MODULES-->', json_encode($validModules), $form);
		$this->SendDebug(__FUNCTION__, 'GetConfigurationForm: ' . $form, 0);
		return $form;
	}

}