<?php

require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../models/Student.php';

class AssignStudentController
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

            $_SESSION['assign_errors'] = ['general' => 'Your session expired. Please try again.'];
            header('Location: index.php?page=assign_students');
            exit();
        }
    }

    // show assign students page
    public function showAssignStudents()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        $year_level_filter = $_GET['year_level'] ?? '';

        // get unassigned students with pagination
        $students = $this->student_model->getUnassignedStudents($limit, $offset, $search);
        $total_students = $this->student_model->getTotalUnassignedCount($search);
        $total_pages = ceil($total_students / $limit);

        // get all sections with availability
        $sections = $this->section_model->getSectionsWithAvailability('2025-2026');

        $errors = $_SESSION['assign_errors'] ?? [];
        $success_message = $_SESSION['assign_success'] ?? null;
        unset($_SESSION['assign_errors'], $_SESSION['assign_success']);

        require __DIR__ . '/../views/admin/assign_students.php';
    }

    // ajax search students for assignment
    public function ajaxSearchStudents()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        $year_level_filter = $_GET['year_level'] ?? '';

        // determine which students to fetch based on filter
        if (!empty($year_level_filter)) {
            $students = $this->student_model->getStudentsByYearLevel($year_level_filter, $limit, $offset, $search);
            $total_students = $this->student_model->getTotalStudentsByYearLevelCount($year_level_filter, $search);
        } else {
            $students = $this->student_model->getUnassignedStudents($limit, $offset, $search);
            $total_students = $this->student_model->getTotalUnassignedCount($search);
        }

        $total_pages = ceil($total_students / $limit);

        $this->jsonResponse([
            'success' => true,
            'students' => $students,
            'total_students' => $total_students,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
    }

    // ajax get section info
    public function ajaxGetSectionInfo()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : null;

        if (!$section_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Section ID is required.'], 400);
        }

        $section = $this->section_model->getSectionWithStudentCount($section_id);

        if (!$section) {
            $this->jsonResponse(['success' => false, 'message' => 'Section not found.'], 404);
        }

        $available_slots = $this->section_model->getAvailableSlots($section_id);

        $this->jsonResponse([
            'success' => true,
            'section' => [
                'section_id' => $section['section_id'],
                'section_name' => $section['section_name'],
                'education_level' => $section['education_level'],
                'year_level' => $section['year_level'],
                'strand_course' => $section['strand_course'],
                'max_capacity' => $section['max_capacity'],
                'current_students' => $section['student_count'],
                'available_slots' => $available_slots,
                'school_year' => $section['school_year']
            ]
        ]);
    }

    // process single student assignment
    public function processAssignStudent()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : null;
        $section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : null;

        $errors = [];

        if (!$student_id) {
            $errors['student_id'] = 'Student ID is required.';
        }

        if (!$section_id) {
            $errors['section_id'] = 'Section is required.';
        }

        // validate student exists
        if ($student_id) {
            $student = $this->student_model->getStudentById($student_id);
            if (!$student) {
                $errors['student_id'] = 'Student not found.';
            }
        }

        // validate section exists and has space
        if ($section_id) {
            $section = $this->section_model->getSectionById($section_id);
            if (!$section) {
                $errors['section_id'] = 'Section not found.';
            } elseif (!$this->section_model->hasAvailableSlots($section_id)) {
                $errors['section_id'] = 'Section is full. No available slots.';
            }
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            }
            $_SESSION['assign_errors'] = $errors;
            header('Location: index.php?page=assign_students');
            exit();
        }

        $result = $this->student_model->assignToSection($student_id, $section_id);

        if ($result) {
            // log the assignment with admin info
            $this->logAssignment($student_id, $section_id, $_SESSION['user_id']);

            if ($this->isAjax()) {
                $this->jsonResponse(['success' => true, 'message' => 'Student assigned successfully.']);
            }
            $_SESSION['assign_success'] = 'Student assigned successfully.';
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to assign student. Please try again.'], 500);
            }
            $_SESSION['assign_errors'] = ['general' => 'Failed to assign student. Please try again.'];
        }

        header('Location: index.php?page=assign_students');
        exit();
    }

    // process bulk student assignment
    public function processBulkAssignStudents()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $student_ids = isset($_POST['student_ids']) ? $_POST['student_ids'] : [];
        $section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : null;

        $errors = [];

        if (empty($student_ids) || !is_array($student_ids)) {
            $errors['student_ids'] = 'Please select at least one student.';
        }

        if (!$section_id) {
            $errors['section_id'] = 'Section is required.';
        }

        // validate section exists
        if ($section_id) {
            $section = $this->section_model->getSectionById($section_id);
            if (!$section) {
                $errors['section_id'] = 'Section not found.';
            } else {
                // check if section has enough space
                $available_slots = $this->section_model->getAvailableSlots($section_id);
                if (count($student_ids) > $available_slots) {
                    $errors['section_id'] = "Section only has {$available_slots} available slot(s), but you selected " . count($student_ids) . " student(s).";
                }
            }
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
            }
            $_SESSION['assign_errors'] = $errors;
            header('Location: index.php?page=assign_students');
            exit();
        }

        $result = $this->student_model->assignMultipleToSection($student_ids, $section_id);

        if ($result) {
            // log bulk assignments
            foreach ($student_ids as $student_id) {
                $this->logAssignment($student_id, $section_id, $_SESSION['user_id']);
            }

            $count = count($student_ids);
            $message = "{$count} student(s) assigned successfully.";

            if ($this->isAjax()) {
                $this->jsonResponse(['success' => true, 'message' => $message]);
            }
            $_SESSION['assign_success'] = $message;
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to assign students. Please try again.'], 500);
            }
            $_SESSION['assign_errors'] = ['general' => 'Failed to assign students. Please try again.'];
        }

        header('Location: index.php?page=assign_students');
        exit();
    }

    // get recent assignments for display
    public function ajaxGetRecentAssignments()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $assignments = $this->student_model->getRecentAssignments($limit);

        $this->jsonResponse([
            'success' => true,
            'assignments' => $assignments
        ]);
    }

    // log assignment to database
    private function logAssignment($student_id, $section_id, $assigned_by_user_id)
    {
        return $this->student_model->logAssignment($student_id, $section_id, $assigned_by_user_id);
    }

    // ajax get sections by filters
    public function ajaxGetSectionsByFilter()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $year_level = $_GET['year_level'] ?? null;
        $education_level = $_GET['education_level'] ?? null;
        $school_year = $_GET['school_year'] ?? '2025-2026';

        $sections = $this->section_model->getSectionsWithAvailability($school_year, $year_level, $education_level);

        $this->jsonResponse([
            'success' => true,
            'sections' => $sections
        ]);
    }

    // remove student from section
    public function processRemoveFromSection()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : null;

        if (!$student_id) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Student ID is required.'], 400);
            }
            $_SESSION['assign_errors'] = ['general' => 'Student ID is required.'];
            header('Location: index.php?page=assign_students');
            exit();
        }

        $result = $this->student_model->removeFromSection($student_id);

        if ($result) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => true, 'message' => 'Student removed from section successfully.']);
            }
            $_SESSION['assign_success'] = 'Student removed from section successfully.';
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to remove student. Please try again.'], 500);
            }
            $_SESSION['assign_errors'] = ['general' => 'Failed to remove student. Please try again.'];
        }

        header('Location: index.php?page=assign_students');
        exit();
    }
}
