<?php

namespace Hospital\Repositories;

use Hospital\DB;

class InvoiceRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::getPDO();
    }

    /** Create and persist an invoice. Returns invoice_no */
    public function create(string $patientId, float $amount, array $data, string $status = 'issued'): string
    {
        $invoiceNo = 'INV-' . date('Ymd') . '-' . substr(md5(uniqid((string)microtime(true), true)), 0, 6);
        $stmt = $this->pdo->prepare('INSERT INTO invoices (invoice_no, patient_id, created_at, amount, data, status) VALUES (:no,:pid,:created,:amt,:data,:status)');
        $stmt->execute([
            ':no' => $invoiceNo,
            ':pid' => $patientId,
            ':created' => date('c'),
            ':amt' => $amount,
            ':data' => json_encode($data),
            ':status' => $status
        ]);
        return $invoiceNo;
    }

    public function getByInvoiceNo(string $no): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM invoices WHERE invoice_no = :no LIMIT 1');
        $stmt->execute([':no' => $no]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['data'] = json_decode($row['data'], true);
        return $row;
    }

    public function listByPatient(string $patientId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM invoices WHERE patient_id = :pid ORDER BY created_at DESC');
        $stmt->execute([':pid' => $patientId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['data'] = json_decode($r['data'], true);
        }
        return $rows;
    }
}
