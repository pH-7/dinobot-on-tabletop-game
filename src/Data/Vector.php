<?php declare(strict_types=1);

namespace App\Data;

final class Vector
{

    private $xpos;

    private $ypos;

    public function __construct(int $xpos, int $ypos)
    {
        $this->xpos = $xpos;
        $this->ypos = $ypos;
    }

    public function xpos(): int
    {
        return $this->xpos;
    }

    public function ypos(): int
    {
        return $this->ypos;
    }

    public function add(Vector $pos): self
    {
        $new = clone $this;

        $new->xpos = $new->xpos() + $pos->xpos();
        $new->ypos = $new->ypos() + $pos->ypos();

        return $new;
    }

}
