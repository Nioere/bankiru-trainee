<?php

namespace App\Command;

class Champion
{
    public string $name;
    public string $nickname;
    public int $attackMin;
    public int $attackMax;
    public int $health;

    public function __construct(string $name, string $nickname, int $attackMin, int $attackMax, int $health)
    {
        $this->name = $name;
        $this->nickname = $nickname;
        $this->attackMin = $attackMin;
        $this->attackMax = $attackMax;
        $this->health = $health;
    }

    public function attack(): int
    {
        return rand($this->attackMin, $this->attackMax);
    }
}