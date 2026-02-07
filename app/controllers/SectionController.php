<?php

require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../models/Student.php';

class SectionController
{
    private $section_model;
    private $student_model;

    public function __construct()
    {
        $this->section_model = new Section();
        $this->student_model = new Student();
    }

    private function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    private function jsonResponse($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    private function requireAdmin()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
            header('Location: index.php?page=login');
            exit();
        }
    }

    private function validateCsrf()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Your session expired. Please refresh and try again.'], 403);
            }

            $_SESSION['section_errors'] = ['general' => 'Your session expired. Please try again.'];
            header('Location: index.php?page=manage_sections');
            exit();
        }
    }

    public function showManageSections()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $school_year = $_GET['school_year'] ?? '2025-2026';
        $sections = $this->section_model->getAllSectionsWithStudentCount($school_year);

        $errors = $_SESSION['section_errors'] ?? [];
        $success_message = $_SESSION['section_success'] ?? null;
        unset($_SESSION['section_errors'], $_SESSION['section_success']);

        require __DIR__ . '/../views/admin/manage_sections.php';
    }

    public function processCreateSection()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $section_name = trim($_POST['section_name'] ?? '');
        $education_level = $_POST['education_level'] ?? '';
        $year_level = trim($_POST['year_level'] ?? '');
        $strand_course = trim($_POST['strand_course'] ?? '');
        $max_capacity = trim($_POST['max_capacity'] ?? '');
        $school_year = $_POST['school_year'] ?? '2025-2026';

        $errors = [];

        // validate required fields
        if (empty($section_name)) {
            $errors['section_name'] = 'Section name is required.';
        }

        if (empty($education_level)) {
            $errors['education_level'] = 'Education level is required.';
        } elseif (!in_array($education_level, ['senior_high', 'college'])) {
            $errors['education_level'] = 'Invalid education level.';
        }

        if (empty($year_level)) {
            $errors['year_level'] = 'Year level is required.';
        }

        if (empty($strand_course)) {
            $errors['strand_course'] = 'Strand/Course is required.';
        }

        // validate max capacity (optional but must be numeric if provided)
        $capacity_value = null;
        if (!empty($max_capacity)) {
            if (!is_numeric($max_capacity) || intval($max_capacity) < 1) {
                $errors['max_capacity'] = 'Maximum capacity must be a positive number.';
            } else {
                $capacity_value = intval($max_capacity);
            }
        }

        // check if section name already exists for this school year
        if (empty($errors) && $this->section_model->sectionExists($section_name, $school_year)) {
            $errors['section_name'] = 'Section name already exists for this school year.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }
            $_SESSION['section_errors'] = $errors;
            header('Location: index.php?page=manage_sections');
            exit();
        }

        // create section
        $result = $this->section_model->create(
            $section_name,
            $education_level,
            $year_level,
            $strand_course,
            $capacity_value,
            $school_year
        );

        if ($result) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => true, 'message' => 'Section created successfully.']);
            }
            $_SESSION['section_success'] = 'Section created successfully.';
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to create section. Please try again.']);
            }
            $_SESSION['section_errors'] = ['general' => 'Failed to create section. Please try again.'];
        }

        header('Location: index.php?page=manage_sections');
        exit();
    }

    public function processUpdateSection()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : null;
        $section_name = trim($_POST['section_name'] ?? '');
        $education_level = $_POST['education_level'] ?? '';
        $year_level = trim($_POST['year_level'] ?? '');
        $strand_course = trim($_POST['strand_course'] ?? '');
        $max_capacity = trim($_POST['max_capacity'] ?? '');
        $school_year = $_POST['school_year'] ?? '2025-2026';

        $errors = [];

        if (empty($section_id)) {
            $errors['section_id'] = 'Section ID is required.';
        }

        if (empty($section_name)) {
            $errors['section_name'] = 'Section name is required.';
        }

        if (empty($education_level)) {
            $errors['education_level'] = 'Education level is required.';
        } elseif (!in_array($education_level, ['senior_high', 'college'])) {
            $errors['education_level'] = 'Invalid education level.';
        }

        if (empty($year_level)) {
            $errors['year_level'] = 'Year level is required.';
        }

        if (empty($strand_course)) {
            $errors['strand_course'] = 'Strand/Course is required.';
        }

        // validate max capacity
        $capacity_value = null;
        if (!empty($max_capacity)) {
            if (!is_numeric($max_capacity) || intval($max_capacity) < 1) {
                $errors['max_capacity'] = 'Maximum capacity must be a positive number.';
            } else {
                $capacity_value = intval($max_capacity);
            }
        }

        // check if section name already exists (excluding current section)
        if (empty($errors) && $this->section_model->sectionExists($section_name, $school_year, $section_id)) {
            $errors['section_name'] = 'Section name already exists for this school year.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }
            $_SESSION['section_errors'] = $errors;
            header('Location: index.php?page=manage_sections');
            exit();
        }

        // update section
        $result = $this->section_model->update(
            $section_id,
            $section_name,
            $education_level,
            $year_level,
            $strand_course,
            $capacity_value,
            $school_year
        );

        if ($result) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => true, 'message' => 'Section updated successfully.']);
            }
            $_SESSION['section_success'] = 'Section updated successfully.';
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update section. Please try again.']);
            }
            $_SESSION['section_errors'] = ['general' => 'Failed to update section. Please try again.'];
        }

        header('Location: index.php?page=manage_sections');
        exit();
    }

    public function processDeleteSection()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : null;

        if (empty($section_id)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Section ID is required.']);
            }
            $_SESSION['section_errors'] = ['general' => 'Section ID is required.'];
            header('Location: index.php?page=manage_sections');
            exit();
        }

        // attempt to delete
        $result = $this->section_model->delete($section_id);

        if ($result) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => true, 'message' => 'Section deleted successfully.']);
            }
            $_SESSION['section_success'] = 'Section deleted successfully.';
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Cannot delete section. There are students enrolled in this section.']);
            }
            $_SESSION['section_errors'] = ['general' => 'Cannot delete section. There are students enrolled in this section.'];
        }

        header('Location: index.php?page=manage_sections');
        exit();
    }

    public function showViewSection()
    {
        $this->requireAdmin();

        $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : null;

        if (empty($section_id)) {
            $_SESSION['section_errors'] = ['general' => 'Invalid section.'];
            header('Location: index.php?page=manage_sections');
            exit();
        }

        // get section details with student count
        $section = $this->section_model->getSectionWithStudentCount($section_id);

        if (!$section) {
            $_SESSION['section_errors'] = ['general' => 'Section not found.'];
            header('Location: index.php?page=manage_sections');
            exit();
        }

        // get students in this section
        $students = $this->student_model->getStudentsBySection($section_id);

        require __DIR__ . '/../views/admin/view_section.php';
    }
}
