<?php

declare(strict_types=1);

namespace App\Service;

use App\Data\Vector;
use App\Entity\Robot;
use App\Exception\InvalidPositionException;
use InvalidArgumentException;
use UnexpectedValueException;

final class RobotAction
{
    public const NORTH = 'north';
    public const EAST = 'east';
    public const SOUTH = 'south';
    public const WEST = 'west';

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

        if ($this->table->hasPotHole($pos)) {
            throw new InvalidPositionException('There is a pothole here', InvalidPositionException::POTHOLE);
        }

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
        $move = $this->getMoveVector($this->robot->face());

        $newPos = $move->add($this->robot->position());

        if ($this->table->hasPotHole($newPos)) {
            throw new InvalidPositionException('There is a pothole here', InvalidPositionException::POTHOLE);
        }

        if ($this->table->isInside($newPos) === false) {
            throw new UnexpectedValueException('Cannot move beyond the barrier');
        }

        $this->robot->update($newPos, $this->robot->face());
    }

    public function left(): void
    {
        $currentFace = $this->robot->face();
        $this->robot->update($this->robot->position(), $this->getFaceLeft($currentFace));
    }

    public function right(): void
    {
        $currentFace = $this->robot->face();
        $this->robot->update($this->robot->position(), $this->getFaceRight($currentFace));
    }

    public function path(int $x, int $y): string
    {
        $messages = [];
        $finalPosition = new Vector($x, $y);
        $currentPosition = $this->robot->position();
        $currentFace = $this->robot->face();
        $pathRobot = new Robot($currentPosition, $currentFace);

        if ($this->table->hasPotHole($finalPosition)) {
            throw new InvalidPositionException(
                'The place you wish to go has a pothole.',
                InvalidPositionException::POTHOLE
            );
        }

        if ($this->table->isInside($finalPosition) === false) {
            throw new InvalidPositionException(
                'The place you wish to go is beyond the barrier.',
                InvalidPositionException::INVALID
            );
        }

        if ($this->hasArrivedToFinalPosition($pathRobot->position(), $finalPosition)) {
            throw new InvalidPositionException(
                'You are already on your final destination.',
                InvalidPositionException::ALREADY_HERE
            );
        }

        do {
            if ($hasArrived = $this->hasArrivedToFinalPosition($pathRobot->position(), $finalPosition)) {
                break;
            }

            if ($this->execute($pathRobot, $finalPosition)) {
                $messages[] = 'move';
                continue;
            }

            $newFace = $this->getFaceLeft($pathRobot->face());
            $pathRobot->update($pathRobot->position(), $newFace);
            if ($this->execute($pathRobot, $finalPosition)) {
                $messages[] = 'left move';
                continue;
            }

            $newFace = $this->getFaceRight($pathRobot->face());
            $pathRobot->update($pathRobot->position(), $newFace);
            if ($this->execute($pathRobot, $finalPosition)) {
                $messages[] = 'right move';
                continue;
            }

            $newFace = $this->getFaceLeft($pathRobot->face());
            $pathRobot->update($pathRobot->position(), $newFace);
            if ($this->execute($pathRobot, $finalPosition)) {
                $messages[] = 'left move';
            }
        } while (!$hasArrived);

        return implode(', ', $messages);
    }

    private function execute(Robot $robot, Vector $finalPosition): bool
    {
        $move = $this->getMoveVector($robot->face());
        $newTmpPosition = $move->add($robot->position());

        $canGo = $this->isValid($newTmpPosition, $finalPosition);

        if ($canGo) {
            $robot->update($newTmpPosition, $robot->face());
        }

        return $canGo;
    }

    private function isValid(Vector $position): bool
    {
        return $this->table->isInside($position) && !$this->table->hasPotHole($position);
    }

    private function hasArrivedToFinalPosition(Vector $current, Vector $destination): bool
    {
        return $current->xpos() === $destination->xpos() && $current->ypos() === $destination->ypos();
    }

    private function getMoveVector(string $face): Vector
    {
        $move = new Vector(0, 0);
        switch ($face) {
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

        return $move;
    }

    private function getFaceLeft(string $currentFace): string
    {
        $newFace = '';
        switch ($currentFace) {
            case self::NORTH:
                $newFace = self::WEST;
                break;
            case self::EAST:
                $newFace = self::NORTH;
                break;
            case self::SOUTH:
                $newFace = self::EAST;
                break;
            case self::WEST:
                $newFace = self::SOUTH;
                break;
        }

        return $newFace;
    }

    private function getFaceRight(string $currentFace): string
    {
        $newFace = '';
        switch ($currentFace) {
            case self::NORTH:
                $newFace = self::EAST;
                break;
            case self::EAST:
                $newFace = self::SOUTH;
                break;
            case self::SOUTH:
                $newFace = self::WEST;
                break;
            case self::WEST:
                $newFace = self::NORTH;
                break;
        }

        return $newFace;
    }
}
