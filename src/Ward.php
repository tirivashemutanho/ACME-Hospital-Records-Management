<?php

namespace Hospital;

class Ward
{
    private int $number;
    private int $capacity;
    private array $occupants = [];

    public function __construct(int $number, int $capacity = 10)
    {
        $this->number = $number;
        $this->capacity = $capacity;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function admitPatient(string $patientId): bool
    {
        if (count($this->occupants) >= $this->capacity) {
            return false;
        }
        $this->occupants[] = $patientId;
        return true;
    }

    public function dischargePatient(string $patientId): bool
    {
        $idx = array_search($patientId, $this->occupants);
        if ($idx === false) return false;
        array_splice($this->occupants, $idx, 1);
        return true;
    }

    public function getOccupants(): array
    {
        return $this->occupants;
    }
}
