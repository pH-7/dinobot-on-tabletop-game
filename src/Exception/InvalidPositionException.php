<?php

namespace App\Exception;

use RuntimeException;

class InvalidPositionException extends RuntimeException
{
    public const INVALID = 1;
    public const POTHOLE = 2;
    public const ALREADY_HERE = 3;
}
