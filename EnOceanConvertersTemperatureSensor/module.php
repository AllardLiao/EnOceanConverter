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

		$this->RegisterPropertyString("SourceEEP", "A5-04-03");
		$this->RegisterPropertyBoolean("AutoDetectEEP", true);
		$this->RegisterPropertyInteger("SourceDevice", 0);
		$this->RegisterPropertyString("TargetEEP", "A5-04-01");
		$this->RegisterPropertyBoolean("ResendActive", false);
		$this->RegisterPropertyInteger("DeviceID", 0);

		$this->SetBuffer("SourceVarTemp", "0");
		$this->SetBuffer("SourceVarHum", "0");

		$this->MaintainVariable("Temperature", "Temperatur", VARIABLETYPE_FLOAT, "~Temperature", 1, true);
		$this->MaintainVariable('Humidity', 'Luftfeuchtigkeit', VARIABLETYPE_FLOAT, '~Humidity.F', 2, true);

		$this->RegisterTimer("ECTSSendDelayed" . $this->InstanceID, 0, 'IPS_RequestAction(' . $this->InstanceID . ', "SendTelegramDelayed", true);');

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
		// Listener für Temp Variable
		if ($sourceIDTemp > 0) {
			if ($this->RegisterMessage($sourceIDTemp, VM_UPDATE)) {
				$this->SendDebug('RegisterMessage', 'Temp variable registered: ' . $sourceIDTemp, 0);
				$status = 102; // Verbindung erfolgreich
			} else {
				$this->SendDebug('RegisterMessage', 'Failed to register Temp variable: ' . $sourceIDTemp, 0);
				$status = 201; 
			}
		} else {
			$this->SendDebug('RegisterMessage', 'Temp variable ID not set', 0);
		}
		// Listener für Humidity Variable
		if ($sourceIDHum > 0) {
			if ($this->RegisterMessage($sourceIDHum, VM_UPDATE)) {
				$this->SendDebug('RegisterMessage', 'Humidity variable registered: ' . $sourceIDHum, 0);
				// Status auf 102 nur, wenn noch nicht 201 gesetzt
				if ($status !== 201) $status = 102;
			} else {
				$this->SendDebug('RegisterMessage', 'Failed to register Humidity variable: ' . $sourceIDHum, 0);
				$status = 201; 
			}
		} else {
			$this->SendDebug('RegisterMessage', 'Humidity variable ID not set', 0);
		}
		// Status setzen
		if ($status == 102) {
			if (!$this->ReadPropertyBoolean('ResendActive')) {
				$this->SetStatus(104); // 104 = Quelle verbunden, aber kein Resend
				return;
			}
		}
		$this->SetStatus($status);
	}

    public function RequestAction(string $ident, mixed $value): void
    {
		//$this->SendDebug(__FUNCTION__, 'RequestAction: ' . $ident . ' → ' . print_r($value, true), 0);
        switch($ident) {
			case 'SendTelegramDelayed':
				$this->SendTelegramDelayed();
				break;
			case 'SendTeachIn':
				$this->sendTeachInTelegram();
				break;
			case 'sendTestTelegram':
				$this->sendTestTelegram();
				break;
			case "selectFreeDeviceID":
				$this->UpdateFormField('DeviceID', 'value', $this->selectFreeDeviceID());
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
		$temp = $this->GetValue("Temperature");   // °C
		$hum  = $this->GetValue("Humidity");   // %
		$this->UpdateFormField('ResultSendTest', 'caption', 'Send test telegram (Temp=' . $temp . '°C, Hum=' . $hum . '%)');
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
		$this->UpdateFormField('ResultTeachIn', 'caption', 'Send teach-in telegram (Temp=' . $temp . '°C, Hum=' . $hum . '%)');
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
			// unterscheiden: kommt Wert aus Temp- oder Humidity-Quelle?
			if ($senderIdInt === $tempVarId) {
				$this->SetValue('Temperature', (float)$value);
			}
			// Falls Update der Humidity-Variable
			if ($senderIdInt === $humVarId) {
				$this->SetValue('Humidity', (float)$value);
			}
			// Timer setzen (2 Sekunden warten, dann send) - verhindert das doppelte Senden des Telegramms, wenn beide Variablen fast gleichzeitig aktualisiert werden
            if ($this->ReadPropertyBoolean("ResendActive")) {
				$this->SetTimerInterval("ECTSSendDelayed" . $this->InstanceID, 2 * 1000);
			}
		}
    }

	public function SendTelegramDelayed()
	{
		$this->SetTimerInterval("ECTSSendDelayed" . $this->InstanceID, 0); // Timer wieder stoppen
		$temp = $this->GetValue('Temperature');
		$hum  = $this->GetValue('Humidity');
		$this->SendDebug(__FUNCTION__, 'Send telegram for ' . $this->InstanceID . '/' . $this->ReadPropertyInteger("DeviceID") . ': temp=' . $temp . ', hum=' . $hum, 0);
		$this->SendEnOceanTelegram($temp, $hum);
	}

	private function isGatewayActive(): bool
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
		if (!$this->isGatewayActive()) {
			$this->SendDebug(__FUNCTION__, 'Gateway nicht verbunden oder nicht aktiv - Telegramm nicht gesendet.', 0);
			return;
		}

		$targetEEP  = $this->ReadPropertyString('TargetEEP');

		// Parameter in Ziel-Protokoll umwandeln
		try {
			$rawTemp = EEPConverter::encodeTemperature($targetEEP, $temperature);
			$rawHum  = EEPConverter::encodeHumidity($targetEEP, $humidity);
		} catch (\Exception $e) {
			$this->SendDebug(__FUNCTION__, 'Encode Fehler: ' . $e->getMessage(), 0);
			return;
		}

		$data = EEPProfiles::gatewayBaseData();
		$data['DeviceID'] = $this->ReadPropertyInteger("DeviceID");
		if ($teachIn) {
			$data['DataByte0'] = 0x00; // Status-Byte = Teach-in
		} else {
			$data['DataByte0'] = 0x0F; // Status-Byte = Datentelegramm
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
		$data['DataByte1'] = $DB1;
		$data['DataByte2'] = $DB2;
		$data['DataByte3'] = $DB3;

		try {
			@$this->SendDataToParent(json_encode($data));
		} catch (Exception $e) {
			$this->SendDebug(__FUNCTION__, 'Error sending data to parent: ' . $e->getMessage(), 0);
		}
		$this->SendDebug(__FUNCTION__, 'Sent telegram to gateway (' . print_r($data, true) . ')', 0);
	}

	public function GetConfigurationForm(): string {
        // 5. HTML Template laden & Platzhalter ersetzen
        $form = file_get_contents(__DIR__ . '/form.json');
        // Unterstützte Devices einfügnen
		$validModules = GUIDs::allTemperatureIpsGuids();
		$form = str_replace('<!---VALID_MODULES-->', json_encode($validModules), $form);
		//$this->SendDebug(__FUNCTION__, 'GetConfigurationForm: ' . $form, 0);
		return $form;
	}

	protected function selectFreeDeviceID()
	{
		$Gateway = @IPS_GetInstance($this->InstanceID)["ConnectionID"];
		if($Gateway == 0) return;
		$Devices = IPS_GetInstanceListByModuleType(3);             # alle Geräte
		$DeviceArray = array();
		foreach ($Devices as $Device){
			if(IPS_GetInstance($Device)["ConnectionID"] == $Gateway){
				$config = json_decode(IPS_GetConfiguration($Device));
				if(!property_exists($config, 'DeviceID'))continue;
				if(is_integer($config->DeviceID)) $DeviceArray[] = $config->DeviceID;
			}
		}	
		for($ID = 1; $ID<=256; $ID++)if(!in_array($ID,$DeviceArray))break;
		return $ID == 256?0:$ID;
	}
}