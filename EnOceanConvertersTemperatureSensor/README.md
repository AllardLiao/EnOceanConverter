
# Temperature Sensors
Simulation of EnOcean temperature (& humidity) sensors

### Table of Contents

1. [Features](#1-features)
2. [Requirements](#2-requirements)
3. [Software Installation](#3-software-installation)
4. [Setting up Instances in IP-Symcon](#4-setting-up-instances-in-ip-symcon)
5. [Status Variables and Profiles](#5-status-variables-and-profiles)
6. [WebFront](#6-webfront)
7. [PHP Command Reference](#7-php-command-reference)

### 1. Features

This module sends EnOcean temperature & humidity telegrams.

It supports the following EEPs:
* A5-02-13
* A5-04-01
* A5-04-02
* A5-04-03
* A5-04-04

### 2. Requirements

* IP-Symcon version 7.1 or higher
* Existing "real" temperature (and humidity) sensors to provide source values for temperature and humidity

### 3. Software Installation

* Install the 'EnOcean Converters' module via the Module Store.
* Alternatively, add the following URL to Module Control:
  https://github.com/AllardLiao/EnOceanConverter.git

### 4. Setting up Instances in IP-Symcon

Under 'Add Instance', you can find the 'EnOcean Converters Temperature Sensor' module using the quick filter.  
For more information on adding instances, see the [Instance Documentation](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen).

__Configuration Page__:

| Name            | Description                                                    |
|-----------------|----------------------------------------------------------------|
| Device ID       | ID of the simulated device                                     |
| Source Device   | ID of the device from which the source values are taken        |
| Target EEP      | EEP with which the telegram should be sent                     |
| Addtl. Values   | If the target EEP requires values not provided by the source, a backup value can be set here |
| Activate        | Enable to send telegrams                                       |

### 5. Status Variables and Profiles

Status variables are automatically created depending on the selected target EEP. Deleting individual variables may cause malfunctions.

When identifying source variables of the source device, the standard IPS variable profiles are evaluated.

#### Status Variables

| Name        | Type    | Description           |
| Name        | Type    | Description           |
|------------ | ------- | ---------------------|
| Temperature | Float   | Temperature (°C)     |
| Humidity    | Float   | Humidity (%)         |

#### Profiles

Name         | Type
------------ | -----------------------------
~Temperature | Temperature (°C)
~Humidity.F  | Humidity (float)

### 6. Visualization

No special functionality in the visualization.

### 7. PHP Command Reference

No callable functions
