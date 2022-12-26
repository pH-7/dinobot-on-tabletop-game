<?php
declare(strict_types=1);

namespace App\Service;

use App\Data\InputDto;
use InvalidArgumentException;

final class InputParser
{
    private const PLACE_PATTERN_COMMAND = '/^place\s+(\d+)\s*,\s*(\d+)\s*,\s*([a-zA-Z]+)$/';
    private const PATH_PATTERN_COMMAND = '/^path\s+(\d+)\s*,\s*(\d+)\s*$/';

    public function parse(string $input): InputDto
    {
        $input = strtolower(trim($input));

        if (preg_match(self::PLACE_PATTERN_COMMAND, $input, $matches) === 1) {
            $xPos = (int)$matches[1];
            $yPos = (int)$matches[2];
            $face = $matches[3];

            return new InputDto('place', $xPos, $yPos, $face);
        }

        if (preg_match(self::PATH_PATTERN_COMMAND, $input, $matches) === 1) {
            $xPos = (int)$matches[1];
            $yPos = (int)$matches[2];

            return new InputDto('path', $xPos, $yPos, null);
        }

        switch ($input) {
            case 'move':
            case 'left':
            case 'right':
            case 'report':
            case 'q':
                return new InputDto($input, null, null, null);
        }

        throw new InvalidArgumentException('Invalid input');
    }
}
