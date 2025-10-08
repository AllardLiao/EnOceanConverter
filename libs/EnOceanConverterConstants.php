<?php

declare(strict_types=1);

namespace EnOceanConverter;

class GUIDs
{
    // Gemeinsame DataFlow-GUID für Gateway <-> Child
    public const PARENT               = '{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}';  // Client Socket
    public const DATAFLOW_RECEIVE     = '{018EF6B5-AB94-40C6-AA53-46943E824ACF}';  // RX
    public const DATAFLOW_TRANSMIT    = '{70E3075F-A35D-4DEB-AC20-C929A156FE48}';  // to Enocean Gateway
    // --- Modul GUIDs (Instanzen) ---
    public const EC_LIBRARY           = '{63647422-FE17-88E4-252A-402E8E58C4AD}';
    public const EC_TEMPERATUR        = '{029B9532-A614-BBC3-B9D6-904F648DC5F1}';
    public const EC_MOTION            = '{62BFBBBC-589E-0613-1794-6D21F34DC54F}';

    public const IPS_BUILDIN_A50401   = '{432FF87E-4497-48D6-8ED9-EE7104A50401}';
    public const IPS_BUILDIN_A50402   = '{432FF87E-4497-48D6-8ED9-EE7104A50402}';
    public const IPS_BUILDIN_A50403   = '{432FF87E-4497-48D6-8ED9-EE7104A50403}';

    /**
     * Liefert alle unterstützten EEP als Array
     *
     * @return string[]
     */
    public static function allTemperatureIpsGuids(): array
    {
        return [
            self::IPS_BUILDIN_A50401,
            self::IPS_BUILDIN_A50402,
            self::IPS_BUILDIN_A50403
        ];
    }
}
class EEPProfiles
{
    // Temperature & Humidity Sensor Profiles (A5-04-xx)

    /** Range: 0…40 °C (8 Bit), 0…100 % (8 Bit) */
    public const A5_04_01 = 'A5-04-01';
    /** Range: -20…60 °C (8 Bit), 0…100 % (8 Bit) */
    public const A5_04_02 = 'A5-04-02';
    /** Range: -20…60 °C (10 Bit), 0…100 % (7 Bit) */
    public const A5_04_03 = 'A5-04-03';
    /** Range: -40…120 °C (12 Bit), 0…100 % (8 Bit) */
    public const A5_04_04 = 'A5-04-04';

    /**
     * Liefert alle Temperature-Profile als Array
     *
     * @return string[]
     */
    public static function allTemperatureProfiles(): array
    {
        return [
            self::A5_04_01,
            self::A5_04_02,
            self::A5_04_03,
            self::A5_04_04
        ];
    }

    private const GatewayBaseData      = '{
        "DataID":"'.GUIDs::DATAFLOW_TRANSMIT.'",
        "Device":0xA5, 
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