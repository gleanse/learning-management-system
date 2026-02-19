<?php

require_once __DIR__ . '/../models/Enrollment.php';
require_once __DIR__ . '/../models/AcademicPeriod.php';

class EnrollmentController
{
    private $enrollment_model;
    private $academic_model;

    public function __construct()
    {
        $this->enrollment_model = new Enrollment();
        $this->academic_model   = new AcademicPeriod();
    }

    private function requireRegistrar()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'registrar') {
            header('Location: index.php?page=login');
            exit();
        }
    }

    public function showEnrollmentForm()
    {
        $this->requireRegistrar();

        $draft = $this->enrollment_model->getDraft();

        // get active academic period
        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $is_validation_error = !empty($_SESSION['enrollment_errors']);

        $errors    = $_SESSION['enrollment_errors'] ?? [];
        $form_data = $_SESSION['enrollment_form_data'] ?? [];
        unset($_SESSION['enrollment_errors'], $_SESSION['enrollment_form_data']);

        if (empty($form_data) && !empty($draft)) {
            $form_data = $draft;
        }

        // always override with active period — registrar cant change these
        $form_data['school_year'] = $school_year;
        $form_data['semester']    = $semester;

        $fee_config = $this->buildFeeConfig(
            $form_data['year_level']      ?? '',
            $form_data['education_level'] ?? '',
            $form_data['strand_course']   ?? ''
        );

        require __DIR__ . '/../views/registrar/enrollment_create.php';
    }

    public function getSections()
    {
        $this->requireRegistrar();
        header('Content-Type: application/json');

        $education_level = $_GET['education_level'] ?? '';
        $year_level      = $_GET['year_level']      ?? '';
        $strand_course   = $_GET['strand_course']   ?? '';

        // school_year from active period, not from client
        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';

        if (!$education_level || !$year_level || !$strand_course || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }

        $sections = $this->enrollment_model->getSectionsByFilter($education_level, $year_level, $strand_course, $school_year);
        echo json_encode(['success' => true, 'data' => $sections]);
    }

    public function getSubjects()
    {
        $this->requireRegistrar();

        header('Content-Type: application/json');

        $section_id  = (int) ($_GET['section_id']  ?? 0);
        $school_year = $_GET['school_year'] ?? '';

        if (!$section_id || !$school_year) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }

        $subjects = $this->enrollment_model->getSubjectsBySection($section_id, $school_year);
        echo json_encode(['success' => true, 'data' => $subjects]);
    }

    public function getFees()
    {
        $this->requireRegistrar();
        header('Content-Type: application/json');

        $year_level      = $_GET['year_level']      ?? '';
        $education_level = $_GET['education_level'] ?? '';
        $strand_course   = $_GET['strand_course']   ?? '';

        if (!$year_level || !$education_level || !$strand_course) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }

        $fee_config = $this->buildFeeConfig($year_level, $education_level, $strand_course);
        echo json_encode(['success' => true, 'data' => $fee_config]);
    }

    public function checkDuplicateName()
    {
        $this->requireRegistrar();

        header('Content-Type: application/json');

        $first_name  = trim($_GET['first_name']  ?? '');
        $last_name   = trim($_GET['last_name']   ?? '');
        $middle_name = trim($_GET['middle_name'] ?? '');

        if (!$first_name || !$last_name) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }

        $matches = $this->enrollment_model->checkDuplicateName($first_name, $last_name, $middle_name);

        echo json_encode([
            'success'  => true,
            'has_match' => !empty($matches),
            'matches'  => $matches,
        ]);
    }

    public function saveDraft()
    {
        $this->requireRegistrar();

        header('Content-Type: application/json');

        if (empty($_POST)) {
            echo json_encode(['success' => false, 'message' => 'No data to save']);
            return;
        }

        $this->enrollment_model->saveDraft($this->sanitize($_POST));
        echo json_encode(['success' => true, 'message' => 'Draft saved']);
    }

    public function store()
    {
        $this->requireRegistrar();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=enrollment_create');
            exit();
        }

        $data   = $this->sanitize($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['enrollment_errors']    = $errors;
            $_SESSION['enrollment_form_data'] = $data;
            header('Location: index.php?page=enrollment_create');
            exit();
        }

        $registrar_id = $_SESSION['user_id'];
        $student_id   = $this->enrollment_model->enrollNewStudent($data, $registrar_id);

        if (!$student_id) {
            $_SESSION['enrollment_errors']    = ['general' => 'Enrollment failed, please try again'];
            $_SESSION['enrollment_form_data'] = $data;
            header('Location: index.php?page=enrollment_create');
            exit();
        }

        $this->enrollment_model->clearDraft();

        $_SESSION['enrollment_success'] = [
            'student_id'   => $student_id,
            'student_name' => $data['first_name'] . ' ' . $data['last_name'],
        ];

        header('Location: index.php?page=enrollment_success');
        exit();
    }

    public function showSuccess()
    {
        $this->requireRegistrar();

        if (empty($_SESSION['enrollment_success'])) {
            header('Location: index.php?page=enrollment_create');
            exit();
        }

        $success_data = $_SESSION['enrollment_success'];
        $student      = $this->enrollment_model->getStudentWithDetails($success_data['student_id']);

        // clear so refreshing doesnt re-show the success page
        unset($_SESSION['enrollment_success']);

        require __DIR__ . '/../views/registrar/enrollment_success.php';
    }

    private function validate($data)
    {
        $errors = [];

        // personal info
        if (empty($data['first_name']))        $errors['first_name']       = 'First name is required';
        if (empty($data['last_name']))         $errors['last_name']        = 'Last name is required';
        if (empty($data['guardian_name']))     $errors['guardian_name']    = 'Guardian name is required';
        if (empty($data['guardian_contact']))  $errors['guardian_contact'] = 'Guardian contact is required';
        if (empty($data['gender']))            $errors['gender']           = 'Gender is required';

        // date of birth required + age range 10–100
        if (empty($data['date_of_birth'])) {
            $errors['date_of_birth'] = 'Date of birth is required';
        } else {
            $dob = new DateTime($data['date_of_birth']);
            $age = $dob->diff(new DateTime('today'))->y;
            if ($age < 10)  $errors['date_of_birth'] = 'Student must be at least 10 years old to enroll';
            if ($age > 100) $errors['date_of_birth'] = 'Please enter a valid date of birth';
        }

        // lrn required only for senior high
        if (!empty($data['education_level']) && $data['education_level'] === 'senior_high' && empty($data['lrn'])) {
            $errors['lrn'] = 'LRN is required for Senior High School enrollment';
        }

        // academic details
        if (empty($data['education_level']))  $errors['education_level'] = 'Education level is required';
        if (empty($data['year_level']))       $errors['year_level']      = 'Grade level is required';
        if (empty($data['strand_course']))    $errors['strand_course']   = 'Strand or course is required';
        if (empty($data['school_year']))      $errors['school_year']     = 'School year is required';
        if (empty($data['semester']))         $errors['semester']        = 'Semester is required';

        // total amount must be greater than zero — means fee config exists for this course
        if (empty($data['total_amount']) || (float) $data['total_amount'] <= 0) {
            $errors['total_amount'] = 'Fee configuration not set for this course and school year. Please contact the administrator.';
        }

        return $errors;
    }

    // builds fee config array — now requires strand_course since fees are per course
    // returns zeros if no config found for that combination
    private function buildFeeConfig($year_level, $education_level, $strand_course)
    {
        $config = [
            'tuition_fee'   => 0,
            'miscellaneous' => 0,
            'other_fees'    => 0,
            'total'         => 0,
        ];

        if (!$year_level || !$education_level || !$strand_course) {
            return $config;
        }

        $row = $this->enrollment_model->getFeeConfig($year_level, $education_level, $strand_course);

        if ($row) {
            $config['tuition_fee']   = (float) $row['tuition_fee'];
            $config['miscellaneous'] = (float) $row['miscellaneous'];
            $config['other_fees']    = (float) $row['other_fees'];
        }

        $config['total'] = $config['tuition_fee'] + $config['miscellaneous'] + $config['other_fees'];

        return $config;
    }

    // sanitize all string inputs recursively
    private function sanitize($data)
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = $this->sanitize($value);
            } else {
                $clean[$key] = htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $clean;
    }
}
