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

    private const DIRECTIONS = ['left', 'right', 'move'];

    private Robot $robot;
    private Tabletop $table;

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
        $this->robot->update($this->robot->position(), $this->getLeftFace($currentFace));
    }

    public function right(): void
    {
        $currentFace = $this->robot->face();
        $this->robot->update($this->robot->position(), $this->getRightFace($currentFace));
    }

    /**
     *  Path finder gives the path to take from the current position to a specific destination.
     *  The pathfinder doesn't give the shortest path, but it does escape from potholes.
     */
    public function path(int $x, int $y): string
    {
        $messages = [];
        $finalPosition = new Vector($x, $y);
        $currentPosition = $this->robot->position();
        $currentFace = $this->robot->face();
        $robot = new Robot($currentPosition, $currentFace);

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

        if ($this->hasArrivedToFinalPosition($robot->position(), $finalPosition)) {
            throw new InvalidPositionException(
                'You are already on your final destination.',
                InvalidPositionException::ALREADY_HERE
            );
        }

        do {
            $directionDetails = $this->pickUpDirection($robot);
            if ($this->execute($robot, $directionDetails['face'])) {
                $messages[] = $directionDetails['message'];
            }

            $hasArrived = $this->hasArrivedToFinalPosition($robot->position(), $finalPosition);
        } while (!$hasArrived);

        return implode(PHP_EOL, $messages);
    }

    /**
     * Execute the new path seeker and updates the robot's details if it's a valid path.
     *
     * @return bool TRUE if the path was valid and got saved, FALSE otherwise.
     */
    private function execute(Robot $robot, string $face): bool
    {
        $move = $this->getMoveVector($face);
        $newTmpPosition = $move->add($robot->position());

        $canGo = $this->isValid($newTmpPosition);
        if ($canGo) {
            $robot->update($newTmpPosition, $face);
        }

        return $canGo;
    }

    private function isValid(Vector $position): bool
    {
        return $this->table->isInside($position) && $this->table->hasPotHole($position) === false;
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

    private function getLeftFace(string $currentFace): string
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

    private function getRightFace(string $currentFace): string
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

    private function pickUpDirection(Robot $robot): array
    {
        $directionIndex = $this->getDirectionIndex();
        switch (self::DIRECTIONS[$directionIndex]) {
            case 'left':
                $face = $this->getLeftFace($robot->face());
                $message = 'move left';
                break;

            case 'right':
                $face = $this->getRightFace($robot->face());
                $message = 'move right';
                break;

            default:
                $face = $robot->face();
                $message = 'move';
        }

        return ['face' => $face, 'message' => $message];
    }

    private function getDirectionIndex(): int
    {
        static $rotateOrder = 0;

        $rotateOrder++;

        if ($rotateOrder > 2) {
            $rotateOrder = 0;
        }

        return $rotateOrder;
    }
}
