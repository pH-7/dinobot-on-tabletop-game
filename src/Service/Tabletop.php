<?php declare(strict_types=1);

namespace App\Service;

use App\Data\Vector;

final class Tabletop
{
    private const MIN_VALUE = 0;

    private const MAX_VALUE = 4;

    public function isInside(Vector $pos): bool
    {
        return min($pos->xpos(), $pos->ypos()) >= self::MIN_VALUE
        && max($pos->xpos(), $pos->ypos()) <= self::MAX_VALUE;
    }
}
