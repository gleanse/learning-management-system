<?php

require_once __DIR__ . '/../models/FeeConfig.php';
require_once __DIR__ . '/../helpers/activity_logger.php';

class FeeConfigController
{
    private $fee_model;

    public function __construct()
    {
        $this->fee_model = new FeeConfig();
    }

    private function requireAdmin()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit();
        }
    }

    // json response helper
    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    // show fee config page
    public function showFeeConfig()
    {
        $this->requireAdmin();

        $fees = $this->fee_model->getAll();

        // group by education level for cleaner view rendering
        $grouped = [
            'senior_high' => [],
            'college'     => [],
        ];

        foreach ($fees as $row) {
            $grouped[$row['education_level']][] = $row;
        }

        require __DIR__ . '/../views/admin/fee_config.php';
    }

    // ajax: get single fee row for edit modal
    public function ajaxGetFee()
    {
        $this->requireAdmin();

        $fee_id = isset($_GET['fee_id']) ? (int) $_GET['fee_id'] : 0;

        if (!$fee_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid fee ID.'], 400);
        }

        $fee = $this->fee_model->getById($fee_id);

        if (!$fee) {
            $this->jsonResponse(['success' => false, 'message' => 'Fee configuration not found.'], 404);
        }

        $this->jsonResponse(['success' => true, 'data' => $fee]);
    }

    // ajax: update fee config row
    public function ajaxUpdateFee()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        $fee_id        = isset($_POST['fee_id'])        ? (int)   $_POST['fee_id']        : 0;
        $tuition_fee   = isset($_POST['tuition_fee'])   ? (float) $_POST['tuition_fee']   : null;
        $miscellaneous = isset($_POST['miscellaneous']) ? (float) $_POST['miscellaneous'] : 0;
        $other_fees    = isset($_POST['other_fees'])    ? (float) $_POST['other_fees']    : 0;

        $errors = [];

        if (!$fee_id)                                   $errors['fee_id']      = 'Invalid fee ID.';
        if ($tuition_fee === null || $tuition_fee <= 0) $errors['tuition_fee'] = 'Tuition fee is required and must be greater than 0.';

        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 422);
        }

        // confirm row exists and get old data for log
        $old_data = $this->fee_model->getById($fee_id);

        if (!$old_data) {
            $this->jsonResponse(['success' => false, 'message' => 'Fee configuration not found.'], 404);
        }

        $result = $this->fee_model->update($fee_id, $tuition_fee, $miscellaneous, $other_fees);

        if (!$result) {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update fee configuration. Please try again.'], 500);
        }

        // LOG THIS ACTION
        logAction(
            'update_fee_config',
            "Updated fee configuration for {$old_data['education_level']} - {$old_data['strand_course']} ({$old_data['school_year']})",
            'fee_config',
            $fee_id,
            [
                'tuition_fee' => $old_data['tuition_fee'],
                'miscellaneous' => $old_data['miscellaneous'],
                'other_fees' => $old_data['other_fees'],
                'total' => $old_data['tuition_fee'] + $old_data['miscellaneous'] + $old_data['other_fees']
            ],
            [
                'tuition_fee' => $tuition_fee,
                'miscellaneous' => $miscellaneous,
                'other_fees' => $other_fees,
                'total' => $tuition_fee + $miscellaneous + $other_fees
            ]
        );

        $total = $tuition_fee + $miscellaneous + $other_fees;

        $this->jsonResponse([
            'success' => true,
            'message' => 'Fee configuration updated successfully.',
            'data'    => [
                'fee_id'        => $fee_id,
                'tuition_fee'   => number_format($tuition_fee,   2),
                'miscellaneous' => number_format($miscellaneous,  2),
                'other_fees'    => number_format($other_fees,     2),
                'total'         => number_format($total,          2),
            ],
        ]);
    }
}
