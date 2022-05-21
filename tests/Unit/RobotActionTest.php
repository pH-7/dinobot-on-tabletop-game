<?php declare(strict_types=1);

use App\Service\RobotAction;
use PHPUnit\Framework\TestCase;

final class RobotActionTest extends TestCase
{

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
}
