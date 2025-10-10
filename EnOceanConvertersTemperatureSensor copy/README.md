# Temperature Sensors
Simulation of EnOcean motion & illumination sensors

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

This module sends EnOcean temperature & humidity telegrams.
 
It supports the following EEP 
* A5-07-03

### 2. Voraussetzungen

* IP-Symcon ab Version 7.1
* Present "real" motion sensors to take the source values for PIR and illumination

### 3. Software-Installation

* Über den Module Store das 'EnOcean Converters'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen
  https://github.com/AllardLiao/EnOceanConverter.git

### 4. Einrichten der Instanzen in IP-Symcon

Unter 'Instanz hinzufügen' kann das 'EnOcean Converters Motion Sensor'-Modul mithilfe des Schnellfilters gefunden werden.  
Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name              | Beschreibung
----------------- | ------------------------------------------------------------
Device ID         | ID des simulierten Geräts
Source ID         | ID des Geräts, von dem die Ausgangswerte genommen werden
Source EEP        | EEP des Source-Geräts
Target EEP        | EEP mit dem das Telegram gesendet werden soll

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name         | Typ     | Beschreibung
------------ | ------- | --------------------
Presence     | bool    | PIR / Anwesenheit
Illumination | int     | Helligkeit Lux
Volt         | float   | Versorgungsspannung V

#### Profile

Name         | Typ
------------ | -----------------------------
˜XXX | Anwesenheit
˜XXX  | Helligkeit Lux (in float)

### 6. Visualisierung

Keine Funktionalität in der Visualisierung.

### 7. PHP-Befehlsreferenz

Keine aufrufbaren Funktionen

### 8. Limitations

In den EEP Profilen A5-08-xx wird der Occupancy Button nicht ausgewertet