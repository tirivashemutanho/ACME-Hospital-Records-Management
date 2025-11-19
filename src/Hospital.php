<?php

namespace Hospital;

class Hospital
{
    /** @var Patient[] */
    private array $patients = [];

    public function addPatient(Patient $p): void
    {
        $this->patients[$p->getPatientId()] = $p;
    }

    public function getPatient(string $patientId): ?Patient
    {
        return $this->patients[$patientId] ?? null;
    }

    /** @return Patient[] */
    public function getAllPatients(): array
    {
        return array_values($this->patients);
    }

    public function invoicePatient(string $patientId): ?Invoice
    {
        $p = $this->getPatient($patientId);
        if (!$p) return null;
        return new Invoice($p);
    }
}
