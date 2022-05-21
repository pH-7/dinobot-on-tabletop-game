<?php declare(strict_types=1);

namespace App\Service;

use App\Data\InputDto;
use InvalidArgumentException;

final class InputParser
{

    public function parse(string $input): InputDto
    {
        $input = strtolower(trim($input));

        $pattern = '/^place\s+(\d+)\s*,\s*(\d+)\s*,\s*([a-zA-Z]+)$/';
        if (preg_match($pattern, $input, $matches) === 1) {
            $xPos = (int) $matches[1];
            $yPos = (int) $matches[2];
            $face = $matches[3];

            return new InputDto('place', $xPos, $yPos, $face);
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
