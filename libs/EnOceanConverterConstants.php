<?php

declare(strict_types=1);

namespace EnOceanConverter;

class GUIDs
{
    // Gemeinsame DataFlow-GUID für Gateway <-> Child
    public const DATAFLOW             = '{FF}';

    // --- Modul GUIDs (Instanzen) ---
    public const EC_LIBRARY           = '{63647422-FE17-88E4-252A-402E8E58C4AD}';
    public const EC_TEMPERATUR        = '{029B9532-A614-BBC3-B9D6-904F648DC5F1}';

    public const IPS_BUILDIN_A50401   = '{432FF87E-4497-48D6-8ED9-EE7104A50401}';
    public const IPS_BUILDIN_A50402   = '{432FF87E-4497-48D6-8ED9-EE7104A50402}';
    public const IPS_BUILDIN_A50403   = '{432FF87E-4497-48D6-8ED9-EE7104A50403}';
}
trait EEPProfiles
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
     * Liefert alle Profile als Array
     *
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::A5_04_01,
            self::A5_04_02,
            self::A5_04_03,
            self::A5_04_04,
        ];
    }
}
