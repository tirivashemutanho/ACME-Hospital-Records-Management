<?php

namespace Hospital;

class Invoice
{
    private Patient $patient;

    public function __construct(Patient $patient)
    {
        $this->patient = $patient;
    }

    public function getAmount(): float
    {
        $sum = 0.0;
        foreach ($this->getItems() as $it) {
            $sum += $it['amount'];
        }
        return $sum;
    }

    /**
     * Return an itemized array for the invoice. Each item is:
     * [description => string, qty => int, unit => float, amount => float]
     * @return array
     */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->patient->getConsultations() as $c) {
            $amt = (float)$c->getFee();
            $items[] = [
                'description' => sprintf('Consultation (%s) - Dr %s', $c->getDate(), $c->getDoctor()),
                'qty' => 1,
                'unit' => $amt,
                'amount' => $amt
            ];
        }

        if ($this->patient instanceof Inpatient) {
            /** @var Inpatient $in */
            $in = $this->patient;
            foreach ($in->getProcedures() as $p) {
                $cost = (float)$p->getCost();
                $items[] = [
                    'description' => sprintf('Procedure: %s', $p->getName()),
                    'qty' => 1,
                    'unit' => $cost,
                    'amount' => $cost
                ];
            }

            // bed charges: compute days between admission and discharge (or today)
            $start = new \DateTime($in->getAdmissionDate());
            $end = $in->getDischargeDate() ? new \DateTime($in->getDischargeDate()) : new \DateTime();
            $diff = $start->diff($end);
            $days = (int)$diff->format('%a');
            $days = max(1, $days);
            $unit = (float)$in->getDailyBedCharge();
            $bedAmount = $unit * $days;
            $items[] = [
                'description' => sprintf('Bed charge (ward %s) — %d day(s) @ %s', $in->getWardNumber(), $days, number_format($unit,2)),
                'qty' => $days,
                'unit' => $unit,
                'amount' => $bedAmount
            ];
        }

        return $items;
    }

    public function getSummary(): string
    {
        $lines = [];
        $lines[] = "Invoice for: " . $this->patient->getName() . " (" . $this->patient->getPatientId() . ")";
        foreach ($this->patient->getConsultations() as $c) {
            $lines[] = sprintf("Consultation (%s) - Dr %s : %.2f", $c->getDate(), $c->getDoctor(), $c->getFee());
        }
        // if inpatient, show procedures and bed charge info
        if ($this->patient instanceof Inpatient) {
            /** @var Inpatient $in */
            $in = $this->patient;
            foreach ($in->getProcedures() as $p) {
                $lines[] = sprintf("Procedure: %s : %.2f", $p->getName(), $p->getCost());
            }
            $lines[] = sprintf("Total: %.2f", $this->getAmount());
        } else {
            $lines[] = sprintf("Total: %.2f", $this->getAmount());
        }

        return implode("\n", $lines);
    }
}
