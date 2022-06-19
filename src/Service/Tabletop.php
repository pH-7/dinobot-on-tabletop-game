<?php

declare(strict_types=1);

namespace App\Service;

use App\Data\Vector;

final class Tabletop
{
    public const MIN_VALUE = 0;

    public const MAX_VALUE = 4;

    private const POTHOLES_X = [
        2,
        2
    ];

    private const POTHOLES_Y = [
        1,
        3
    ];

    public function isInside(Vector $pos): bool
    {
        return min($pos->xpos(), $pos->ypos()) >= self::MIN_VALUE
            && max($pos->xpos(), $pos->ypos()) <= self::MAX_VALUE;
    }


    public function hasPotHole(Vector $pos): bool
    {
        return in_array($pos->xpos(), self::POTHOLES_X, true) &&
            in_array($pos->ypos(), self::POTHOLES_Y, true);
    }
}
