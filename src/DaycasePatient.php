<?php

namespace Hospital;

class DaycasePatient extends Inpatient
{
    private string $procedureName;
    private float $theatreFee;

    public function __construct(string $patientId, string $name, int $age, string $admissionDate, int $wardNumber, string $procedureName, float $theatreFee)
    {
        parent::__construct($patientId, $name, $age, $admissionDate, $wardNumber, 0.0);
        $this->procedureName = $procedureName;
        $this->theatreFee = $theatreFee;
        // Daycase is admitted and discharged same day; mark discharge as admission date
        $this->discharge($admissionDate);
    }

    public function getProcedureName(): string
    {
        return $this->procedureName;
    }

    public function getTheatreFee(): float
    {
        return $this->theatreFee;
    }

    public function getTotalBill(): float
    {
        // For Daycase: consultation fees + theatre fee + procedure costs if any
        $consultationSum = $this->getConsultationFeesTotal();
        $procedureSum = 0.0;
        foreach ($this->getProcedures() as $p) {
            $procedureSum += $p->getCost();
        }
        return $consultationSum + $this->theatreFee + $procedureSum;
    }
}
