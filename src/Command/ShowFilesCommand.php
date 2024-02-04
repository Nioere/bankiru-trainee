<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'show_files')]
class ShowFilesCommand extends Command
{
    private array $files = [
        ['file1.txt', 'dir1' => ['file2.txt', 'dir2' => ['file3.txt'], 'dir4' => []]],
    ];

    private int $counter = 1;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->showFiles($this->files, '', $output);

        return Command::SUCCESS;
    }

    private function showFiles(array $files, string $path, OutputInterface $output): void
    {
        foreach ($files as $key => $value) {
            if (is_array($value)) {
                $newPath = is_numeric($key) ? $path : $path . $key . '/';
                $this->showFiles($value, $newPath, $output);
            } else {
                $output->writeln($this->counter . ') ' . $path . $value);
                $this->counter++;
            }
        }
    }
}
