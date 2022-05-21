<?php declare(strict_types=1);

namespace App\Data;

final class InputDto
{

    private $command;

    private $xPos;

    private $yPos;

    private $face;

    public function __construct(string $command, ?int $xPos, ?int $yPos, ?string $face)
    {
        $this->command = $command;
        $this->xPos    = $xPos;
        $this->yPos    = $yPos;
        $this->face    = $face;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function xPos(): ?int
    {
        return $this->xPos;
    }

    public function yPos(): ?int
    {
        return $this->yPos;
    }

    public function face(): ?string
    {
        return $this->face;
    }
}
