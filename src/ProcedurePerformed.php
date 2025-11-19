<?php

namespace Hospital;

class ProcedurePerformed
{
    private string $name;
    private float $cost;

    public function __construct(string $name, float $cost)
    {
        $this->name = $name;
        $this->cost = $cost;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCost(): float
    {
        return $this->cost;
    }
}
