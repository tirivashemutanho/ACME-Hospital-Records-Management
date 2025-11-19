<?php

namespace Hospital\Repositories;

use Hospital\DB;
use Hospital\Patient;
use Hospital\Outpatient;
use Hospital\Inpatient;
use Hospital\DaycasePatient;
use Hospital\Consultation;
use Hospital\ProcedurePerformed;

class PatientRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::getPDO();
    }

    public function save(Patient $p): void
    {
        // upsert patient row
        $sql = "INSERT OR REPLACE INTO patients (patient_id, name, age, type, admission_date, discharge_date, ward_number, daily_bed_charge, procedure_name, theatre_fee)
                VALUES (:patient_id, :name, :age, :type, :admission_date, :discharge_date, :ward_number, :daily_bed_charge, :procedure_name, :theatre_fee)";

        $type = 'outpatient';
        $admission = null;
        $discharge = null;
        $ward = null;
        $daily = null;
        $procName = null;
        $theatre = null;

        if ($p instanceof DaycasePatient) {
            $type = 'daycase';
            $procName = $p->getProcedureName();
            $theatre = $p->getTheatreFee();
            $admission = $p->getConsultations()[0]->getDate() ?? null;
            $discharge = $admission;
            $ward = $p->getWardNumber();
        } elseif ($p instanceof Inpatient) {
            $type = 'inpatient';
            $admission = $p->getAdmissionDate() ?? null;
            $discharge = $p->getDischargeDate() ?? null;
            $ward = $p->getWardNumber() ?? null;
            $daily = $p->getDailyBedCharge() ?? null;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':patient_id' => $p->getPatientId(),
            ':name' => $p->getName(),
            ':age' => $p->getAge(),
            ':type' => $type,
            ':admission_date' => $admission,
            ':discharge_date' => $discharge,
            ':ward_number' => $ward,
            ':daily_bed_charge' => $daily,
            ':procedure_name' => $procName,
            ':theatre_fee' => $theatre,
        ]);

        // save consultations
        $this->pdo->prepare('DELETE FROM consultations WHERE patient_id = :pid')->execute([':pid' => $p->getPatientId()]);
        $stmtC = $this->pdo->prepare('INSERT INTO consultations (patient_id, date, doctor, fee) VALUES (:pid, :date, :doctor, :fee)');
        foreach ($p->getConsultations() as $c) {
            $stmtC->execute([':pid' => $p->getPatientId(), ':date' => $c->getDate(), ':doctor' => $c->getDoctor(), ':fee' => $c->getFee()]);
        }

        // save procedures
        if ($p instanceof Inpatient) {
            $this->pdo->prepare('DELETE FROM procedures WHERE patient_id = :pid')->execute([':pid' => $p->getPatientId()]);
            $stmtP = $this->pdo->prepare('INSERT INTO procedures (patient_id, name, cost) VALUES (:pid, :name, :cost)');
            foreach ($p->getProcedures() as $pr) {
                $stmtP->execute([':pid' => $p->getPatientId(), ':name' => $pr->getName(), ':cost' => $pr->getCost()]);
            }
        }
    }

    /** @return Patient[] */
    public function getAll(): array
    {
        $rows = $this->pdo->query('SELECT * FROM patients ORDER BY id DESC')->fetchAll(\PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = $this->rowToPatient($r);
        }
        return $out;
    }

    /**
     * Get paged patients with optional search term (searches name and patient_id)
     * @param string|null $search
     * @param int $page (1-based)
     * @param int $perPage
     * @return array [patients array, total_count]
     */
    public function getPaged(?string $search, int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        if ($search) {
            $like = '%' . $search . '%';
            $stmt = $this->pdo->prepare('SELECT * FROM patients WHERE name LIKE :s OR patient_id LIKE :s ORDER BY id DESC LIMIT :lim OFFSET :off');
            $stmt->bindValue(':s', $like, \PDO::PARAM_STR);
            $stmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $countStmt = $this->pdo->prepare('SELECT COUNT(*) as c FROM patients WHERE name LIKE :s OR patient_id LIKE :s');
            $countStmt->execute([':s' => $like]);
            $total = (int)$countStmt->fetch(\PDO::FETCH_ASSOC)['c'];
        } else {
            $stmt = $this->pdo->prepare('SELECT * FROM patients ORDER BY id DESC LIMIT :lim OFFSET :off');
            $stmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $total = (int)$this->pdo->query('SELECT COUNT(*) as c FROM patients')->fetch(\PDO::FETCH_ASSOC)['c'];
        }

        $out = [];
        foreach ($rows as $r) {
            $out[] = $this->rowToPatient($r);
        }
        return [$out, $total];
    }

    public function getByPatientId(string $patientId): ?Patient
    {
        $stmt = $this->pdo->prepare('SELECT * FROM patients WHERE patient_id = :pid');
        $stmt->execute([':pid' => $patientId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;
        return $this->rowToPatient($row);
    }

    private function rowToPatient(array $r): Patient
    {
        $type = $r['type'] ?? 'outpatient';
        if ($type === 'inpatient') {
            $p = new Inpatient($r['patient_id'], $r['name'], (int)$r['age'], $r['admission_date'] ?? date('Y-m-d'), (int)($r['ward_number'] ?? 0), (float)($r['daily_bed_charge'] ?? 0.0));
            if (!empty($r['discharge_date'])) $p->discharge($r['discharge_date']);
        } elseif ($type === 'daycase') {
            $p = new DaycasePatient($r['patient_id'], $r['name'], (int)$r['age'], $r['admission_date'] ?? date('Y-m-d'), (int)($r['ward_number'] ?? 0), $r['procedure_name'] ?? '', (float)($r['theatre_fee'] ?? 0.0));
        } else {
            $p = new Outpatient($r['patient_id'], $r['name'], (int)$r['age']);
        }

        // load consultations
        $stmt = $this->pdo->prepare('SELECT * FROM consultations WHERE patient_id = :pid ORDER BY id');
        $stmt->execute([':pid' => $r['patient_id']]);
        $cons = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($cons as $c) {
            $p->addConsultation(new Consultation($c['date'], $c['doctor'], (float)$c['fee']));
        }

        // load procedures
        $stmt = $this->pdo->prepare('SELECT * FROM procedures WHERE patient_id = :pid ORDER BY id');
        $stmt->execute([':pid' => $r['patient_id']]);
        $procs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($procs as $pr) {
            $p->addProcedure(new ProcedurePerformed($pr['name'], (float)$pr['cost']));
        }

        return $p;
    }
}
