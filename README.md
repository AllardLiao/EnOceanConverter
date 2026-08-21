
# EnOcean Converter

A collection of modules to send EnOcean radio telegrams as virtual sensors from IP-Symcon.

Any sensor (instance) available in IP-Symcon can be used as a source.

Currently supported EEPs:

__Contacts and Switches__ ([Documentation](EnOceanConvertersContactSensor))
* EnOcean EEP D5-00-01

__Temperature Sensors__ ([Documentation](EnOceanConvertersTemperatureSensor))  
* EnOcean EEP A5-02-13
* EnOcean EEP A5-04-01
* EnOcean EEP A5-04-02
* EnOcean EEP A5-04-03
* EnOcean EEP A5-04-04

__Motion Sensors__ ([Documentation](EnOceanConvertersMotionSensor))  
* EnOcean EEP A5-07-01
* EnOcean EEP A5-07-02
* EnOcean EEP A5-07-03
* EnOcean EEP A5-08-01
* EnOcean EEP A5-08-02
* EnOcean EEP A5-08-03

## Changelog

### 1.0.4
* Fixed: telegrams were resent whenever the source variable updated, even if its value hadn't actually changed (e.g. periodic/heartbeat updates from the source). `MessageSink` now compares the new value against the stored one and only triggers a resend on a real change.
* Fixed: the Contact Sensor converter compared its boolean contact value as a float, which defeated the above fix for that module.
* Fixed: the Temperature Sensor converter's EEP A5-04-03 encoding packed humidity and the upper temperature bits into overlapping bits of the same data byte, corrupting both values for certain temperature/humidity combinations.
* Improved identification of temperature and voltage source variables by reading the resolved variable presentation (`IPS_GetVariablePresentation`) instead of matching a fixed list of known presentation templates.
* Raised the minimum required IP-Symcon version to 8.1, since `IPS_GetVariablePresentation` (used by the above fix) is not available on older versions.
* Added missing German/Spanish translations (Device ID, missing-value hint, license block, and the contact-sensor connection error, which also had an incorrect message text copy-pasted from the Temperature Sensor module).

### 1.0.3
* Identification of temperature and voltage by presentation template

### 1.0.2
* Improved identification of variables
* Support of dummy-modules as source

### 1.0
* Initial version

## License

This project is licensed under the [CC BY-NC-SA 4.0 License](https://creativecommons.org/licenses/by-nc-sa/4.0/).

## Third-party Licenses

- This module uses **traits from the [StylePHP](https://github.com/symcon/StylePHP) project** by Symcon GmbH,
  licensed under [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/).

This module is an independent community project and is not officially affiliated with or endorsed by Symcon GmbH.
