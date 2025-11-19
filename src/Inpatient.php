<?php

namespace Hospital;

class Inpatient extends Patient
{
    private string $admissionDate;
    private ?string $dischargeDate;
    private int $wardNumber;
    /** @var ProcedurePerformed[] */
    private array $procedures = [];
    private float $dailyBedCharge;

    public function __construct(string $patientId, string $name, int $age, string $admissionDate, int $wardNumber, float $dailyBedCharge = 50.0)
    {
        parent::__construct($patientId, $name, $age);
        $this->admissionDate = $admissionDate;
        $this->wardNumber = $wardNumber;
        $this->dailyBedCharge = $dailyBedCharge;
        $this->dischargeDate = null;
    }

    public function discharge(string $dischargeDate): void
    {
        $this->dischargeDate = $dischargeDate;
    }

    public function addProcedure(ProcedurePerformed $p): void
    {
        $this->procedures[] = $p;
    }

    /** @return ProcedurePerformed[] */
    public function getProcedures(): array
    {
        return $this->procedures;
    }

    private function getProceduresCost(): float
    {
        $sum = 0.0;
        foreach ($this->procedures as $p) {
            $sum += $p->getCost();
        }
        return $sum;
    }

    protected function getNumberOfDays(): int
    {
        if (!$this->dischargeDate) {
            // assume still admitted: compute until today
            $end = new \DateTime();
        } else {
            $end = new \DateTime($this->dischargeDate);
        }
        $start = new \DateTime($this->admissionDate);

        $diff = $start->diff($end);
        $days = (int)$diff->format('%a');
        // A stay of 0 days should count as 1 day
        return max(1, $days);
    }

    public function getTotalBill(): float
    {
        $consultationSum = $this->getConsultationFeesTotal();
        $procedureSum = $this->getProceduresCost();
        $days = $this->getNumberOfDays();
        $bedCharge = $this->dailyBedCharge * $days;
        return $consultationSum + $procedureSum + $bedCharge;
    }

    public function getAdmissionDate(): string
    {
        return $this->admissionDate;
    }

    public function getDischargeDate(): ?string
    {
        return $this->dischargeDate;
    }

    public function getWardNumber(): int
    {
        return $this->wardNumber;
    }

    public function getDailyBedCharge(): float
    {
        return $this->dailyBedCharge;
    }
}
