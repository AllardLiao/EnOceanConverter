<?php

declare(strict_types=1);
	class EnOceanConvertersTemperatureSensor extends IPSModuleStrict
	{
		public function Create():void
		{
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyString("SourceEEP", "");
			$this->RegisterPropertyString("AutoDetectEEP", "");
			$this->RegisterPropertyInteger("SourceDevice", 0);
			$this->RegisterPropertyInteger("TargetEEP", 0);
			$this->RegisterPropertyInteger("ResendActive", 0);
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
		}

		private function resend(): bool
		{
			return true;
		}
	}