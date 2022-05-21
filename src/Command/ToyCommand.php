<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\InputDto;
use App\Exception\InvalidPositionException;
use App\Service\InputParser;
use App\Service\RobotAction;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ToyCommand extends Command
{

    protected static $defaultName = 'app:toy';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Toy command';

    /**
     * @var RobotAction
     */
    private $robot;

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var QuestionHelper $helper
         */
        $helper = $this->getHelper('question');
        $q = new Question('> ', '');
        $parser = new InputParser();

        do {
            try {
                $command = $helper->ask($input, $output, $q);
                $dto = $parser->parse($command);
                $this->process($output, $dto);
            } catch (InvalidArgumentException|InvalidPositionException $exc) {
                $output->writeln($exc->getMessage());
            }
        } while ($command !== 'q');

        return 0;
    }

    private function process(OutputInterface $output, InputDto $dto): void
    {
        if ($dto->command() === 'q') {
            return;
        }

        if ($dto->command() !== 'place' && $this->robot === null) {
            throw new InvalidArgumentException('Should call place first');
        }

        switch ($dto->command()) {
            case 'place':
                /* @phpstan-ignore-next-line */
                $this->robot = new RobotAction($dto->xPos(), $dto->yPos(), $dto->face());
                break;
            case 'report':
                $output->writeln($this->robot->report());
                break;
            case 'move':
                $this->robot->move();
                break;
            case 'left':
                $this->robot->left();
                break;
            case 'right':
                $this->robot->right();
                break;
            case 'path':
                $path = $this->robot->path($dto->xPos(), $dto->yPos());
                $output->writeln($path);
                break;
        }
    }
}
