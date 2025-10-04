<?php

declare(strict_types=1);
	class EnOceanConvertersTemperatureSensor extends IPSModuleStrict
	{
		public function Create():void
		{
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyInteger("SourceEEP", 1);
			$this->RegisterPropertyBoolean("AutoDetectEEP", true);
			$this->RegisterPropertyInteger("SourceDevice", 0);
			$this->RegisterPropertyInteger("TargetEEP", 2);
			$this->RegisterPropertyBoolean("ResendActive", false);
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