# Temperature Sensors
Simulation of EnOcean temperature & humidity sensors

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
* A5-04-01 (PTM 215B)
* A5-04-02 (PTM 216B)
* A5-04-03 (PTM 217B)
* A5-04-04 (PTM 218B)

### 2. Voraussetzungen

* IP-Symcon ab Version 7.1
* Present "real" temperature sensors to take the source values for temperature and humidity

### 3. Software-Installation

* Über den Module Store das 'EnOcean Converters Temperature Sensor'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen
  https://github.com/AllardLiao/EnOceanConverter.git

### 4. Einrichten der Instanzen in IP-Symcon

Unter 'Instanz hinzufügen' kann das 'EnOcean Converters Temperature Sensor'-Modul mithilfe des Schnellfilters gefunden werden.  
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

Name        | Typ     | Beschreibung
----------- | ------- | --------------------
Temperature | Float   | Temperatur
Humidity    | Float   | Luftfeuchtigkeit

#### Profile

Name         | Typ
------------ | -----------------------------
˜Temperature | Temperatur
˜Humidity.F  | Luftfeuchtigkeit (in float)

### 6. Visualisierung

Keine Funktionalität in der Visualisierung.

### 7. PHP-Befehlsreferenz

Keine aufrufbaren Funktionen





"parentRequirements": ["{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}"], 
{F29F3902-427B-4231-A1B7-0CDB8191E02F}

    "implemented": ["{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}"],
