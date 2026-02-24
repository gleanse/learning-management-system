<?php

require_once __DIR__ . '/../../config/db_connection.php';

class FeeConfig
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    // get all fee config rows grouped by education level
    public function getAll()
    {
        $stmt = $this->connection->prepare("
            SELECT *
            FROM fee_config
            ORDER BY
                education_level ASC,
                FIELD(school_year, 'Grade 11', 'Grade 12', '1st Year', '2nd Year', '3rd Year', '4th Year'),
                strand_course ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // get single fee config row by id
    public function getById($fee_id)
    {
        $stmt = $this->connection->prepare("
            SELECT * FROM fee_config WHERE fee_id = ?
        ");
        $stmt->execute([$fee_id]);
        return $stmt->fetch();
    }

    // update tuition, miscellaneous, other_fees for a specific row
    public function update($fee_id, $tuition_fee, $miscellaneous, $other_fees)
    {
        $stmt = $this->connection->prepare("
            UPDATE fee_config
            SET
                tuition_fee   = ?,
                miscellaneous = ?,
                other_fees    = ?,
                updated_at    = CURRENT_TIMESTAMP
            WHERE fee_id = ?
        ");
        return $stmt->execute([
            $tuition_fee,
            $miscellaneous,
            $other_fees,
            $fee_id,
        ]);
    }
}
