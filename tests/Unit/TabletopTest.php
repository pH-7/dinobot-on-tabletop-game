<?php

declare(strict_types=1);

use App\Data\Vector;
use App\Service\Tabletop;
use PHPUnit\Framework\TestCase;

final class TabletopTest extends TestCase
{

    public function test_inside_table(): void
    {
        $table = new Tabletop();

        $this->assertTrue($table->isInside(new Vector(0, 0)));
        $this->assertTrue($table->isInside(new Vector(4, 0)));
        $this->assertTrue($table->isInside(new Vector(0, 4)));
        $this->assertTrue($table->isInside(new Vector(4, 4)));
    }

    public function test_outside_table(): void
    {
        $table = new Tabletop();

        $this->assertFalse($table->isInside(new Vector(-1, -1)));
        $this->assertFalse($table->isInside(new Vector(-1, 0)));
        $this->assertFalse($table->isInside(new Vector(0, -1)));

        $this->assertFalse($table->isInside(new Vector(5, 5)));
        $this->assertFalse($table->isInside(new Vector(5, 0)));
        $this->assertFalse($table->isInside(new Vector(0, 5)));
    }

    /**
     * @dataProvider potholesProvider
     */
    public function test_correct_potholes(int $x, int $y): void
    {
        $table = new Tabletop();
        $move = new Vector($x, $y);

        $actual = $table->hasPotHole($move);

        $this->assertTrue($actual);
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

}
