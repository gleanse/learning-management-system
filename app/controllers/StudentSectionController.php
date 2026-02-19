<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../models/AcademicPeriod.php';

class StudentSectionController
{
    private $student_model;
    private $section_model;
    private $academic_model;

    public function __construct()
    {
        $this->student_model  = new Student();
        $this->section_model  = new Section();
        $this->academic_model = new AcademicPeriod();
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
                $this->jsonResponse(['success' => false, 'message' => 'unauthorized access.'], 403);
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
                $this->jsonResponse(['success' => false, 'message' => 'session expired. please refresh and try again.'], 403);
            }

            $_SESSION['student_section_errors'] = ['general' => 'session expired. please try again.'];
            header('Location: index.php?page=student_sections');
            exit();
        }
    }

    public function showAssignmentPage()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // pull from active period instead of hardcoding
        $current = $this->academic_model->getCurrentPeriod();
        $school_year    = $current['school_year'] ?? '';

        $sections           = $this->section_model->getAllSectionsWithStudentCount($school_year);
        $recent_assignments = $this->student_model->getRecentAssignments(15);

        $errors = $_SESSION['student_section_errors'] ?? [];
        $success_message = $_SESSION['student_section_success'] ?? null;
        unset($_SESSION['student_section_errors'], $_SESSION['student_section_success']);

        require __DIR__ . '/../views/admin/student_sections.php';
    }

    // ajax endpoint get section info with current students and eligible students
    public function getSectionData()
    {
        $this->requireAdmin();

        if (!$this->isAjax()) {
            header('Location: index.php?page=student_sections');
            exit();
        }

        $section_id = isset($_GET['section_id']) ? (int) $_GET['section_id'] : null;

        if (empty($section_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'section id is required.']);
        }

        $section = $this->section_model->getSectionWithStudentCount($section_id);

        if (!$section) {
            $this->jsonResponse(['success' => false, 'message' => 'section not found.']);
        }

        $available_slots = $this->section_model->getAvailableSlots($section_id);
        $current_students = $this->student_model->getStudentsBySection($section_id);
        $eligible_students = $this->student_model->getEligibleStudentsForSection($section_id);

        $this->jsonResponse([
            'success' => true,
            'section' => [
                'section_id' => $section['section_id'],
                'section_name' => $section['section_name'],
                'education_level' => $section['education_level'],
                'year_level' => $section['year_level'],
                'strand_course' => $section['strand_course'],
                'max_capacity' => $section['max_capacity'],
                'student_count' => $section['student_count'],
                'available_slots' => $available_slots
            ],
            'current_students' => $current_students,
            'eligible_students' => $eligible_students
        ]);
    }

    // ajax endpoint search eligible students for a section
    public function searchEligibleStudents()
    {
        $this->requireAdmin();

        if (!$this->isAjax()) {
            header('Location: index.php?page=student_sections');
            exit();
        }

        $section_id = isset($_GET['section_id']) ? (int) $_GET['section_id'] : null;
        $search = $_GET['search'] ?? '';

        if (empty($section_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'section id is required.']);
        }

        $eligible_students = $this->student_model->getEligibleStudentsForSection($section_id, null, null, $search);

        $this->jsonResponse([
            'success' => true,
            'students' => $eligible_students
        ]);
    }

    // ajax endpoint search current students in a section
    public function searchCurrentStudents()
    {
        $this->requireAdmin();

        if (!$this->isAjax()) {
            header('Location: index.php?page=student_sections');
            exit();
        }

        $section_id = isset($_GET['section_id']) ? (int) $_GET['section_id'] : null;
        $search = $_GET['search'] ?? '';

        if (empty($section_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'section id is required.']);
        }

        $current_students = $this->student_model->getStudentsBySectionWithPagination($section_id, 1000, 0, $search);

        $this->jsonResponse([
            'success' => true,
            'students' => $current_students
        ]);
    }

    public function processAssignment()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;
        $student_ids = isset($_POST['student_ids']) && is_array($_POST['student_ids'])
            ? array_map('intval', $_POST['student_ids'])
            : [];

        $errors = [];

        if (empty($section_id)) {
            $errors['section_id'] = 'please select a section.';
        }

        if (empty($student_ids)) {
            $errors['student_ids'] = 'please select at least one student.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }
            $_SESSION['student_section_errors'] = $errors;
            header('Location: index.php?page=student_sections');
            exit();
        }

        // validate section exists
        $section = $this->section_model->getSectionWithStudentCount($section_id);
        if (!$section) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'invalid section selected.']);
            }
            $_SESSION['student_section_errors'] = ['section_id' => 'invalid section selected.'];
            header('Location: index.php?page=student_sections');
            exit();
        }

        // check capacity
        $available_slots = $this->section_model->getAvailableSlots($section_id);
        $requested_count = count($student_ids);

        if ($requested_count > $available_slots) {
            $message = "cannot assign {$requested_count} student(s). only {$available_slots} slot(s) available in this section.";
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => $message]);
            }
            $_SESSION['student_section_errors'] = ['capacity' => $message];
            header('Location: index.php?page=student_sections');
            exit();
        }

        // validate all students are eligible (match education_level, year_level, strand_course)
        foreach ($student_ids as $student_id) {
            $student = $this->student_model->getStudentById($student_id);
            if (!$student) {
                $this->jsonResponse(['success' => false, 'message' => 'invalid student selected.']);
            }

            if ($student['section_id'] !== null) {
                $message = "student {$student['first_name']} {$student['last_name']} is already assigned to a section.";
                if ($this->isAjax()) {
                    $this->jsonResponse(['success' => false, 'message' => $message]);
                }
                $_SESSION['student_section_errors'] = ['student' => $message];
                header('Location: index.php?page=student_sections');
                exit();
            }

            // strict validation must match section's education_level, year_level, and strand_course
            if (
                $student['education_level'] !== $section['education_level'] ||
                $student['year_level'] !== $section['year_level'] ||
                $student['strand_course'] !== $section['strand_course']
            ) {

                $message = "student {$student['first_name']} {$student['last_name']} does not match this section's requirements.";
                if ($this->isAjax()) {
                    $this->jsonResponse(['success' => false, 'message' => $message]);
                }
                $_SESSION['student_section_errors'] = ['mismatch' => $message];
                header('Location: index.php?page=student_sections');
                exit();
            }
        }

        // assign students
        if (count($student_ids) === 1) {
            $result = $this->student_model->assignToSection($student_ids[0], $section_id, $_SESSION['user_id']);
        } else {
            $result = $this->student_model->assignMultipleToSection($student_ids, $section_id, $_SESSION['user_id']);
        }

        if ($result) {
            $count = count($student_ids);
            $message = $count === 1 ? 'student assigned successfully.' : "{$count} students assigned successfully.";

            if ($this->isAjax()) {
                // return updated section data
                $updated_section = $this->section_model->getSectionWithStudentCount($section_id);
                $updated_available = $this->section_model->getAvailableSlots($section_id);
                $current_students = $this->student_model->getStudentsBySection($section_id);
                $eligible_students = $this->student_model->getEligibleStudentsForSection($section_id);
                $recent_assignments = $this->student_model->getRecentAssignments(15);

                $this->jsonResponse([
                    'success' => true,
                    'message' => $message,
                    'section' => [
                        'student_count' => $updated_section['student_count'],
                        'available_slots' => $updated_available
                    ],
                    'current_students' => $current_students,
                    'eligible_students' => $eligible_students,
                    'recent_assignments' => $recent_assignments
                ]);
            }

            $_SESSION['student_section_success'] = $message;
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'failed to assign students. please try again.']);
            }
            $_SESSION['student_section_errors'] = ['general' => 'failed to assign students. please try again.'];
        }

        header('Location: index.php?page=student_sections');
        exit();
    }

    public function processRemoveStudent()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : null;
        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;

        $errors = [];

        if (empty($student_id)) {
            $errors['student_id'] = 'student id is required.';
        }

        if (empty($section_id)) {
            $errors['section_id'] = 'section id is required.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }
            $_SESSION['student_section_errors'] = $errors;
            header('Location: index.php?page=student_sections');
            exit();
        }

        // validate student is in this section
        $student = $this->student_model->getStudentById($student_id);
        if (!$student || $student['section_id'] != $section_id) {
            $message = 'student is not assigned to this section.';
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => $message]);
            }
            $_SESSION['student_section_errors'] = ['student' => $message];
            header('Location: index.php?page=student_sections');
            exit();
        }

        $result = $this->student_model->removeFromSection($student_id);

        if ($result) {
            $student_name = "{$student['first_name']} {$student['last_name']}";
            $message = "student {$student_name} removed from section successfully.";

            if ($this->isAjax()) {
                // return updated section data
                $updated_section = $this->section_model->getSectionWithStudentCount($section_id);
                $updated_available = $this->section_model->getAvailableSlots($section_id);
                $current_students = $this->student_model->getStudentsBySection($section_id);
                $eligible_students = $this->student_model->getEligibleStudentsForSection($section_id);

                $this->jsonResponse([
                    'success' => true,
                    'message' => $message,
                    'section' => [
                        'student_count' => $updated_section['student_count'],
                        'available_slots' => $updated_available
                    ],
                    'current_students' => $current_students,
                    'eligible_students' => $eligible_students
                ]);
            }

            $_SESSION['student_section_success'] = $message;
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'failed to remove student. please try again.']);
            }
            $_SESSION['student_section_errors'] = ['general' => 'failed to remove student. please try again.'];
        }

        header('Location: index.php?page=student_sections');
        exit();
    }

    public function processBulkRemove()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $student_ids = isset($_POST['student_ids']) && is_array($_POST['student_ids'])
            ? array_map('intval', $_POST['student_ids'])
            : [];
        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;

        $errors = [];

        if (empty($student_ids)) {
            $errors['student_ids'] = 'please select at least one student.';
        }

        if (empty($section_id)) {
            $errors['section_id'] = 'section id is required.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }
            $_SESSION['student_section_errors'] = $errors;
            header('Location: index.php?page=student_sections');
            exit();
        }

        $removed_count = 0;
        foreach ($student_ids as $student_id) {
            $student = $this->student_model->getStudentById($student_id);
            if ($student && $student['section_id'] == $section_id) {
                if ($this->student_model->removeFromSection($student_id)) {
                    $removed_count++;
                }
            }
        }

        if ($removed_count > 0) {
            $message = "{$removed_count} student(s) removed from section successfully.";

            if ($this->isAjax()) {
                $updated_section = $this->section_model->getSectionWithStudentCount($section_id);
                $updated_available = $this->section_model->getAvailableSlots($section_id);
                $current_students = $this->student_model->getStudentsBySection($section_id);
                $eligible_students = $this->student_model->getEligibleStudentsForSection($section_id);

                $this->jsonResponse([
                    'success' => true,
                    'message' => $message,
                    'section' => [
                        'student_count' => $updated_section['student_count'],
                        'available_slots' => $updated_available
                    ],
                    'current_students' => $current_students,
                    'eligible_students' => $eligible_students
                ]);
            }

            $_SESSION['student_section_success'] = $message;
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'no students were removed.']);
            }
            $_SESSION['student_section_errors'] = ['general' => 'no students were removed.'];
        }

        header('Location: index.php?page=student_sections');
        exit();
    }
}
