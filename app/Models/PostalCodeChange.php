<?php
class PostalCodeChange
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO postal_code_changes (applicant_id, old_postal_code, new_postal_code)
             VALUES (:applicant_id, :old_postal_code, :new_postal_code)"
        );
        $stmt->execute([
            'applicant_id' => $data['applicant_id'],
            'old_postal_code' => $data['old_postal_code'],
            'new_postal_code' => $data['new_postal_code']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findByApplicantId(int $applicantId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM postal_code_changes WHERE applicant_id = :id ORDER BY requested_at DESC");
        $stmt->execute(['id' => $applicantId]);
        return $stmt->fetchAll();
    }
}