<?php

declare(strict_types=1);

namespace EnOceanConverter;

trait EnOceanConverterConstants{
    // Einheitliches Prefix für alle Variablen:
    private const VAR_PREFIX = "Variable";
    private const PROP_PREFIX_BACKUP = "Backup";

    // Verwaltete Variablen (Im Code NUR Constanten verwenden!):
    private const HUMIDITY = "Humidity";
    private const TEMPERATURE = "Temperature";
    private const MOTION = "Motion";
    private const ILLUMINATION = "Illumination";
    private const VOLTAGE = "Voltage";
    private const BUTTON = "Button";
    private const CONTACT = "Contact";

    // Einheitliche Idents und Namen für die Variablen, die in EEP Protokollen benötigt werden, in allen Modulen:
    public const EEP_VARIABLES = [
        self::HUMIDITY      => ["Ident" => "Humidity",       "Name" => "Luftfeuchtigkeit",       "BackupValue" => 40.0,     "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Humidity.F'],
        self::TEMPERATURE   => ["Ident" => "Temperature",    "Name" => "Temperatur",             "BackupValue" => 20.0,     "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Temperature'],
        self::MOTION        => ["Ident" => "Motion",         "Name" => "PIR-Status",             "BackupValue" => false,    "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Motion'],
        self::ILLUMINATION  => ["Ident" => "Illumination",   "Name" => "Helligkeit",             "BackupValue" => 240,      "Type" => VARIABLETYPE_INTEGER,  "Profile" => '~Illumination'],
        self::VOLTAGE       => ["Ident" => "Voltage",        "Name" => "Versorgungsspannung",    "BackupValue" => 3.3,      "Type" => VARIABLETYPE_FLOAT,    "Profile" => '~Volt'],
        self::BUTTON        => ["Ident" => "Button",         "Name" => "Taster-Status",          "BackupValue" => false,    "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Switch'],
        self::CONTACT       => ["Ident" => "Contact",        "Name" => "Kontakt",                "BackupValue" => false,    "Type" => VARIABLETYPE_BOOLEAN,  "Profile" => '~Door']
    ];

    public const EEP_VARIABLE_PROFILES = 
    [
        EEPProfiles::D5_00_01 => [
            self::EEP_VARIABLES[self::CONTACT]
        ],
        EEPProfiles::A5_02_13 => [
            self::EEP_VARIABLES[self::TEMPERATURE]
        ],
        EEPProfiles::A5_04_01 => [
            self::EEP_VARIABLES[self::TEMPERATURE], self::EEP_VARIABLES[self::HUMIDITY]
        ],
        EEPProfiles::A5_04_02 => [
            self::EEP_VARIABLES[self::TEMPERATURE], self::EEP_VARIABLES[self::HUMIDITY]
        ],
        EEPProfiles::A5_04_03 => [
            self::EEP_VARIABLES[self::TEMPERATURE], self::EEP_VARIABLES[self::HUMIDITY]
        ],
        EEPProfiles::A5_04_04 => [
            self::EEP_VARIABLES[self::TEMPERATURE], self::EEP_VARIABLES[self::HUMIDITY]
        ],
        EEPProfiles::A5_07_01 => [
            self::EEP_VARIABLES[self::MOTION], self::EEP_VARIABLES[self::VOLTAGE]
        ],
        EEPProfiles::A5_07_02 => [
            self::EEP_VARIABLES[self::MOTION], self::EEP_VARIABLES[self::VOLTAGE]
        ],
        EEPProfiles::A5_07_03 => [
            self::EEP_VARIABLES[self::MOTION], self::EEP_VARIABLES[self::ILLUMINATION], self::EEP_VARIABLES[self::VOLTAGE]
        ],
        EEPProfiles::A5_08_01 => [
            self::EEP_VARIABLES[self::MOTION], self::EEP_VARIABLES[self::TEMPERATURE], self::EEP_VARIABLES[self::ILLUMINATION], self::EEP_VARIABLES[self::VOLTAGE], self::EEP_VARIABLES[self::BUTTON]
        ],
        EEPProfiles::A5_08_02 => [
            self::EEP_VARIABLES[self::MOTION], self::EEP_VARIABLES[self::TEMPERATURE], self::EEP_VARIABLES[self::ILLUMINATION], self::EEP_VARIABLES[self::VOLTAGE], self::EEP_VARIABLES[self::BUTTON]
        ],
        EEPProfiles::A5_08_03 => [
            self::EEP_VARIABLES[self::MOTION], self::EEP_VARIABLES[self::TEMPERATURE], self::EEP_VARIABLES[self::ILLUMINATION], self::EEP_VARIABLES[self::VOLTAGE], self::EEP_VARIABLES[self::BUTTON]
        ],
        EEPProfiles::A5_08_02 => [
            self::EEP_VARIABLES[self::MOTION], self::EEP_VARIABLES[self::TEMPERATURE], self::EEP_VARIABLES[self::ILLUMINATION], self::EEP_VARIABLES[self::VOLTAGE], self::EEP_VARIABLES[self::BUTTON]
        ],
        EEPProfiles::A5_08_03 => [
            self::EEP_VARIABLES[self::MOTION], self::EEP_VARIABLES[self::TEMPERATURE], self::EEP_VARIABLES[self::ILLUMINATION], self::EEP_VARIABLES[self::VOLTAGE], self::EEP_VARIABLES[self::BUTTON]
        ]
    ];
}

class GUIDs
{
    // Gemeinsame DataFlow-GUID für Gateway <-> Child
    public const PARENT               = '{70E3075F-A35D-4DEB-AC20-C929A156FE48}';  // EnOcean Gateway
    //public const DATAFLOW_RECEIVE     = '{018EF6B5-AB94-40C6-AA53-46943E824ACF}';  // not receiving data
    public const DATAFLOW_TRANSMIT    = '{70E3075F-A35D-4DEB-AC20-C929A156FE48}';  // to Enocean Gateway {DE2DA2C0-7A28-4D23-A9AA-6D1C7609C7EC}
    // --- Modul GUIDs (Instanzen) ---
    public const EC_LIBRARY           = '{63647422-FE17-88E4-252A-402E8E58C4AD}';
    public const EC_TEMPERATUR        = '{029B9532-A614-BBC3-B9D6-904F648DC5F1}';
    public const EC_MOTION            = '{62BFBBBC-589E-0613-1794-6D21F34DC54F}';
    public const EC_CONTACT           = '{F512DF39-EE14-5CB5-DE38-71644EE5FE64}';
    // --- IPS BuildIn Variable Profile GUIDs ---
    public const IPS_BUILDIN_A50401   = '{432FF87E-4497-48D6-8ED9-EE7104A50401}';
    public const IPS_BUILDIN_A50402   = '{432FF87E-4497-48D6-8ED9-EE7104A50402}';
    public const IPS_BUILDIN_A50403   = '{432FF87E-4497-48D6-8ED9-EE7104A50403}';
    public const IPS_BUILDIN_A50404   = '{432FF87E-4497-48D6-8ED9-EE7104A50404}';
    public const IPS_BUILDIN_A50701   = '{432FF87E-4497-48D6-8ED9-EE7104A50701}';
    public const IPS_BUILDIN_A50702   = '{432FF87E-4497-48D6-8ED9-EE7104A50702}';
    public const IPS_BUILDIN_A50703   = '{432FF87E-4497-48D6-8ED9-EE7104A50703}';
    public const IPS_BUILDIN_A50801   = '{432FF87E-4497-48D6-8ED9-EE7104A50801}';
    public const IPS_BUILDIN_A50802   = '{432FF87E-4497-48D6-8ED9-EE7104A50802}';
    public const IPS_BUILDIN_A50803   = '{432FF87E-4497-48D6-8ED9-EE7104A50803}';
    public const IPS_BUILDIN_FWS61    = '{9E4572C0-C306-4F00-B536-E75B4950F094}'; // A5-01-13
    public const IPS_BUILDIN_D50001   = '{D4FC9D38-F7C7-8486-C940-AE5705A60E33}'; // D5-00-01

    /**
     * Liefert alle unterstützten Input-GUIDs für Temperaturen als Array
     *
     * @return string[]
     */
    public static function allTemperatureIpsGuids(): array
    {
        return [];
        return [
            self::IPS_BUILDIN_A50401,
            self::IPS_BUILDIN_A50402,
            self::IPS_BUILDIN_A50403,
            self::IPS_BUILDIN_A50404,
            self::IPS_BUILDIN_A50801,
            self::IPS_BUILDIN_A50802,
            self::IPS_BUILDIN_A50803,
            self::IPS_BUILDIN_FWS61
        ];
    }

    /**
     * Liefert alle unterstützten EEP für Motion als Array
     *
     * @return string[]
     */
    public static function allOccupancyIpsGuids(): array
    {
        return [
            self::IPS_BUILDIN_A50701,
            self::IPS_BUILDIN_A50702,
            self::IPS_BUILDIN_A50703,
            self::IPS_BUILDIN_A50801,
            self::IPS_BUILDIN_A50802,
            self::IPS_BUILDIN_A50803
        ];
    }

    /**
     * Liefert alle unterstützten EEP für Contact als Array
     *
     * @return string[]
     */
    public static function allContactIpsGuids(): array
    {
        return [
            self::IPS_BUILDIN_D50001
        ];
    }
}

class EEPProfiles
{
    // D5-00: Contacts and Switches
    public const D5_00_01 = 'D5-00-01'; /** Range: 0..1 (1 Bit) */
    // A5-02: Temperature Sensors
    public const A5_02_13 = 'A5-02-13'; /** Range: -30°C to +50°C (8 Bit) */
    
    // A5-04: Temperature and Humidity Sensor
    public const A5_04_01 = 'A5-04-01'; /** Range: 0…40 °C (8 Bit),     0…100 % (8 Bit) */
    public const A5_04_02 = 'A5-04-02'; /** Range: -20…60 °C (8 Bit),   0…100 % (8 Bit) */
    public const A5_04_03 = 'A5-04-03'; /** Range: -20…60 °C (10 Bit),  0…100 % (7 Bit) */
    public const A5_04_04 = 'A5-04-04'; /** Range: -40…120 °C (12 Bit), 0…100 % (8 Bit) */

    // A5-07: Occupancy Sensor
    public const A5_07_01 = 'A5-07-01'; /** Range: 0...127 PIR off / 128-255 PIR on (8 Bit),    0..5 V (8 Bit) */
    public const A5_07_02 = 'A5-07-02'; /** Range: 0 Uncertain / 1 Motion detected (1 Bit),     0..5 V (8 Bit) */
    public const A5_07_03 = 'A5-07-03'; /** Range: 0 Uncertain / 1 Motion detected (1 Bit),     0..5 V (8 Bit),     0..1000 Lux (10 Bit) */
    
    // A5-08: Light, Temperature and Occupancy Sensor
    public const A5_08_01 = 'A5-08-01'; /** Range: 0..1 Button (1 Bit), 0..1 PIR (1 Bit), 0..51°C (8 Bit),      0..510lx (8 Bit),  0..5,1V (8 Bit) */
    public const A5_08_02 = 'A5-08-02'; /** Range: 0..1 Button (1 Bit), 0..1 PIR (1 Bit), 0..51°C (8 Bit),      0..1020lx (8 Bit), 0..5,1V (8 Bit) */
    public const A5_08_03 = 'A5-08-03'; /** Range: 0..1 Button (1 Bit), 0..1 PIR (1 Bit), -30..50°C (10 Bit),   0..1530lx (8 Bit), 0..5,1V (8 Bit) */

    /**
     * Liefert alle Temperature-Profile als Array
     *
     * @return string[]
     */
    public static function allTemperatureProfiles(): array
    {
        return [
            self::A5_02_13,
            self::A5_04_01,
            self::A5_04_02,
            self::A5_04_03,
            self::A5_04_04
        ];
    }

    /**
     * Liefert alle Bewegungsmelder-Profile als Array
     *
     * @return string[]
     */
    public static function allMotionProfiles(): array
    {
        return [
            self::A5_07_01,
            self::A5_07_02,
            self::A5_07_03,
            self::A5_08_01,
            self::A5_08_02,
            self::A5_08_03
        ];
    }

    /**
     * Liefert alle Contact-Profile als Array
     *
     * @return string[]
     */
    public static function allContactProfiles(): array
    {
        return [
            self::D5_00_01
        ];
    }
    /**
     * Liefert alle unterstützten EEP als Json für die Options-Liste im Formular
     */
    public static function createFormularJsonFromAvailableEEP(array $EEPs): string
    {
        $result = [];
        foreach ($EEPs as $item) {
            $result[] = [
                "caption" => $item,
                "value"   => $item
            ];
        }
        return json_encode($result);
    }

    private const GatewayBaseData      = '{
        "DataID":"' . GUIDS::DATAFLOW_TRANSMIT . '",
        "Device":165, 
        "Status":0,
        "DeviceID":0,
        "DestinationID":-1,
        "DataLength":4,
        "DataByte12":0,
        "DataByte11":0,
        "DataByte10":0,
        "DataByte9":0,
        "DataByte8":0,
        "DataByte7":0,
        "DataByte6":0,
        "DataByte5":0,
        "DataByte4":0,
        "DataByte3":0,
        "DataByte2":0,
        "DataByte1":0,
        "DataByte0":0
    }';
    /**
     * Liefert die Basisdaten für das Gateway-Telegramm als Array
     *
     * @return array
     */    
    public static function gatewayBaseData(): array
    {
        return json_decode(self::GatewayBaseData, true);
    }
}