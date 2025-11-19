<?php

namespace Hospital;

class Outpatient extends Patient
{
    public function getTotalBill(): float
    {
        return $this->getConsultationFeesTotal();
    }
}
