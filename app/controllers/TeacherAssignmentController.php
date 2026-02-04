<?php

require_once __DIR__ . '/../models/TeacherAssignment.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../models/Subject.php';

class TeacherAssignmentController
{
    private $assignment_model;
    private $teacher_model;
    private $section_model;
    private $subject_model;

    public function __construct()
    {
        $this->assignment_model = new TeacherAssignment();
        $this->teacher_model = new Teacher();
        $this->section_model = new Section();
        $this->subject_model = new Subject();
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

            $_SESSION['assignment_errors'] = ['general' => 'Your session expired. Please try again.'];
            header('Location: index.php?page=teacher_assignments');
            exit();
        }
    }

    public function showAssignmentPage()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $teachers = $this->teacher_model->getAllActiveTeachers();
        $sections = $this->section_model->getAllSections();
        $subjects = $this->subject_model->getAll();

        $assignments = $this->assignment_model->getAllAssignmentsGrouped('active');
        $inactive_assignments = $this->assignment_model->getAllAssignmentsGrouped('inactive');

        $errors = $_SESSION['assignment_errors'] ?? [];
        $success_message = $_SESSION['assignment_success'] ?? null;
        unset($_SESSION['assignment_errors'], $_SESSION['assignment_success']);

        $reassign_data = $_SESSION['reassign_prefill'] ?? null;
        unset($_SESSION['reassign_prefill']);

        require __DIR__ . '/../views/admin/teacher_assignments.php';
    }

    public function processAssignment()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $teacher_id = isset($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;
        $subject_ids = isset($_POST['subject_ids']) && is_array($_POST['subject_ids'])
            ? array_map('intval', $_POST['subject_ids'])
            : [];
        $school_year = $_POST['school_year'] ?? '';
        $semester = $_POST['semester'] ?? 'First';

        $errors = [];

        if (empty($teacher_id)) {
            $errors['teacher_id']  = 'Please select a teacher.';
        }
        if (empty($section_id)) {
            $errors['section_id']  = 'Please select a section.';
        }
        if (empty($subject_ids)) {
            $errors['subject_ids'] = 'Please select at least one subject.';
        }
        if (empty($school_year)) {
            $errors['school_year'] = 'School year is required.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }
            $_SESSION['assignment_errors'] = $errors;
            header('Location: index.php?page=teacher_assignments');
            exit();
        }

        $section = $this->section_model->getSectionById($section_id);
        if (!$section) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid section selected.']);
            }
            $_SESSION['assignment_errors'] = ['section_id' => 'Invalid section selected.'];
            header('Location: index.php?page=teacher_assignments');
            exit();
        }

        $year_level = $section['year_level'];

        $result = $this->assignment_model->assignTeacherToSection(
            $teacher_id,
            $subject_ids,
            $section_id,
            $year_level,
            $school_year,
            $semester
        );

        if ($result === true) {
            if ($this->isAjax()) {
                // fetch the full updated active state (merges new subjects with existing ones)
                $activeData = $this->assignment_model->getGroupedAssignmentByTeacherSection(
                    $teacher_id,
                    $section_id,
                    $school_year,
                    $semester,
                    'active'
                );

                // fetch the full updated inactive state (in case some subjects remain removed)
                $inactiveData = $this->assignment_model->getGroupedAssignmentByTeacherSection(
                    $teacher_id,
                    $section_id,
                    $school_year,
                    $semester,
                    'inactive'
                );

                $response = [
                    'success'       => true,
                    'message'       => 'Teacher assigned successfully.',
                    'row_key'       => "{$teacher_id}_{$section_id}_{$school_year}_{$semester}",
                    'inactive_data' => $inactiveData
                ];

                // merge active data into response so js has full subject list
                if ($activeData) {
                    $response = array_merge($response, $activeData);
                }

                $this->jsonResponse($response);
            }
            $_SESSION['assignment_success'] = 'Teacher assigned successfully.';
        } elseif ($result === null) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'These subjects are already assigned to this teacher for this section.']);
            }
            $_SESSION['assignment_errors'] = ['general' => 'These subjects are already assigned to this teacher for this section.'];
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to assign teacher. Please try again.']);
            }
            $_SESSION['assignment_errors'] = ['general' => 'Failed to assign teacher. Please try again.'];
        }

        header('Location: index.php?page=teacher_assignments');
        exit();
    }

    public function processReassignment()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $teacher_id = isset($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;
        $subject_ids = isset($_POST['subject_ids']) && is_array($_POST['subject_ids'])
            ? array_map('intval', $_POST['subject_ids'])
            : [];
        $school_year = $_POST['school_year'] ?? null;
        $semester = $_POST['semester'] ?? 'First';

        $errors = [];

        if (empty($teacher_id)) {
            $errors['teacher_id']  = 'Teacher ID is required.';
        }
        if (empty($section_id)) {
            $errors['section_id']  = 'Section ID is required.';
        }
        if (empty($subject_ids)) {
            $errors['subject_ids'] = 'Please select at least one subject.';
        }
        if (empty($school_year)) {
            $errors['school_year'] = 'School year is required.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }
            $_SESSION['assignment_errors'] = $errors;
            header('Location: index.php?page=teacher_assignments');
            exit();
        }

        $section = $this->section_model->getSectionById($section_id);
        if (!$section) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid section selected.']);
            }
            $_SESSION['assignment_errors'] = ['section_id' => 'Invalid section selected.'];
            header('Location: index.php?page=teacher_assignments');
            exit();
        }

        $result = $this->assignment_model->reassignTeacherSubjects(
            $teacher_id,
            $section_id,
            $subject_ids,
            $section['year_level'],
            $school_year,
            $semester
        );

        if ($result) {
            if ($this->isAjax()) {
                $updatedAssignment = $this->assignment_model->getGroupedAssignmentByTeacherSection(
                    $teacher_id,
                    $section_id,
                    $school_year,
                    $semester,
                    'active'
                );

                if ($updatedAssignment) {
                    $inactiveAssignment = $this->assignment_model->getGroupedAssignmentByTeacherSection(
                        $teacher_id,
                        $section_id,
                        $school_year,
                        $semester,
                        'inactive'
                    );

                    $this->jsonResponse([
                        'success'       => true,
                        'message'       => 'Teacher assignment updated successfully.',
                        'action'        => 'update',
                        'row_key'       => "{$teacher_id}_{$section_id}_{$school_year}_{$semester}",
                        'data'          => $updatedAssignment,
                        'inactive_data' => $inactiveAssignment
                    ]);
                } else {
                    $inactiveAssignment = $this->assignment_model->getGroupedAssignmentByTeacherSection(
                        $teacher_id,
                        $section_id,
                        $school_year,
                        $semester,
                        'inactive'
                    );

                    $this->jsonResponse([
                        'success'   => true,
                        'message'   => 'Teacher assignment updated successfully.',
                        'action'    => 'remove',
                        'row_key'   => "{$teacher_id}_{$section_id}_{$school_year}_{$semester}",
                        'inactive_data' => $inactiveAssignment
                    ]);
                }
            }
            $_SESSION['assignment_success'] = 'Teacher assignment updated successfully.';
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update assignment. Please try again.']);
            }
            $_SESSION['assignment_errors'] = ['general' => 'Failed to update assignment. Please try again.'];
        }

        header('Location: index.php?page=teacher_assignments');
        exit();
    }

    public function showReassignForm()
    {
        $this->requireAdmin();

        $teacher_id = isset($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : null;
        $section_id = isset($_GET['section_id']) ? (int) $_GET['section_id'] : null;
        $school_year = $_GET['school_year'] ?? null;

        if (empty($teacher_id) || empty($section_id) || empty($school_year)) {
            $_SESSION['assignment_errors'] = ['general' => 'Invalid reassignment request.'];
            header('Location: index.php?page=teacher_assignments');
            exit();
        }

        $_SESSION['reassign_prefill'] = [
            'teacher_id'  => $teacher_id,
            'section_id'  => $section_id,
            'school_year' => $school_year,
        ];

        header('Location: index.php?page=teacher_assignments');
        exit();
    }

    public function processRemoveAssignment()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $teacher_id = isset($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;
        $school_year = $_POST['school_year'] ?? null;
        $semester = $_POST['semester'] ?? 'First';

        $errors = [];

        if (empty($teacher_id)) {
            $errors['teacher_id']  = 'Teacher ID is required.';
        }
        if (empty($section_id)) {
            $errors['section_id']  = 'Section ID is required.';
        }
        if (empty($school_year)) {
            $errors['school_year'] = 'School year is required.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }
            $_SESSION['assignment_errors'] = $errors;
            header('Location: index.php?page=teacher_assignments');
            exit();
        }

        $result = $this->assignment_model->removeTeacherSectionAssignments($teacher_id, $section_id, $school_year, $semester);

        if ($result) {
            if ($this->isAjax()) {
                $removedAssignment = $this->assignment_model->getGroupedAssignmentByTeacherSection(
                    $teacher_id,
                    $section_id,
                    $school_year,
                    $semester,
                    'inactive'
                );

                $this->jsonResponse([
                    'success'   => true,
                    'message'   => 'Teacher assignment removed. You can restore it from the Removed Assignments section.',
                    'action'    => 'move_to_removed',
                    'row_key'   => "{$teacher_id}_{$section_id}_{$school_year}_{$semester}",
                    'data'      => $removedAssignment
                ]);
            }
            $_SESSION['assignment_success'] = 'Teacher assignment removed. You can restore it from the Removed Assignments section.';
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to remove assignment. Please try again.']);
            }
            $_SESSION['assignment_errors'] = ['general' => 'Failed to remove assignment. Please try again.'];
        }

        header('Location: index.php?page=teacher_assignments');
        exit();
    }

    public function processRestoreAssignment()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $teacher_id = isset($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;
        $school_year = $_POST['school_year'] ?? null;
        $semester = $_POST['semester'] ?? 'First';

        $errors = [];

        if (empty($teacher_id)) {
            $errors['teacher_id']  = 'Teacher ID is required.';
        }
        if (empty($section_id)) {
            $errors['section_id']  = 'Section ID is required.';
        }
        if (empty($school_year)) {
            $errors['school_year'] = 'School year is required.';
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'errors' => $errors]);
            }
            $_SESSION['assignment_errors'] = $errors;
            header('Location: index.php?page=teacher_assignments');
            exit();
        }

        $result = $this->assignment_model->reactivateAssignments($teacher_id, $section_id, $school_year, $semester);

        if ($result) {
            if ($this->isAjax()) {
                $restoredAssignment = $this->assignment_model->getGroupedAssignmentByTeacherSection(
                    $teacher_id,
                    $section_id,
                    $school_year,
                    $semester,
                    'active'
                );

                if ($restoredAssignment) {
                    $this->jsonResponse([
                        'success'   => true,
                        'message'   => 'Teacher assignment restored successfully.',
                        'action'    => 'add',
                        'data'      => $restoredAssignment
                    ]);
                }
            }
            $_SESSION['assignment_success'] = 'Teacher assignment restored successfully.';
        } else {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to restore assignment. Please try again.']);
            }
            $_SESSION['assignment_errors'] = ['general' => 'Failed to restore assignment. Please try again.'];
        }

        header('Location: index.php?page=teacher_assignments');
        exit();
    }
}
