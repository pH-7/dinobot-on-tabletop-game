<?php

declare(strict_types=1);

use App\Exception\InvalidPositionException;
use App\Service\RobotAction;
use PHPUnit\Framework\TestCase;

final class RobotActionTest extends TestCase
{
    /**
     * @dataProvider potholesProvider
     */
    public function test_invalid_potholes(int $x, int $y): void
    {
        $this->expectException(InvalidPositionException::class);
        $this->expectExceptionCode(InvalidPositionException::POTHOLE);
        $this->expectExceptionMessage('There is a pothole here');

        new RobotAction($x, $y, RobotAction::NORTH);
    }

    /**
     * @return array<int>[]
     */
    public function potholesProvider(): array
    {
        return [
            [2, 3],
            [2, 1]
        ];
    }

    public function test_invalid_face(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid face value');

        new RobotAction(0, 0, 'est');
    }

    public function test_place_and_report(): void
    {
        $robot = new RobotAction(0, 0, 'north');

        $this->assertSame('0,0,north', $robot->report());
    }

    /**
     * @dataProvider moveData
     */
    public function test_move_command(int $x, int $y, string $face, string $report): void
    {
        $robot = new RobotAction($x, $y, $face);
        $robot->move();

        $this->assertSame($report, $robot->report());
    }

    /**
     * @return array<int|string>[]
     */
    public function moveData(): array
    {
        return [
            [0, 0, 'north', '0,1,north'],
            [0, 0, 'east', '1,0,east'],
            [1, 1, 'south', '1,0,south'],
            [1, 1, 'west', '0,1,west'],
        ];
    }

    /**
     * @dataProvider leftData
     */
    public function test_left_command(int $x, int $y, string $face, string $report): void
    {
        $robot = new RobotAction($x, $y, $face);
        $robot->left();

        $this->assertSame($report, $robot->report());
    }

    /**
     * @return array<int|string>[]
     */
    public function leftData(): array
    {
        return [
            [0, 0, 'north', '0,0,west'],
            [0, 0, 'east', '0,0,north'],
            [0, 0, 'south', '0,0,east'],
            [0, 0, 'west', '0,0,south'],
        ];
    }

    /**
     * @dataProvider rightData
     */
    public function test_right_command(int $x, int $y, string $face, string $report): void
    {
        $robot = new RobotAction($x, $y, $face);
        $robot->right();

        $this->assertSame($report, $robot->report());
    }

    /**
     * @return array<int|string>[]
     */
    public function rightData(): array
    {
        return [
            [0, 0, 'north', '0,0,east'],
            [0, 0, 'east', '0,0,south'],
            [0, 0, 'south', '0,0,west'],
            [0, 0, 'west', '0,0,north'],
        ];
    }

    public function test_complex_move(): void
    {
        $robot = new RobotAction(1, 2, 'east');
        $robot->move();
        $robot->move();
        $robot->left();
        $robot->move();

        $this->assertSame('3,3,north', $robot->report());
    }

    /**
     * @dataProvider moveOutside
     */
    public function test_move_to_outside(int $x, int $y, string $face): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot move beyond the barrier');

        $robot = new RobotAction($x, $y, $face);
        $robot->move();
    }

    /**
     * @return array<int|string>[]
     */
    public function moveOutside(): array
    {
        return [
            [0, 0, 'west'],
            [0, 0, 'south'],
            [4, 0, 'east'],
            [0, 4, 'north'],
        ];
    }

    /**
     * @dataProvider moveToPotholesProvider
     */
    public function test_move_to_potholes(int $x, int $y, string $face): void
    {
        $this->expectException(InvalidPositionException::class);
        $this->expectExceptionCode(InvalidPositionException::POTHOLE);

        $robot = new RobotAction($x, $y, $face);
        $robot->move();
    }

    /**
     * @return array<int|string>[]
     */
    public function moveToPotholesProvider(): array
    {
        return [
            [2, 2, 'north'],
            [2, 0, 'north'],
            [1, 3, 'east'],
        ];
    }

    /**
     * @dataProvider placeOutside
     */
    public function test_place_outside_table(int $x, int $y, string $face): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid place position');

        new RobotAction($x, $y, $face);
    }

    /**
     * @return array<int|string>[]
     */
    public function placeOutside(): array
    {
        return [
            [-1, 0, 'north'],
            [0, -1, 'north'],
            [5, 0, 'north'],
            [0, 5, 'north'],
        ];
    }

    public function test_path_on_final_destination(): void
    {
        $this->expectException(InvalidPositionException::class);
        $this->expectExceptionCode(InvalidPositionException::ALREADY_HERE);

        $robot = new RobotAction(2, 4, RobotAction::SOUTH);
        $robot->path(2, 4);
    }

    public function test_path_beyond_barrier(): void
    {
        $this->expectException(InvalidPositionException::class);
        $this->expectExceptionCode(InvalidPositionException::INVALID);

        $robot = new RobotAction(0, 0, RobotAction::SOUTH);
        $robot->path(5, 0);
    }

    /**
     * @dataProvider potholesProvider
     */
    public function test_path_on_pothole(int $x, int $y): void
    {
        $this->expectException(InvalidPositionException::class);
        $this->expectExceptionCode(InvalidPositionException::POTHOLE);

        $robot = new RobotAction(0, 0, RobotAction::SOUTH);
        $robot->path($x, $y);
    }

    /**
     * @dataProvider pathsProvider
     */
    public function test_path_from_first_tile(int $x, int $y, string $face, string $pathResult): void
    {
        $robot = new RobotAction(0, 0, $face);

        // Assert what the pathfinder gives back
        $actual = $robot->path($x, $y);
        $this->assertSame($pathResult, $actual);
    }

    /**
     * @dataProvider initialPositionsAndPathsProvider
     */
    public function test_path_from_other_tile(
        int $initialX,
        $initialY,
        int $newX,
        int $newY,
        string $face,
        string $pathResult
    ): void {
        $robot = new RobotAction($initialX, $initialY, $face);

        // Assert what the pathfinder gives back
        $actual = $robot->path($newX, $newY);
        $this->assertSame($pathResult, $actual);
    }

    /**
     * @return array<int|string>[]
     */
    public function pathsProvider(): array
    {
        return [
            // Facing East
            [
                3,
                0,
                RobotAction::EAST,
                'move, move left, move, move left, move right, move, move right, move, move, move right, move, move left, move right, move, move right'
            ],

            // Facing West
            [4, 4, RobotAction::WEST, 'move right, move, move right, move, move, move left, move right, move left'],

            // Facing North
            [
                2,
                0,
                RobotAction::NORTH,
                'move right, move'
            ],

            // Facing South
            [
                4,
                3,
                RobotAction::SOUTH,
                'move left, move, move, move left, move right, move left, move'
            ],
        ];
    }

    /**
     * @return array<int|string>[]
     */
    public function initialPositionsAndPathsProvider(): array
    {
        return [
            // Facing East
            [
                3,
                2,
                2,
                4,
                RobotAction::EAST,
                'move left, move right, move left, move left, move'
            ],

            // Facing West
            [
                4,
                4,
                2,
                4,
                RobotAction::WEST,
                'move left, move right, move left, move right, move, move left, move right, move left, move left, move, move, move left, move right, move left, move, move left, move right, move left'
            ],

            // Facing North
            [
                4,
                2,
                1,
                4,
                RobotAction::NORTH,
                'move, move left, move right, move left, move'
            ],

            // Facing South
            [
                1,
                2,
                2,
                4,
                RobotAction::SOUTH,
                'move left, move, move left, move right, move left, move left, move'
            ],
        ];
    }
}
