<?php
namespace Hospital\Repositories;

use Hospital\DB;

class AppointmentRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::getPDO();
    }

    /**
     * Search appointments (consultations) by patient name, doctor, or date fragment.
     * Returns array of rows: id, patient_id, patient_name, date, doctor, fee
     */
    public function search(?string $q = null, int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT c.id, c.patient_id, p.name as patient_name, c.date, c.doctor, c.fee FROM consultations c JOIN patients p ON c.patient_id = p.patient_id';
        $params = [];
        if ($q) {
            $sql .= ' WHERE (p.name LIKE :q OR c.doctor LIKE :q OR c.date LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY c.date DESC LIMIT :lim OFFSET :off';
        $stmt = $this->pdo->prepare($sql);
        // bind ints safely
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT c.id, c.patient_id, p.name as patient_name, c.date, c.doctor, c.fee FROM consultations c JOIN patients p ON c.patient_id = p.patient_id WHERE c.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Return array of doctors => ['doctor' => name, 'patients' => [patient_id => name,...]] */
    public function listDoctorsWithPatients(): array
    {
        $stmt = $this->pdo->query('SELECT doctor, patient_id FROM consultations ORDER BY doctor');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) {
            $doc = $r['doctor'] ?: 'Unknown';
            if (!isset($map[$doc])) $map[$doc] = [];
            $map[$doc][$r['patient_id']] = true;
        }
        $out = [];
        foreach ($map as $doc => $patients) {
            $out[] = ['doctor' => $doc, 'patients' => array_keys($patients)];
        }
        return $out;
    }
}
