<?php

namespace Hospital;

abstract class Patient
{
    protected string $patientId;
    protected string $name;
    protected int $age;
    /** @var Consultation[] */
    protected array $consultations = [];

    public function __construct(string $patientId, string $name, int $age)
    {
        $this->patientId = $patientId;
        $this->name = $name;
        $this->age = $age;
    }

    public function getPatientId(): string
    {
        return $this->patientId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function addConsultation(Consultation $c): void
    {
        $this->consultations[] = $c;
    }

    /**
     * @return Consultation[]
     */
    public function getConsultations(): array
    {
        return $this->consultations;
    }

    public function getConsultationFeesTotal(): float
    {
        $sum = 0.0;
        foreach ($this->consultations as $c) {
            $sum += $c->getFee();
        }
        return $sum;
    }

    abstract public function getTotalBill(): float;
}
