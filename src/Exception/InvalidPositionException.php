<?php

namespace App\Exception;

use RuntimeException;

class InvalidPositionException extends RuntimeException
{
    public const ALREADY_HERE = 1;
    public const POTHOLE = 2;
}
