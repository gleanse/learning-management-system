<?php
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/AcademicPeriod.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class ReportController
{
    private $report_model;
    private $academic_period;

    public function __construct()
    {
        $this->report_model = new Report();
        $this->academic_period = new AcademicPeriod();
    }

    public function showReports()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit();
        }

        $current_period = $this->academic_period->getCurrentPeriod();
        $report_type = $_GET['type'] ?? 'enrollment';
        $interval = $_GET['interval'] ?? 'daily';

        $data = [];
        $summary = $this->report_model->getSummaryStats($current_period['school_year']);

        switch ($report_type) {
            case 'enrollment':
                $data = $this->getEnrollmentData($interval);
                break;
            case 'payment':
                $data = $this->getPaymentData($interval);
                break;
            case 'grade':
                $data['submission_rates'] = $this->report_model->getGradeSubmissionRate(
                    $current_period['school_year'],
                    $current_period['semester']
                );
                $data['grading_progress'] = $this->report_model->getGradingProgress(
                    $current_period['school_year'],
                    $current_period['semester']
                );
                break;
            case 'section':
                $data['utilization'] = $this->report_model->getSectionUtilization(
                    $current_period['school_year']
                );
                break;
            case 'teacher':
                $data['workload'] = $this->report_model->getTeacherWorkload(
                    $current_period['school_year'],
                    $current_period['semester']
                );
                break;
            case 'performance':
                $data['performance'] = $this->report_model->getStudentPerformanceSummary(
                    $current_period['school_year'],
                    $current_period['semester']
                );
                break;
        }

        require __DIR__ . '/../views/admin/reports.php';
    }

    private function getEnrollmentData($interval)
    {
        $data = [];

        switch ($interval) {
            case 'daily':
                $data['today'] = $this->report_model->getDailyEnrollments();
                $data['trends'] = $this->report_model->getEnrollmentTrends(
                    date('Y-m-d', strtotime('-30 days')),
                    date('Y-m-d'),
                    'day'
                );
                break;
            case 'weekly':
                $data['this_week'] = $this->report_model->getWeeklyEnrollments();
                $data['trends'] = $this->report_model->getEnrollmentTrends(
                    date('Y-m-d', strtotime('-12 weeks')),
                    date('Y-m-d'),
                    'week'
                );
                break;
            case 'monthly':
                $data['this_month'] = $this->report_model->getMonthlyEnrollments();
                $data['trends'] = $this->report_model->getEnrollmentTrends(
                    date('Y-m-d', strtotime('-12 months')),
                    date('Y-m-d'),
                    'month'
                );
                break;
            case 'yearly':
                $data['this_year'] = $this->report_model->getYearlyEnrollments();
                break;
        }

        return $data;
    }

    private function getPaymentData($interval)
    {
        $data = [];

        switch ($interval) {
            case 'daily':
                $data['today'] = $this->report_model->getDailyPayments();
                $data['trends'] = $this->report_model->getPaymentTrends(
                    date('Y-m-d', strtotime('-30 days')),
                    date('Y-m-d'),
                    'day'
                );
                break;
            case 'weekly':
                $data['this_week'] = $this->report_model->getWeeklyPayments();
                $data['trends'] = $this->report_model->getPaymentTrends(
                    date('Y-m-d', strtotime('-12 weeks')),
                    date('Y-m-d'),
                    'week'
                );
                break;
            case 'monthly':
                $data['this_month'] = $this->report_model->getMonthlyPayments();
                $data['trends'] = $this->report_model->getPaymentTrends(
                    date('Y-m-d', strtotime('-12 months')),
                    date('Y-m-d'),
                    'month'
                );
                break;
            case 'yearly':
                $data['this_year'] = $this->report_model->getYearlyPayments();
                break;
        }

        return $data;
    }

    public function exportReport()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit();
        }

        $report_type = $_GET['type'] ?? 'enrollment';
        $format = $_GET['format'] ?? 'csv';
        $interval = $_GET['interval'] ?? 'monthly';
        $current_period = $this->academic_period->getCurrentPeriod();

        // get data based on report type and interval
        $data = [];
        $title = '';
        $headers = [];

        switch ($report_type) {
            case 'enrollment':
                $title = 'enrollment report';
                if ($interval === 'trends') {
                    $data = $this->report_model->getEnrollmentTrends(
                        $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 year')),
                        $_GET['end_date'] ?? date('Y-m-d'),
                        $_GET['trend_interval'] ?? 'month'
                    );
                    $headers = ['period', 'enrollment_count', 'senior_high', 'college'];
                } else {
                    switch ($interval) {
                        case 'daily':
                            $data = [$this->report_model->getDailyEnrollments()];
                            $headers = ['date', 'total_enrollments', 'senior_high', 'college', 'strands_count'];
                            break;
                        case 'weekly':
                            $data = [$this->report_model->getWeeklyEnrollments()];
                            $headers = ['week', 'year', 'total_enrollments', 'strands_count', 'active_count'];
                            break;
                        case 'monthly':
                            $data = [$this->report_model->getMonthlyEnrollments()];
                            $headers = ['month', 'year', 'total_enrollments', 'sections_used', 'senior_high', 'college', 'active'];
                            break;
                        case 'yearly':
                            $data = [$this->report_model->getYearlyEnrollments()];
                            $headers = ['year', 'total_enrollments', 'months_active', 'strands_offered', 'currently_active'];
                            break;
                    }
                }
                break;

            case 'payment':
                $title = 'payment report';
                if ($interval === 'trends') {
                    $data = $this->report_model->getPaymentTrends(
                        $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 year')),
                        $_GET['end_date'] ?? date('Y-m-d'),
                        $_GET['trend_interval'] ?? 'month'
                    );
                    $headers = ['period', 'transaction_count', 'total_collected', 'payments_processed'];
                } else {
                    switch ($interval) {
                        case 'daily':
                            $data = [$this->report_model->getDailyPayments()];
                            $headers = ['date', 'transaction_count', 'payment_count', 'total_collected', 'average_payment'];
                            break;
                        case 'weekly':
                            $data = [$this->report_model->getWeeklyPayments()];
                            $headers = ['week', 'year', 'transaction_count', 'total_collected', 'fully_paid', 'partial'];
                            break;
                        case 'monthly':
                            $data = [$this->report_model->getMonthlyPayments()];
                            $headers = ['month', 'year', 'total_transactions', 'revenue', 'paying_students'];
                            break;
                        case 'yearly':
                            $data = [$this->report_model->getYearlyPayments()];
                            $headers = ['year', 'total_transactions', 'total_revenue', 'months_active', 'avg_monthly_revenue'];
                            break;
                    }
                }
                break;

            case 'teacher':
                $title = 'teacher workload report';
                $data = $this->report_model->getTeacherWorkload(
                    $current_period['school_year'],
                    $current_period['semester']
                );
                $headers = ['teacher_id', 'teacher_name', 'subjects_taught', 'sections_handled', 'total_students'];
                break;

            case 'section':
                $title = 'section utilization report';
                $data = $this->report_model->getSectionUtilization($current_period['school_year']);
                $headers = ['section_id', 'section_name', 'education_level', 'year_level', 'strand_course', 'max_capacity', 'enrolled', 'utilization', 'teachers', 'subjects'];
                break;

            case 'grade':
                $title = 'grade submission report';
                $sub_data = $this->report_model->getGradeSubmissionRate(
                    $current_period['school_year'],
                    $current_period['semester']
                );
                $grading_data = $this->report_model->getGradingProgress(
                    $current_period['school_year'],
                    $current_period['semester']
                );
                $data = [
                    'submission_rates' => $sub_data,
                    'grading_progress' => $grading_data
                ];
                break;

            case 'performance':
                $title = 'student performance summary';
                $data = $this->report_model->getStudentPerformanceSummary(
                    $current_period['school_year'],
                    $current_period['semester']
                );
                $headers = ['education_level', 'year_level', 'strand_course', 'total_students', 'avg_prelim', 'avg_midterm', 'avg_prefinal', 'avg_final', 'students_at_risk', 'students_excellent'];
                break;
        }

        if ($format === 'csv') {
            $this->exportToCsv($data, $headers, $title);
        } elseif ($format === 'pdf') {
            $this->exportToPdf($data, $headers, $title, $report_type);
        }
    }

    private function exportToCsv($data, $headers, $filename)
    {
        $filename = preg_replace('/[^a-z0-9_]/i', '_', $filename) . '_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // add headers if provided
        if (!empty($headers)) {
            fputcsv($output, $headers);
        }

        // handle nested data structures
        if (isset($data['submission_rates']) || isset($data['grading_progress'])) {
            // for grade report with multiple sections
            if (!empty($data['submission_rates'])) {
                fputcsv($output, ['teacher submission rates']);
                fputcsv($output, ['teacher_name', 'subjects_handled', 'total_students', 'grades_submitted', 'submission_rate']);
                foreach ($data['submission_rates'] as $row) {
                    fputcsv($output, [
                        $row['teacher_name'],
                        $row['subjects_handled'],
                        $row['total_students'],
                        $row['grades_submitted'],
                        round($row['submission_rate'], 2) . '%'
                    ]);
                }
            }

            if (!empty($data['grading_progress'])) {
                fputcsv($output, []);
                fputcsv($output, ['grading period progress']);
                fputcsv($output, ['grading_period', 'deadline', 'locked', 'grades_encoded', 'expected_grades', 'completion_rate']);
                foreach ($data['grading_progress'] as $row) {
                    fputcsv($output, [
                        $row['grading_period'],
                        $row['deadline_date'],
                        $row['is_locked'] ? 'yes' : 'no',
                        $row['grades_encoded'],
                        $row['expected_grades'],
                        round($row['completion_rate'], 2) . '%'
                    ]);
                }
            }
        } else {
            // regular flat data structure
            if (!empty($data) && is_array($data)) {
                // check if it's a single row or multiple rows
                if (isset($data[0]) && is_array($data[0])) {
                    // multiple rows
                    foreach ($data as $row) {
                        if (is_array($row)) {
                            fputcsv($output, array_values($row));
                        }
                    }
                } else {
                    // single row
                    fputcsv($output, array_values($data));
                }
            }
        }

        fclose($output);
        exit();
    }

    private function exportToPdf($data, $headers, $title, $report_type)
    {
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator('LMS');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle(ucwords($title) . ' - ' . date('Y-m-d'));

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set margins (left, top, right)
        $pdf->SetMargins(10, 15, 10);

        // USE LANDSCAPE for wide tables
        if ($report_type == 'section' || $report_type == 'performance' || $report_type == 'teacher') {
            $pdf->AddPage('L'); // Landscape orientation
        } else {
            $pdf->AddPage('P'); // Portrait orientation
        }

        // set font
        $pdf->SetFont('helvetica', '', 9);

        // title
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, strtoupper($title), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 8, 'Generated on: ' . date('F j, Y'), 0, 1, 'C');
        $pdf->Ln(3);

        // school year and semester
        $current = $this->academic_period->getCurrentPeriod();
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, 'School Year: ' . $current['school_year'] . ' | Semester: ' . $current['semester'], 0, 1, 'C');
        $pdf->Ln(8);

        // handle different report types
        if ($report_type == 'section') {
            $this->renderSectionTable($pdf, $data);
        } elseif ($report_type == 'performance') {
            $this->renderPerformanceTable($pdf, $data);
        } elseif ($report_type == 'teacher') {
            $this->renderTeacherTable($pdf, $data);
        } elseif ($report_type === 'grade' && isset($data['submission_rates'])) {
            // teacher submission rates table
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 8, 'Teacher Submission Rates', 0, 1, 'L');
            $pdf->Ln(2);

            $this->renderPdfTable($pdf, $data['submission_rates'], [
                'teacher_name' => 'Teacher',
                'subjects_handled' => 'Subjects',
                'total_students' => 'Students',
                'grades_submitted' => 'Submitted',
                'submission_rate' => 'Rate'
            ]);

            $pdf->Ln(8);

            // grading progress table
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 8, 'Grading Period Progress', 0, 1, 'L');
            $pdf->Ln(2);

            $this->renderPdfTable($pdf, $data['grading_progress'], [
                'grading_period' => 'Period',
                'deadline_date' => 'Deadline',
                'is_locked' => 'Locked',
                'grades_encoded' => 'Encoded',
                'expected_grades' => 'Expected',
                'completion_rate' => 'Completion'
            ]);
        } elseif (!empty($data) && is_array($data)) {
            // check if it's an array of rows or a single row
            if (isset($data[0]) && is_array($data[0])) {
                // multiple rows
                if (!empty($headers)) {
                    // create header mapping with better display names
                    $header_map = [];
                    foreach ($headers as $header) {
                        $display = ucwords(str_replace('_', ' ', $header));
                        $header_map[$header] = $display;
                    }
                    $this->renderPdfTable($pdf, $data, $header_map);
                } else {
                    // try to use first row keys as headers
                    $first_row = $data[0];
                    $header_map = [];
                    foreach (array_keys($first_row) as $key) {
                        $header_map[$key] = ucwords(str_replace('_', ' ', $key));
                    }
                    $this->renderPdfTable($pdf, $data, $header_map);
                }
            } else {
                // single row - show as key-value pairs
                $pdf->SetFont('helvetica', '', 10);

                // create a two-column layout for better readability
                $col1_width = 70;
                $col2_width = 90;

                foreach ($data as $key => $value) {
                    if (!is_array($value)) {
                        // format the value
                        if (strpos($key, 'amount') !== false || strpos($key, 'revenue') !== false || strpos($key, 'collected') !== false) {
                            if (is_numeric($value)) {
                                $value = '₱' . number_format($value, 2);
                            }
                        } elseif (strpos($key, 'rate') !== false || strpos($key, 'utilization') !== false) {
                            if (is_numeric($value)) {
                                $value = number_format($value, 2) . '%';
                            }
                        } elseif ($key === 'date' && !empty($value)) {
                            $value = date('M d, Y', strtotime($value));
                        }

                        $pdf->Cell($col1_width, 7, ucwords(str_replace('_', ' ', $key)) . ':', 0, 0, 'L');
                        $pdf->Cell($col2_width, 7, $value, 0, 1, 'L');
                    }
                }
            }
        }

        // output PDF
        $filename = preg_replace('/[^a-z0-9_]/i', '_', $title) . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D');
        exit();
    }

    private function renderPdfTable($pdf, $data, $header_map)
    {
        if (empty($data)) return;

        // calculate column widths based on content
        $col_count = count($header_map);
        $page_width = $pdf->getPageWidth() - 30; // subtract margins

        // smarter column width calculation
        $col_widths = [];
        $min_width = 25; // minimum width per column
        $max_width = 60; // maximum width per column

        // first, estimate based on header text length
        foreach (array_keys($header_map) as $field) {
            $header_text = $header_map[$field];
            $width = max($min_width, min(strlen($header_text) * 2.5, $max_width));
            $col_widths[$field] = $width;
        }

        // adjust for data content
        foreach ($data as $row) {
            foreach (array_keys($header_map) as $field) {
                $value = isset($row[$field]) ? (string)$row[$field] : '';

                // format for length estimation
                if (strpos($field, 'amount') !== false || strpos($field, 'revenue') !== false || strpos($field, 'collected') !== false) {
                    if (is_numeric($value)) {
                        $value = '₱' . number_format($value, 2);
                    }
                } elseif (strpos($field, 'rate') !== false || strpos($field, 'utilization') !== false) {
                    if (is_numeric($value)) {
                        $value = number_format($value, 2) . '%';
                    }
                }

                $content_length = strlen($value);
                $estimated_width = $content_length * 2.5;

                if ($estimated_width > $col_widths[$field]) {
                    $col_widths[$field] = min($estimated_width, $max_width);
                }
            }
        }

        // normalize widths to fit page
        $total_width = array_sum($col_widths);
        if ($total_width > $page_width) {
            $ratio = $page_width / $total_width;
            foreach ($col_widths as $field => $width) {
                $col_widths[$field] = $width * $ratio;
            }
        }

        // headers
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(240, 240, 240);

        $x_start = $pdf->GetX();
        foreach ($header_map as $field => $display) {
            $pdf->Cell($col_widths[$field], 10, $display, 1, 0, 'C', true);
        }
        $pdf->Ln();

        // data rows
        $pdf->SetFont('helvetica', '', 8);
        $fill = false;

        foreach ($data as $row) {
            $x_current = $x_start;

            foreach (array_keys($header_map) as $field) {
                $value = isset($row[$field]) ? $row[$field] : '';

                // format special fields
                if (strpos($field, 'rate') !== false || strpos($field, 'utilization') !== false) {
                    if (is_numeric($value)) {
                        $value = number_format($value, 2) . '%';
                    }
                } elseif (strpos($field, 'amount') !== false || strpos($field, 'revenue') !== false || strpos($field, 'collected') !== false) {
                    if (is_numeric($value)) {
                        $value = '₱' . number_format($value, 2);
                    }
                } elseif ($field === 'is_locked') {
                    $value = $value ? 'Yes' : 'No';
                } elseif ($field === 'date' && !empty($value)) {
                    $value = date('M d, Y', strtotime($value));
                } elseif ($field === 'deadline_date' && !empty($value)) {
                    $value = date('M d, Y', strtotime($value));
                }

                // handle long text - truncate if too long
                if (strlen($value) > 20) {
                    $value = substr($value, 0, 18) . '...';
                }

                $pdf->SetX($x_current);
                $pdf->Cell($col_widths[$field], 8, $value, 1, 0, 'L', $fill);
                $x_current += $col_widths[$field];
            }
            $pdf->Ln();
            $fill = !$fill;
        }
    }

    private function renderSectionTable($pdf, $data)
    {
        if (empty($data)) return;

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(240, 240, 240);

        // headers
        $headers = [
            'Section',
            'Level',
            'Year',
            'Strand/Course',
            'Capacity',
            'Enrolled',
            'Utilization',
            'Teachers',
            'Subjects'
        ];

        $widths = [35, 25, 20, 45, 22, 22, 28, 22, 22];

        // print headers
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();

        // data rows
        $pdf->SetFont('helvetica', '', 7.5);
        $fill = false;

        foreach ($data as $row) {
            // format education level
            $education = $row['education_level'] == 'senior_high' ? 'SHS' : 'COL';

            // format utilization with %
            $utilization = number_format($row['utilization_rate'], 1) . '%';

            // color code utilization
            if ($row['utilization_rate'] >= 90) {
                $pdf->SetTextColor(16, 185, 129); // green
            } elseif ($row['utilization_rate'] >= 70) {
                $pdf->SetTextColor(245, 158, 11); // orange
            } else {
                $pdf->SetTextColor(239, 68, 68); // red
            }

            $pdf->Cell($widths[0], 8, substr($row['section_name'], 0, 20), 1, 0, 'L', $fill);
            $pdf->SetTextColor(0, 0, 0); // reset color
            $pdf->Cell($widths[1], 8, $education, 1, 0, 'L', $fill);
            $pdf->Cell($widths[2], 8, $row['year_level'], 1, 0, 'L', $fill);
            $pdf->Cell($widths[3], 8, substr($row['strand_course'], 0, 15), 1, 0, 'L', $fill);
            $pdf->Cell($widths[4], 8, $row['max_capacity'], 1, 0, 'C', $fill);
            $pdf->Cell($widths[5], 8, $row['enrolled_students'], 1, 0, 'C', $fill);

            // utilization with color
            if ($row['utilization_rate'] >= 90) {
                $pdf->SetTextColor(16, 185, 129);
            } elseif ($row['utilization_rate'] >= 70) {
                $pdf->SetTextColor(245, 158, 11);
            } else {
                $pdf->SetTextColor(239, 68, 68);
            }
            $pdf->Cell($widths[6], 8, $utilization, 1, 0, 'C', $fill);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->Cell($widths[7], 8, $row['teachers_assigned'], 1, 0, 'C', $fill);
            $pdf->Cell($widths[8], 8, $row['subjects_offered'], 1, 0, 'C', $fill);

            $pdf->Ln();
            $fill = !$fill;
        }
    }

    private function renderPerformanceTable($pdf, $data)
    {
        if (empty($data)) return;

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(240, 240, 240);

        // headers
        $headers = [
            'Level',
            'Year',
            'Strand/Course',
            'Students',
            'Prelim',
            'Midterm',
            'Prefinal',
            'Final',
            'Risk',
            'Excellent'
        ];

        $widths = [20, 18, 40, 18, 18, 18, 18, 18, 15, 18];

        // print headers
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();

        // data rows
        $pdf->SetFont('helvetica', '', 7);
        $fill = false;

        foreach ($data as $row) {
            // format education level
            $education = $row['education_level'] == 'senior_high' ? 'SHS' : 'COL';

            $pdf->Cell($widths[0], 7, $education, 1, 0, 'L', $fill);
            $pdf->Cell($widths[1], 7, $row['year_level'], 1, 0, 'L', $fill);
            $pdf->Cell($widths[2], 7, substr($row['strand_course'], 0, 15), 1, 0, 'L', $fill);
            $pdf->Cell($widths[3], 7, $row['total_students'], 1, 0, 'C', $fill);

            // grades with color coding
            $this->renderGradeCell($pdf, $row['avg_prelim'] ?? null, $widths[4], $fill);
            $this->renderGradeCell($pdf, $row['avg_midterm'] ?? null, $widths[5], $fill);
            $this->renderGradeCell($pdf, $row['avg_prefinal'] ?? null, $widths[6], $fill);
            $this->renderGradeCell($pdf, $row['avg_final'] ?? null, $widths[7], $fill);

            // at risk (red)
            if ($row['students_at_risk'] > 0) {
                $pdf->SetTextColor(239, 68, 68);
            }
            $pdf->Cell($widths[8], 7, $row['students_at_risk'], 1, 0, 'C', $fill);
            $pdf->SetTextColor(0, 0, 0);

            // excellent (green)
            if ($row['students_excellent'] > 0) {
                $pdf->SetTextColor(16, 185, 129);
            }
            $pdf->Cell($widths[9], 7, $row['students_excellent'], 1, 0, 'C', $fill);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->Ln();
            $fill = !$fill;
        }
    }

    private function renderTeacherTable($pdf, $data)
    {
        if (empty($data)) return;

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(240, 240, 240);

        // headers
        $headers = ['Teacher Name', 'Subjects', 'Sections', 'Students', 'Workload'];
        $widths = [70, 25, 25, 30, 40];

        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();

        // data rows
        $pdf->SetFont('helvetica', '', 7.5);
        $fill = false;

        foreach ($data as $row) {
            $workload = round($row['total_students'] / max($row['subjects_taught'], 1), 1);

            $pdf->Cell($widths[0], 8, substr($row['teacher_name'], 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell($widths[1], 8, $row['subjects_taught'], 1, 0, 'C', $fill);
            $pdf->Cell($widths[2], 8, $row['sections_handled'], 1, 0, 'C', $fill);
            $pdf->Cell($widths[3], 8, $row['total_students'], 1, 0, 'C', $fill);

            // color code workload
            if ($workload > 40) {
                $pdf->SetTextColor(239, 68, 68); // red - overloaded
            } elseif ($workload > 25) {
                $pdf->SetTextColor(245, 158, 11); // orange - medium
            } else {
                $pdf->SetTextColor(16, 185, 129); // green - light
            }
            $pdf->Cell($widths[4], 8, $workload . ' std/subj', 1, 0, 'C', $fill);
            $pdf->SetTextColor(0, 0, 0);

            $pdf->Ln();
            $fill = !$fill;
        }
    }

    private function renderGradeCell($pdf, $value, $width, $fill)
    {
        if ($value) {
            if ($value >= 90) {
                $pdf->SetTextColor(16, 185, 129); // green
            } elseif ($value >= 75) {
                $pdf->SetTextColor(0, 0, 0); // black
            } else {
                $pdf->SetTextColor(239, 68, 68); // red
            }
            $pdf->Cell($width, 7, number_format($value, 1), 1, 0, 'C', $fill);
        } else {
            $pdf->SetTextColor(156, 163, 175); // gray
            $pdf->Cell($width, 7, '—', 1, 0, 'C', $fill);
        }
        $pdf->SetTextColor(0, 0, 0);
    }
}
