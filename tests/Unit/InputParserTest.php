<?php declare(strict_types=1);

use App\Service\InputParser;
use PHPUnit\Framework\TestCase;

final class InputParserTest extends TestCase
{

    public function test_case_sensitive_input(): void
    {
        $parser = new InputParser();

        $dto = $parser->parse('MOVE');
        $this->assertSame('move', $dto->command());
    }

    public function test_parse_place_command(): void
    {
        $parser = new InputParser();

        $dto = $parser->parse('PLACE 2,1,north');

        $this->assertSame('place', $dto->command());
        $this->assertSame(2, $dto->xpos());
        $this->assertSame(1, $dto->ypos());
        $this->assertSame('north', $dto->face());
    }

    public function test_parse_other_commands(): void
    {
        $parser = new InputParser();

        $dto = $parser->parse('move');
        $this->assertSame('move', $dto->command());

        $dto = $parser->parse('left');
        $this->assertSame('left', $dto->command());

        $dto = $parser->parse('right');
        $this->assertSame('right', $dto->command());

        $dto = $parser->parse('report');
        $this->assertSame('report', $dto->command());

        $dto = $parser->parse('q');
        $this->assertSame('q', $dto->command());
    }

    /**
     * @dataProvider invalidInput
     */
    public function test_invalid_input(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input');

        $parser = new InputParser();
        $parser->parse('place 2,1,north left');
    }

    /**
     * @return string[][]
     */
    public function invalidInput(): array
    {
        return [
                [''],
                ['place 2,1,north left'],
                ['place 2,1'],
                ['place 2'],
                ['place2'],
               ];
    }
}
