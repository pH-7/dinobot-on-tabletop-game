<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\Vector;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class Robot
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $xPos;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $yPos;

    /**
     * @ORM\Column(type="string", length=16)
     *
     * @var string
     */
    private $face;

    public function __construct(Vector $pos, string $face)
    {
        $this->update($pos, $face);
    }

    public function position(): Vector
    {
        return new Vector($this->xPos, $this->yPos);
    }

    public function face(): string
    {
        return $this->face;
    }

    public function update(Vector $pos, string $face): void
    {
        $this->xPos = $pos->xpos();
        $this->yPos = $pos->ypos();
        $this->face = $face;
    }
}
