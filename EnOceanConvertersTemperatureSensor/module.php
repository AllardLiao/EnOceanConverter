<?php

declare(strict_types=1);

// IPS-Stubs nur in der Entwicklungsumgebung laden

if (substr(__DIR__,0, 10) == "/Users/kai") {
    // Development
	include_once __DIR__ . '/../.ips_stubs/autoload.php';
}
use EnOceanConverter\EEPProfiles;
use EnOceanConverter\EEPConverter;
use EnOceanConverter\GUIDs;
/**
 * Include Controme helper classes.
 */
require_once __DIR__ . '/../libs/EnOceanConverterConstants.php';
require_once __DIR__ . '/../libs/EnOceanConverterHelper.php';

class EnOceanConvertersTemperatureSensor extends IPSModuleStrict
{
	use EnOceanConverter\MessagesHelper;
	use EnOceanConverter\VariableHelper;
	use EnOceanConverter\BufferHelper;
	use EnOceanConverter\DeviceIDHelper;
	use EnOceanConverter\EnOceanConverterConstants;

	private const propertyDeviceID = "DeviceID";
	private const propertySourceDevice = "SourceDevice";
	private const propertyTargetEEP = "TargetEEP";
	private const propertyResendActive = "ResendActive";

	private const timerPrefix = "ECTSSendDelayed";

	public function Create():void
	{
		//Never delete this line!
		parent::Create();
		// Modul-Properties anlegen
		$this->RegisterPropertyInteger(self::propertySourceDevice, 0);
		$this->RegisterPropertyString(self::propertyTargetEEP, EEPProfiles::A5_04_01);
		$this->RegisterPropertyBoolean(self::propertyResendActive, false);
		$this->RegisterPropertyInteger(self::propertyDeviceID, 0);
		//Alle Backup-Werte als Property anlegen
		$this->registerECBackupProperties(self::EEP_VARIABLES);
		// Alle Buffer vorbelegen
		$this->maintainECBuffers(self::EEP_VARIABLES);
		// Die benötigten Variablen (initial) anlegen
		$this->MaintainECVariables(self::EEP_VARIABLE_PROFILES[$this->ReadPropertyString(self::propertyTargetEEP)]); // immer alle anlegen
		// Timer für verzögertes Senden anlegen
		$this->RegisterTimer(self::timerPrefix . $this->InstanceID, 0, 'IPS_RequestAction(' . $this->InstanceID . ', "SendTelegramDelayed", true);');
		// Standard-Status (104 = Quelle nicht verbunden)
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
		// Standard-Status (104 = Quelle nicht verbunden)
		$status = 104; // Standard: Quelle nicht gesetzt
		// Alle Buffer vorbelegen
		$this->maintainECBuffers(self::EEP_VARIABLES);
		// Variablen anlegen/löschen je nach ausgewähltem EEP
		$this->MaintainECVariables(self::EEP_VARIABLE_PROFILES[$this->ReadPropertyString(self::propertyTargetEEP)]); // immer alle anlegen
        // Quelle auslesen
        $sourceID = $this->ReadPropertyInteger(self::propertySourceDevice);
		// Variablen der Source-Instanz durchsuchen und passenden Buffer setzen
		$this->analyseSourceAndWriteIdToBuffers($sourceID);
		// Alte Nachrichten abmelden
        $this->unregisterAllECMessages();
		// Gesuchte Werte (target EEP) in Variablen schreiben und gleichzeitig Messages registrieren
		// Dabei auch Backup-Werte setzen, wenn keine Variable in der Source gefunden wurde
		$status = $this->readValuesFromSourceAndRegisterMessage(self::EEP_VARIABLE_PROFILES[$this->ReadPropertyString(self::propertyTargetEEP)], $status);
		// Status setzen
		if ($status == 102) {
			if (!$this->ReadPropertyBoolean('ResendActive')) {
				$this->SetStatus(104); // 104 = Quelle verbunden, aber kein Resend aktiv
				return;
			}
		}
		$this->SetStatus($status);
	}

	public function RequestAction(string $ident, mixed $value): void
    {
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
			case "checkSourceVariables":
				$this->CheckSourceVariables(self::propertySourceDevice, self::propertyTargetEEP);
				break;
			case "showEepDefinition":
				$this->ShowEepDefinition($this->ReadPropertyString(self::propertyTargetEEP));
				break;
            default:
                parent::RequestAction($ident, $value);
        }
    }

	/**
	 * Sendet ein Test-Telegramm mit den aktuellen Werten der Quell-Variablen
	 */
	public function sendTestTelegram(): void
	{
		if ($this->ReadPropertyInteger(self::propertyDeviceID) == 0) {
			$this->UpdateFormField('ResultSendTest', 'caption', 'Please select Device ID first!');
			return;
		}
		$temp = $this->GetECValue(self::EEP_VARIABLES[self::TEMPERATURE]);   // °C
		$hum  = $this->GetECValue(self::EEP_VARIABLES[self::HUMIDITY]);   // %
		// Default-Werte, falls Variable nicht benötigt wird für gewähltes EEP (dann gibt es auch keinen Backup und der Wert wird bei Senden ignoriert)
		if (!is_float($hum)) {
			$hum = 0.0;
		}
		$this->UpdateFormField('ResultSendTest', 'caption', 'Send test telegram (Temp=' . $temp . '°C, Hum=' . $hum . '%)');
		$this->SendDebug(__FUNCTION__, "sending test: temp=" . $temp . ", hum=" . $hum, 0);
		$this->SendEnOceanTelegram($temp, $hum, false);
	}

	/**
	 * Sendet ein Teach-in-Telegramm mit Temperatur 18,6°C und Luftfeuchtigkeit 68,1%
	 */
	public function sendTeachInTelegram(): void
	{
		if ($this->ReadPropertyInteger(self::propertyDeviceID) == 0) {
			$this->UpdateFormField('ResultTeachIn', 'caption', 'Please select Device ID first!');
			return;
		}
		$temp = 18.6;   // °C
		$hum  = 68.1;   // %
		$this->UpdateFormField('ResultTeachIn', 'caption', 'Send teach-in telegram (with Temp=' . $temp . '°C, Hum=' . $hum . '%)');
		$this->SendDebug(__FUNCTION__, "sending teach-in with: temp=" . $temp . ", hum=" . $hum, 0);
		$this->SendEnOceanTelegram($temp, $hum, true);
	}

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data): void
    {
		$senderIdInt = (int)$SenderID;
		$tempVarId   = (int)$this->GetECBuffer(self::EEP_VARIABLES[self::TEMPERATURE]);
		$humVarId    = (int)$this->GetECBuffer(self::EEP_VARIABLES[self::HUMIDITY]);
		$this->SendDebug(__FUNCTION__, "sender={$senderIdInt} (tempVar={$tempVarId}, humVar={$humVarId}) with DATA-0: " . print_r($Data[0], true), 0);
		// Save received values in own variables
		if ($Message == VM_UPDATE) {
			$value = $Data[0];
			// Wert entsprechend zuordnen
			if ($senderIdInt === $tempVarId) {
				$this->SetECValue(self::EEP_VARIABLES[self::TEMPERATURE], (float)$value);
			}
			if ($senderIdInt === $humVarId) {
				$this->SetECValue(self::EEP_VARIABLES[self::HUMIDITY], (float)$value);
			}
			// Timer setzen (2 Sekunden warten, dann send) - verhindert das doppelte Senden des Telegramms, wenn beide Variablen fast gleichzeitig aktualisiert werden
            if ($this->ReadPropertyBoolean(self::propertyResendActive)) {
				$this->SetTimerInterval(self::timerPrefix . $this->InstanceID, 2 * 1000);
			}
		}
    }

	public function SendTelegramDelayed()
	{
		$this->SetTimerInterval(self::timerPrefix . $this->InstanceID, 0); // Timer wieder stoppen
		$temp = 0.0;
		$hum  = 0.0;
		$variables = self::EEP_VARIABLE_PROFILES[$this->ReadPropertyString(self::propertyTargetEEP)];
		foreach ($variables as $varIdent => $definition) {
			if ($varIdent === self::EEP_VARIABLES[self::TEMPERATURE]['Ident']) {
				$temp = $this->GetECValue(self::EEP_VARIABLES[$varIdent]);
			}
			if ($varIdent === self::EEP_VARIABLES[self::HUMIDITY]['Ident']) {
				$hum = $this->GetECValue(self::EEP_VARIABLES[$varIdent]);
			}
		}
		// Default-Werte, falls Variable nicht benötigt wird für gewähltes EEP (dann gibt es auch keinen Backup und der Wert wird bei Senden ignoriert)
		if (!is_int($hum)) {
			$hum = 0;
		}		
		$this->SendDebug(__FUNCTION__, 'Send telegram for ' . $this->InstanceID . '/' . $this->ReadPropertyInteger(self::propertyDeviceID) . ': temp=' . $temp . ', hum=' . $hum, 0);
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

		$targetEEP  = $this->ReadPropertyString(self::propertyTargetEEP);

		// Parameter in Ziel-Protokoll umwandeln
		try {
			$rawTemp = EEPConverter::encodeTemperature($targetEEP, $temperature);
			$rawHum  = EEPConverter::encodeHumidity($targetEEP, $humidity);
		} catch (\Exception $e) {
			$this->SendDebug(__FUNCTION__, 'Encode Fehler: ' . $e->getMessage(), 0);
			return;
		}

		$data = EEPProfiles::gatewayBaseData();
		$data['DeviceID'] = $this->ReadPropertyInteger(self::propertyDeviceID); 
		$DB0 = 8;
		$DB1 = 0;
		$DB2 = 0;
		$DB3 = 0;
		if ($teachIn) {
			$DB0 = 0; // Status-Byte = Teach-in
		}

		switch ($targetEEP) {
			case EEPProfiles::A5_02_13: // 8 Bit Temp
				// Temperatur linear auf 0...255 skaliert, aber invertiert
				// Formel aus Spec: val = (255 - raw) * (Range / 255) + Tmin
				// Umkehrung zum Kodieren:
				// raw = 255 - round((TEMP - Tmin) * 255 / Range)
				$Tmin = -30.0;
				$Tmax = 50.0;
				$range = $Tmax - $Tmin; // 80 K
				$rawTemp = 255 - (int)round((($temperature - $Tmin) * 255) / $range);
				if ($rawTemp < 0) $rawTemp = 0;
				if ($rawTemp > 255) $rawTemp = 255;
				$DB1 = $rawTemp;
				$lrnBit = $teachIn ? 0 : 1;
				$DB0 = ($lrnBit << 3); // Bit 3 = LRN, Rest 0
				break;
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
		$data['DataByte0'] = $DB0;
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
        // Json Template laden & Platzhalter ersetzen
        $form = file_get_contents(__DIR__ . '/form.json');
        // Unterstützte Devices einfügnen
		$validModules = GUIDs::allTemperatureIpsGuids();
		$form = str_replace('<!---VALID_MODULES-->', json_encode($validModules), $form);
		$form = str_replace('<!---VALID_EEP_OPTIONS-->', EEPProfiles::createFormularJsonFromAvailableEEP(EEPProfiles::allTemperatureProfiles()), $form);
		$form = str_replace('<!---BACKUP_TEMPERATURE_VISIBLE-->', ($this->getECBuffer(self::EEP_VARIABLES[self::TEMPERATURE])==="0" ? 'true' : 'false'), $form);
		$form = str_replace('<!---BACKUP_HUMIDITY_VISIBLE-->', ($this->getECBuffer(self::EEP_VARIABLES[self::HUMIDITY])==="0" ? 'true' : 'false'), $form);
		$form = str_replace('<!---BACKUP_MOTION_VISIBLE-->', ($this->getECBuffer(self::EEP_VARIABLES[self::MOTION])==="0" ? 'true' : 'false'), $form);
		$form = str_replace('<!---BACKUP_ILLUMINATION_VISIBLE-->', ($this->getECBuffer(self::EEP_VARIABLES[self::ILLUMINATION])==="0" ? 'true' : 'false'), $form);
		$form = str_replace('<!---BACKUP_VOLTAGE_VISIBLE-->', ($this->getECBuffer(self::EEP_VARIABLES[self::VOLTAGE])==="0" ? 'true' : 'false'), $form);
		$form = str_replace('<!---BACKUP_BUTTON_VISIBLE-->', ($this->getECBuffer(self::EEP_VARIABLES[self::BUTTON])==="0" ? 'true' : 'false'), $form);
		return $form;
	}
}