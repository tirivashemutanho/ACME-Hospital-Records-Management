<?php

namespace Hospital;

class Consultation
{
    private string $date; // ISO date YYYY-MM-DD
    private string $doctor;
    private float $fee;

    public function __construct(string $date, string $doctor, float $fee)
    {
        $this->date = $date;
        $this->doctor = $doctor;
        $this->fee = $fee;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getDoctor(): string
    {
        return $this->doctor;
    }

    public function getFee(): float
    {
        return $this->fee;
    }
}
