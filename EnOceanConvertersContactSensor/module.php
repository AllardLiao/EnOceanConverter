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

class EnOceanConvertersContactSensor extends IPSModuleStrict
{
	use EnOceanConverter\MessagesHelper;
	use EnOceanConverter\VariableHelper;
	use EnOceanConverter\BufferHelper;
	use EnOceanConverter\DeviceIDHelper;
	use EnOceanConverter\EnOceanConverterConstants;
	use EnOceanConverter\FormHelper;

	private const propertyDeviceID = "DeviceID";
	private const propertySourceDevice = "SourceDevice";
	private const propertyTargetEEP = "TargetEEP";
	private const propertyResendActive = "ResendActive";

	private const timerPrefix = "ECCSSendDelayed";

	public function Create():void
	{
		//Never delete this line!
		parent::Create();
		// Modul-Properties anlegen
		$this->RegisterPropertyInteger(self::propertySourceDevice, 0);
		$this->RegisterPropertyString(self::propertyTargetEEP, EEPProfiles::D5_00_01);
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
		$contact = $this->GetECValue(self::EEP_VARIABLES[self::CONTACT]);   // °C
		$this->UpdateFormField('ResultSendTest', 'caption', 'Send test telegram (Contact=' . ($contact ? "closed" : "open") . ')');
		$this->SendDebug(__FUNCTION__, "sending test: contact=" . $contact, 0);
		$this->SendEnOceanTelegram($contact, false);
	}

	/**
	 * Sendet ein Teach-in-Telegramm mit einem festen Wert (Kontakt geschlossen)
	 */
	public function sendTeachInTelegram(): void
	{
		if ($this->ReadPropertyInteger(self::propertyDeviceID) == 0) {
			$this->UpdateFormField('ResultTeachIn', 'caption', 'Please select Device ID first!');
			return;
		}
		$contact = true;   // closed
		$this->UpdateFormField('ResultTeachIn', 'caption', 'Send teach-in telegram (with Contact=' . ($contact ? "closed" : "open") . ')');
		$this->SendDebug(__FUNCTION__, "sending teach-in with: contact=" . $contact, 0);
		$this->SendEnOceanTelegram($contact, true);
	}

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data): void
    {
		$senderIdInt = (int)$SenderID;
		$contactVarId   = (int)$this->GetECBuffer(self::EEP_VARIABLES[self::CONTACT]);
		$this->SendDebug(__FUNCTION__, "sender={$senderIdInt} (contactVar={$contactVarId}) with DATA-0: " . print_r($Data[0], true), 0);
		// Save received values in own variables
		if ($Message == VM_UPDATE) {
			$value = $Data[0];
			// Wert entsprechend zuordnen
			if ($senderIdInt === $contactVarId) {
				$this->SetECValue(self::EEP_VARIABLES[self::CONTACT], (float)$value);
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
		$contact = true;
		$variables = self::EEP_VARIABLE_PROFILES[$this->ReadPropertyString(self::propertyTargetEEP)];
		foreach ($variables as $varIdent => $definition) {
			if ($varIdent === self::EEP_VARIABLES[self::CONTACT]['Ident']) {
				$contact = $this->GetECValue(self::EEP_VARIABLES[$varIdent]);
			}
		}
		$this->SendDebug(__FUNCTION__, 'Send telegram for ' . $this->InstanceID . '/' . $this->ReadPropertyInteger(self::propertyDeviceID) . ': contact=' . $contact, 0);
		$this->SendEnOceanTelegram($contact);
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
	private function SendEnOceanTelegram(bool $contact, bool $teachIn = false): void
	{
		if (!$this->isGatewayActive()) {
			$this->SendDebug(__FUNCTION__, 'Gateway nicht verbunden oder nicht aktiv - Telegramm nicht gesendet.', 0);
			return;
		}

		$targetEEP  = $this->ReadPropertyString(self::propertyTargetEEP);

		$data = EEPProfiles::gatewayBaseData();
		$data['DeviceID'] = $this->ReadPropertyInteger(self::propertyDeviceID); 
		$data['Device'] = 213; // 0xD5 = Contacts and Switches
		$data['DataLength'] = 1; //1BS Kommunikation
		$DB0 = 0;

		switch ($targetEEP) {
			case EEPProfiles::D5_00_01: // 1 Bit contact
				// Contact: 0=open, 1=closed
				$rawContact = $contact ? 1 : 0;
				// LRN Bit: 0=pressed (teach-in), 1=not pressed (data telegram)
				$lrnBit = $teachIn ? 0 : 1;
				// Byte DB0 zusammensetzen
				$DB0 |= ($lrnBit << 3);      // Bit 3
				$DB0 |= ($rawContact << 0);  // Bit 0
				break;
			default:
				$this->SendDebug(__FUNCTION__, 'Unknown TargetEEP: ' . $targetEEP, 0);
				return;
		}
		$data['DataByte0'] = $DB0;

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
		$form = $this->ReplacePlaceholdersInForm($form, GUIDs::allContactIpsGuids(), EEPProfiles::allContactProfiles());
		return $form;
	}
}