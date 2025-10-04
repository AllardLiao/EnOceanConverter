<?php

declare(strict_types=1);

namespace EnOceanConverter;

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
