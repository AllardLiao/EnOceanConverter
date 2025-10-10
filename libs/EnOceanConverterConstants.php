<?php

declare(strict_types=1);

namespace EnOceanConverter;

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
    public const IPS_BUILDIN_FWS61    = '{9E4572C0-C306-4F00-B536-E75B4950F094}'; // A5-13-01

    /**
     * Liefert alle unterstützten Input-GUIDs als Array
     *
     * @return string[]
     */
    public static function allTemperatureIpsGuids(): array
    {
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
     * Liefert alle unterstützten EEP als Array
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
}
class EEPProfiles
{
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
            self::A5_04_04,
            self::A5_08_01,
            self::A5_08_02,
            self::A5_08_03
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