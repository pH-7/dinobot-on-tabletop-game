<?php declare(strict_types=1);

namespace App\Service;

use App\Data\Vector;
use App\Entity\Robot;
use InvalidArgumentException;
use UnexpectedValueException;

final class RobotAction
{
    public const NORTH = 'north';
    public const EAST  = 'east';
    public const SOUTH = 'south';
    public const WEST  = 'west';

    /**
     * @var Robot
     */
    private $robot;

    private $table;

    public function __construct(int $xpos, int $ypos, string $face)
    {
        $face = trim(strtolower($face));

        switch ($face) {
            case self::NORTH:
            case self::EAST:
            case self::SOUTH:
            case self::WEST:
                break;
            default:
                throw new InvalidArgumentException('Invalid face value');
        }

        $this->table = new Tabletop();

        $pos = new Vector($xpos, $ypos);
        if ($this->table->isInside($pos) === false) {
            throw new InvalidArgumentException('Invalid place position');
        }

        $this->robot = new Robot($pos, $face);
    }

    public function report(): string
    {
        $pos = $this->robot->position();
        return sprintf('%d,%d,%s', $pos->xpos(), $pos->ypos(), $this->robot->face());
    }

    public function move(): void
    {
        $move = new Vector(0, 0);
        switch ($this->robot->face()) {
            case self::NORTH:
                $move = new Vector(0, 1);
                break;
            case self::EAST:
                $move = new Vector(1, 0);
                break;
            case self::SOUTH:
                $move = new Vector(0, -1);
                break;
            case self::WEST:
                $move = new Vector(-1, 0);
                break;
        }

        $newPos = $move->add($this->robot->position());
        if ($this->table->isInside($newPos) === false) {
            throw new UnexpectedValueException('Cannot move beyond the barrier');
        }

        $this->robot->update($newPos, $this->robot->face());
    }

    public function left(): void
    {
        $face = '';
        switch ($this->robot->face()) {
            case self::NORTH:
                $face = self::WEST;
                break;
            case self::EAST:
                $face = self::NORTH;
                break;
            case self::SOUTH:
                $face = self::EAST;
                break;
            case self::WEST:
                $face = self::SOUTH;
                break;
        }

        $this->robot->update($this->robot->position(), $face);
    }

    public function right(): void
    {
        $face = '';
        switch ($this->robot->face()) {
            case self::NORTH:
                $face = self::EAST;
                break;
            case self::EAST:
                $face = self::SOUTH;
                break;
            case self::SOUTH:
                $face = self::WEST;
                break;
            case self::WEST:
                $face = self::NORTH;
                break;
        }

        $this->robot->update($this->robot->position(), $face);
    }
}
