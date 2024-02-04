<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'fight')]
class FightCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fighter1 = new Champion('Петя', 'Осень', 10, 100, 100);
        $fighter2 = new Champion('Миша', 'Зима', 10, 100, 100);

        $output->writeln('Поприветствуем бойцов:');
        $output->writeln("  Боец 1: {$fighter1->name} {$fighter1->nickname} {$fighter1->health} здоровья, {$fighter1->attackMin}-{$fighter1->attackMax} атака");
        $output->writeln("  Боец 2: {$fighter2->name} {$fighter2->nickname} {$fighter2->health} здоровья, {$fighter2->attackMin}-{$fighter2->attackMax} атака");
        $output->writeln('---');

        $round = 1;
        while ($fighter1->health > 0 && $fighter2->health > 0) {
            $damageByFighter1 = $fighter1->attack();
            $damageByFighter2 = $fighter2->attack();
            $fighter2->health -= $damageByFighter1;
            $fighter1->health -= $damageByFighter2;

            $output->writeln("Раунд $round.");
            $output->writeln("   {$fighter1->name} наносит $damageByFighter1 урона");
            $output->writeln("   {$fighter2->name} наносит $damageByFighter2 урона");
            $output->writeln("   Итог: {$fighter2->name} ({$fighter2->health} здоровья) / {$fighter1->name} ({$fighter1->health} здоровья)");
            $output->writeln('---');

            ++$round;
        }

        if ($fighter1->health <= 0 && $fighter2->health <= 0) {
            $output->writeln('В этом бою ничья!');
        } elseif ($fighter1->health > 0) {
            $output->writeln("{$fighter1->name} побеждает в этом бою!");
        } else {
            $output->writeln("{$fighter2->name} побеждает в этом бою!");
        }

        return Command::SUCCESS;
    }
}
