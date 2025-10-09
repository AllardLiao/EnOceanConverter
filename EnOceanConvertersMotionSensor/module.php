<?php

declare(strict_types=1);

// IPS-Stubs nur in der Entwicklungsumgebung laden

if (substr(__DIR__,0, 10) == "/Users/kai") {
    // Development
	include_once __DIR__ . '/../.ips_stubs/autoload.php';
}

use EnOceanConverter\DeviceIDHelper;
use EnOceanConverter\EEPProfiles;
use EnOceanConverter\EEPConverter;
use EnOceanConverter\GUIDs;
use EnOceanConverter\MessagesHelper;

/**
 * Include Controme helper classes.
 */
require_once __DIR__ . '/../libs/EnOceanConverterConstants.php';
require_once __DIR__ . '/../libs/EnOceanConverterHelper.php';

class EnOceanConvertersMotionSensor extends IPSModuleStrict
{
	use MessagesHelper;
	use DeviceIDHelper;

	private const propertyDeviceID = "DeviceID";
	private const propertySourceDevice = "SourceDevice";
	private const propertyTargetEEP = "TargetEEP";
	private const propertySourceEEP = "SourceEEP";
	private const propertyResendActive = "ResendActive";

	private const bufferPIR = "BufferPIR";
	private const bufferIllumination = "BufferIllumination";
	private const bufferVoltage = "BufferVoltage";
	private const bufferTemperature = "BufferTemperature";

	private const varPIR = "PIRStatus";
	private const varIllumination = "Helligkeit";
	private const varVoltage = "Versorgungsspannung";
	private const varTemperature = "Temperatur";

	private const timerPrefix = "ECMSSendDelayed";

	public function Create():void
	{
		//Never delete this line!
		parent::Create();

		$this->RegisterPropertyInteger(self::propertyDeviceID, 0);
		$this->RegisterPropertyInteger(self::propertySourceDevice, 0);
		$this->RegisterPropertyString(self::propertyTargetEEP, EEPProfiles::A5_07_01);
		$this->RegisterPropertyString(self::propertySourceEEP, EEPProfiles::A5_08_01);
		$this->RegisterPropertyBoolean(self::propertyResendActive, false);

		// Die Variablen-IDs der Quell-Variablen werden in den Buffern gespeichert
		$this->SetBuffer(self::bufferPIR, "0");
		$this->SetBuffer(self::bufferIllumination, "0");
		$this->SetBuffer(self::bufferVoltage, "0");
		$this->SetBuffer(self::bufferTemperature, "0");

		// Die übertragenen Werte werden in Variablen gespeichert
		$this->MaintainVariable(self::varPIR, "PIR-Status", VARIABLETYPE_BOOLEAN, "~Motion", 1, true);
		$this->MaintainVariable(self::varIllumination, "Helligkeit", VARIABLETYPE_INTEGER, "~Illumination", 2, true);
		$this->MaintainVariable(self::varVoltage, "Versorgungsspannung", VARIABLETYPE_FLOAT, "~Volt", 3, true);
		$this->MaintainVariable(self::varTemperature, "Temperatur", VARIABLETYPE_FLOAT, "~Temperature", 4, true);

		$this->RegisterTimer(self::timerPrefix . $this->InstanceID, 0, 'IPS_RequestAction(' . $this->InstanceID . ', "SendTelegramDelayed", true);');

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
        $this->unregisterAllECMessages();

        $sourceID = $this->ReadPropertyInteger(self::propertySourceDevice);
		//$this->SendDebug(__FUNCTION__, 'ApplyChanges: SourceDevice=' . $sourceID, 0);

		$this->SetBuffer(self::bufferPIR, '0');
		$this->SetBuffer(self::bufferIllumination, '0');
		$this->SetBuffer(self::bufferVoltage, '0');
		$this->SetBuffer(self::bufferTemperature, '0');

		 // Source Device auslesen und Variable(n) suchen
		if ($sourceID > 1) { // 0=root, 1=none
			$variables = IPS_GetChildrenIDs($sourceID);
			foreach ($variables as $vid) {
				$vinfo = IPS_GetVariable($vid);
				// nach Profil erkennen
				if (str_contains(strtoupper($vinfo['VariableProfile']), '_PIRS') || str_contains(strtoupper($vinfo['VariableProfile']), 'MOTION') || str_contains(strtoupper($vinfo['VariableProfile']), 'PRESENCE')) {
					$this->SetBuffer(self::bufferPIR, (string)$vid);
				}
				if (str_contains(strtoupper($vinfo['VariableProfile']), '_ILL') || str_contains(strtoupper($vinfo['VariableProfile']), 'ILLUMINATION')) {
					$this->SetBuffer(self::bufferIllumination, (string)$vid);
				}
				if (str_contains(strtoupper($vinfo['VariableProfile']), '_SVC') || str_contains(strtoupper($vinfo['VariableProfile']), 'VOLT')) {
					$this->SetBuffer(self::bufferVoltage, (string)$vid);
				}
				if (str_contains(strtoupper($vinfo['VariableProfile']), '_TMP') || str_contains(strtoupper($vinfo['VariableProfile']), 'TEMPERATURE')) {
					$this->SetBuffer(self::bufferTemperature, (string)$vid);
				}
			}
		}

		// Update Messages registrieren
		$status = 104; // Standard: Quelle nicht gesetzt
		$status = $this->registerECMessage(self::varPIR, intval($this->GetBuffer(self::bufferPIR)), $status);
		$status = $this->registerECMessage(self::varIllumination, intval($this->GetBuffer(self::bufferIllumination)), $status);
		$status = $this->registerECMessage(self::varVoltage, intval($this->GetBuffer(self::bufferVoltage)), $status);
		$status = $this->registerECMessage(self::varTemperature, intval($this->GetBuffer(self::bufferTemperature)), $status);

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
	 * Sendet ein Test-Telegramm mit den aktuellen Werten der Quell-Variablen
	 */
	public function sendTestTelegram(): void
	{
		$PIR = $this->GetValue(self::varPIR);
		$ILL = $this->GetValue(self::varIllumination);
		$TEMP = $this->GetValue(self::varTemperature);
		$VOL = $this->GetValue(self::varVoltage);
		$this->UpdateFormField('ResultSendTest', 'caption', 'Send test telegram (PIR=' . $PIR . ', ILL=' . $ILL . 'lx, TEMP=' . $TEMP . '°C, VOLT=' . $VOL . 'V)');
		$this->SendDebug(__FUNCTION__, "sending test: PIR=" . $PIR . ", ILL=" . $ILL . "lx, TEMP=" . $TEMP . "°C, VOLT=" . $VOL . "V", 0);
		$this->SendEnOceanTelegram($PIR, $ILL, $TEMP, $VOL, false);
	}

	/**
	 * Sendet ein Teach-in-Telegramm mit PIR=true, Illumination=12, Temperature=18.6, Voltage=3.3
	 */
	public function sendTeachInTelegram(): void
	{
		$PIR = true;
		$ILL = 12;
		$TEMP = 18.6;
		$VOL = 3.3;
		$this->UpdateFormField('ResultSendTeachIn', 'caption', 'Send teach-in telegram (PIR=' . $PIR . ', ILL=' . $ILL . 'lx, TEMP=' . $TEMP . '°C, VOLT=' . $VOL . 'V)');
		$this->SendDebug(__FUNCTION__, "sending teach-in with: PIR=" . $PIR . ", ILL=" . $ILL . "lx, TEMP=" . $TEMP . "°C, VOLT=" . $VOL . "V", 0);
		$this->SendEnOceanTelegram($PIR, $ILL, $TEMP, $VOL, true);
	}

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data): void
    {
		$senderIdInt = (int)$SenderID;
		$tempVarId   = (int)$this->GetBuffer(self::bufferTemperature);
		$illVarId    = (int)$this->GetBuffer(self::bufferIllumination);
		$pirVarId    = (int)$this->GetBuffer(self::bufferPIR);
		$volVarId    = (int)$this->GetBuffer(self::bufferVoltage);

		$this->SendDebug(__FUNCTION__, "sender={$senderIdInt} (tempVar={$tempVarId}, illVar={$illVarId}, pirVar={$pirVarId}, volVar={$volVarId}) with DATA-0: " . print_r($Data[0], true), 0);
		// Save received values in own variables
		if ($Message == VM_UPDATE) {
			$value = $Data[0];
			// Wert entsprechend zuordnen
			if ($senderIdInt === $tempVarId) {
				$this->SetValue(self::varTemperature, (float)$value);
			}
			if ($senderIdInt === $illVarId) {
				$this->SetValue(self::varIllumination, (int)$value);
			}
			if ($senderIdInt === $pirVarId) {
				$targetEEP  = $this->ReadPropertyString(self::propertyTargetEEP);
				$sourceEEP  = $this->ReadPropertyString(self::propertySourceEEP);
				$valueNew = (bool)$value;
				if ((str_starts_with($sourceEEP, 'A5-07') && str_starts_with($targetEEP, 'A5-08')) || (str_starts_with($sourceEEP, 'A5-08') && str_starts_with($targetEEP, 'A5-07'))) {
					// A05-07 und A05-08 haben inverse PIR-Codierungen!
					$valueNew = (!$valueNew);
				}
				$this->SetValue(self::varPIR, $valueNew);
			}
			if ($senderIdInt === $volVarId) {
				$this->SetValue(self::varVoltage, (float)$value);
			}
			// Timer setzen (2 Sekunden warten, dann send) - verhindert das doppelte Senden des Telegramms, wenn beide Variablen fast gleichzeitig aktualisiert werden
            $this->SetTimerInterval(self::timerPrefix . $this->InstanceID, 2 * 1000);
		}
    }

	public function SendTelegramDelayed()
	{
		$this->SetTimerInterval(self::timerPrefix . $this->InstanceID, 0); // Timer wieder stoppen
		$temp = $this->GetValue(self::varTemperature);
		$ill  = $this->GetValue(self::varIllumination);
		$pir  = $this->GetValue(self::varPIR);
		$vol  = $this->GetValue(self::varVoltage);
		$this->SendDebug(__FUNCTION__, 'Send telegram for ' . $this->InstanceID . '/' . $this->ReadPropertyInteger(self::propertyDeviceID) . ': temp=' . $temp . ', ill=' . $ill . ', pir=' . $pir . ', vol=' . $vol, 0);
		$this->SendEnOceanTelegram($pir, $ill, $temp, $vol);
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
	private function SendEnOceanTelegram(bool $PIR, int $ILL, float $TEMP, float $VOLT, bool $teachIn = false): void
	{
		if (!$this->isSocketActive()) {
			$this->SendDebug(__FUNCTION__, 'Socket nicht verbunden oder nicht aktiv - Telegramm nicht gesendet.', 0);
			return;
		}

		$targetEEP  = $this->ReadPropertyString(self::propertyTargetEEP);

		// -> benutze die vorhandenen encode-Funktionen, um die RAW-Integer zu bekommen
		try {
			$rawPir = EEPConverter::encodeMotion($targetEEP, $PIR);
			$rawIll = EEPConverter::encodeLux($targetEEP, $ILL);
			$rawTemp = EEPConverter::encodeTemperature($targetEEP, $TEMP);
			$rawVol = EEPConverter::encodeVoltage($targetEEP, $VOLT);
		} catch (\Exception $e) {
			$this->SendDebug(__FUNCTION__, 'Encode Fehler: ' . $e->getMessage(), 0);
			return;
		}

		//4bs = 4 Databytes. DB0 enthält im byte 3 das Lern-Flag (0=Teach-in, 8=Normal)
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
			case EEPProfiles::A5_07_01: 
				// A5-07-01 (Occupancy / Supply voltage)
				// DB3: Supply voltage (0..250 valid; 251-255 reserved/error)
				// DB2: Not used (= 0)
				// DB1: PIR Status (0..127 PIR off, 128..255 PIR on)
				// DB0:
				//   DB0.3 LRN (0 = Teach-in, 1 = Data)
				//   DB0.2..DB0.1 not used (=0)
				//   DB0.0 SVA (Supply voltage availability: 0 = not supported, 1 = supported)
				// DB3: take encoded voltage, clamp to 0..250 (250 = max valid; >250 could be error codes)
				$DB3 = is_numeric($rawVol) ? intval($rawVol) : 0;
				if ($DB3 < 0) {
					$DB3 = 0;
				} elseif ($DB3 > 250) {
					// wenn >250, setze auf 250 (oder handle als error-code falls gewünscht)
					$DB3 = 250;
				}
				// DB2: not used
				$DB2 = 0;
				// DB1: PIR status
				// Spez: 0..127 -> PIR off, 128..255 -> PIR on
				// Wir setzen DB1 in erster Linie anhand der boolschen $PIR-Flag.
				// Falls EEPConverter schon eine Skala liefert, versuchen wir diese zu respektieren, sonst einfache Zuordnung.
				if (is_numeric($rawPir)) {
					$db1Candidate = intval($rawPir);
					// normalize candidate into allowed byte range 0..255
					if ($db1Candidate < 0) $db1Candidate = 0;
					if ($db1Candidate > 255) $db1Candidate = 255;
					if ($PIR) {
						// sicherstellen, dass Wert im "on"-Bereich ist
						if ($db1Candidate < 128) $db1Candidate = 128;
					} else {
						// sicherstellen, dass Wert im "off"-Bereich ist
						if ($db1Candidate > 127) $db1Candidate = 0;
					}
					$DB1 = $db1Candidate;
				} else {
					// rawPir nicht numerisch -> reiner boolean-Fallback
					$DB1 = $PIR ? 128 : 0;
				}
				// DB0: Bits zusammenbauen
				// LRN (DB0.3): 0 = Teach-in, 1 = Data
				$lrn = $teachIn ? 0 : 1;
				// SVA (DB0.0): 1 wenn Versorgungswert angegeben/unterstützt (wir prüfen rawVol)
				$sva = is_numeric($rawVol) ? 1 : 0;
				$DB0 = ($lrn << 3) | ($sva ? 1 : 0);
				// restliche Bits bleiben 0 (gemäß Spezifikation)
				break;
			case EEPProfiles::A5_07_02: 
				// A5-07-02 (Motion + Supply Voltage only)
				// DB3: Supply Voltage
				$DB3 = is_numeric($rawVol) ? intval($rawVol) : 0;
				if ($DB3 < 0) {
					$DB3 = 0;
				} elseif ($DB3 > 250) {
					$DB3 = 250; // 251-255 reserved
				}
				// DB2 + DB1: Not used
				$DB2 = 0;
				$DB1 = 0;
				// DB0: PIR + LRN
				$pirBit = $PIR ? 1 : 0;             // DB0.7 = PIR status
				$lrn    = $teachIn ? 0 : 1;         // DB0.3 = LRN Bit
				$DB0 = ($pirBit << 7) | ($lrn << 3);
				break;
			case EEPProfiles::A5_07_03: 
				// A5-07-03 (Motion, Supply Voltage, Illumination)
				// DB3: Supply Voltage
				$DB3 = is_numeric($rawVol) ? intval($rawVol) : 0;
				if ($DB3 < 0) {
					$DB3 = 0;
				} elseif ($DB3 > 250) {
					$DB3 = 250; // 251-255 reserved for error
				}
				// Illumination: 10-bit linear 0..1000
				$ill = is_numeric($rawIll) ? intval($rawIll) : 0;
				if ($ill < 0) $ill = 0;
				if ($ill > 1000) $ill = 1001; // over range
				$DB2 = ($ill >> 2) & 0xFF;           // high 8 bits
				$DB1 = ($ill & 0x03) << 6;           // low 2 bits in DB1.7..6
				// DB1.5..0 bleiben 0
				// DB0: PIR + LRN
				$pirBit = $PIR ? 1 : 0;              // DB0.7 = PIR status
				$lrn    = $teachIn ? 0 : 1;          // DB0.3 = LRN
				$DB0 = ($pirBit << 7) | ($lrn << 3);
				break;
			case EEPProfiles::A5_08_01: 
				// A5-08-01: Motion + Temp + Lux + Voltage
				// DB3: Supply Voltage (linear, 0..255 -> 0..5.1 V)
				$DB3 = is_numeric($rawVol) ? intval($rawVol) : 0;
				if ($DB3 < 0) {
					$DB3 = 0;
				} elseif ($DB3 > 255) {
					$DB3 = 255;
				}
				// DB2: Illumination (linear, 0..255 -> 0..510 lx)
				$DB2 = is_numeric($rawIll) ? intval($rawIll) : 0;
				if ($DB2 < 0) {
					$DB2 = 0;
				} elseif ($DB2 > 255) {
					$DB2 = 255;
				}
				// DB1: Temperature (linear, 0..255 -> 0..+51 °C)
				$DB1 = is_numeric($rawTemp) ? intval($rawTemp) : 0;
				if ($DB1 < 0) {
					$DB1 = 0;
				} elseif ($DB1 > 255) {
					$DB1 = 255;
				}
				// DB0: [7..4]=0 | [3]=LRN | [2]=0 | [1]=PIR | [0]=Occupancy Button
				$lrnBit     = $teachIn ? 0 : 1;  // DB0.3
				$pirBit     = $PIR ? 0 : 1;      // Achtung: 0 = PIR on, 1 = PIR off
				$buttonBit  = 1;                 // Default: released
				$DB0 = ($lrnBit << 3) | ($pirBit << 1) | $buttonBit;
				break;
			case EEPProfiles::A5_08_02: 
				// A5-08-02: Supply voltage + Illumination + Temperature + PIR + LRN
				// DB3: Supply Voltage (0..255 -> 0..5.1 V)
				$DB3 = is_numeric($rawVol) ? intval($rawVol) : 0;
				if ($DB3 < 0) {
					$DB3 = 0;
				} elseif ($DB3 > 255) {
					$DB3 = 255;
				}
				// DB2: Illumination (0..255 -> 0..1020 lx)
				$DB2 = is_numeric($rawIll) ? intval($rawIll) : 0;
				if ($DB2 < 0) {
					$DB2 = 0;
				} elseif ($DB2 > 255) {
					$DB2 = 255;
				}
				// DB1: Temperature (0..255 -> 0..+51 °C)
				$DB1 = is_numeric($rawTemp) ? intval($rawTemp) : 0;
				if ($DB1 < 0) {
					$DB1 = 0;
				} elseif ($DB1 > 255) {
					$DB1 = 255;
				}
				// DB0: [7..4]=0 | [3]=LRN | [2]=0 | [1]=PIR | [0]=ignored
				$lrnBit = $teachIn ? 0 : 1;       // DB0.3: 0=Teach-in, 1=Data
				$pir    = $PIR ? 0 : 1;           // DB0.1: 0=PIR on, 1=PIR off
				$DB0 = ($lrnBit << 3) | ($pir << 1);
				break;
			case EEPProfiles::A5_08_03: 
				// A5-08-03: Supply voltage + Illumination + Temperature + PIR + LRN
				// DB3: Supply Voltage (0..255 -> 0..5.1 V)
				$DB3 = is_numeric($rawVol) ? intval($rawVol) : 0;
				if ($DB3 < 0) {
					$DB3 = 0;
				} elseif ($DB3 > 255) {
					$DB3 = 255;
				}
				// DB2: Illumination (0..255 -> 0..1530 lx)
				$DB2 = is_numeric($rawIll) ? intval($rawIll) : 0;
				if ($DB2 < 0) {
					$DB2 = 0;
				} elseif ($DB2 > 255) {
					$DB2 = 255;
				}
				// DB1: Temperature (0..255 -> -30..+50 °C)
				// Mapping ist linear: 0 → -30 °C, 255 → +50 °C
				$DB1 = is_numeric($rawTemp) ? intval($rawTemp) : 0;
				if ($DB1 < 0) {
					$DB1 = 0;
				} elseif ($DB1 > 255) {
					$DB1 = 255;
				}
				// DB0: [7..4]=0 | [3]=LRN | [2]=0 | [1]=PIR | [0]=ignored
				$lrnBit = $teachIn ? 0 : 1;   // DB0.3: 0=Teach-in, 1=Data
				$pir    = $PIR ? 0 : 1;       // DB0.1: 0=PIR on, 1=PIR off
				$DB0 = ($lrnBit << 3) | ($pir << 1);
				break;

			default:
				$this->SendDebug(__FUNCTION__, 'Unknown Target EEP: ' . $targetEEP, 0);
				return;
		}
		$data['DataByte0'] = $DB0;
		$data['DataByte1'] = $DB1;
		$data['DataByte2'] = $DB2;
		$data['DataByte3'] = $DB3;

		$this->SendDebug(__FUNCTION__, 'Try to send data to parent: ' . print_r($data, true), 0);
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
		$validModules = GUIDs::allOccupancyIpsGuids();
		$form = str_replace('<!---VALID_MODULES-->', json_encode($validModules), $form);
		$form = str_replace('<!---VALID_EEP_OPTIONS-->', EEPProfiles::createFormularJsonFromAvailableEEP(EEPProfiles::allMotionProfiles()), $form);
		return $form;
	}
}