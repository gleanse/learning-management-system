<?php

require_once __DIR__ . '/../models/TeacherAssignment.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/AcademicPeriod.php';

class TeacherAssignmentController
{
    private $assignment_model;
    private $teacher_model;
    private $section_model;
    private $subject_model;
    private $student_model;
    private $academic_model;

    public function __construct()
    {
        $this->assignment_model = new TeacherAssignment();
        $this->teacher_model    = new Teacher();
        $this->section_model    = new Section();
        $this->subject_model    = new Subject();
        $this->student_model    = new Student();
        $this->academic_model   = new AcademicPeriod();
    }

    private function enrollSectionStudentsInSubjects($section_id, $subject_ids, $school_year, $semester)
    {
        $students = $this->student_model->getStudentsBySection($section_id);

        if (empty($students)) {
            return;
        }

        foreach ($students as $student) {
            foreach ($subject_ids as $subject_id) {
                $this->student_model->enrollInSubjectIfNotExists(
                    $student['student_id'],
                    $subject_id,
                    $school_year,
                    $semester
                );

                // write history snapshot if not exists
                $this->student_model->writeSectionHistoryPublic(
                    $student['student_id'],
                    $section_id,
                    $school_year,
                    $semester
                );
            }
        }
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

    private function validateSchoolYear($school_year)
    {
        if (!preg_match('/^\d{4}-\d{4}$/', $school_year)) {
            return false;
        }
        $parts = explode('-', $school_year);
        $start = (int) $parts[0];
        $end   = (int) $parts[1];

        return ($end - $start) === 1;
    }

    // get active period — returns null if none initialized
    private function getActivePeriod()
    {
        return $this->academic_model->getCurrentPeriod();
    }

    public function showAssignmentPage()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // pull school year and semester from active period
        $current     = $this->getActivePeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $teachers = $this->teacher_model->getAllActiveTeachers();
        $subjects = $this->subject_model->getAll();

        // only show sections belonging to the active school year
        $sections = $this->section_model->getSectionsBySchoolYear($school_year);

        $assignments          = $this->assignment_model->getAllAssignmentsGrouped('active');
        $inactive_assignments = $this->assignment_model->getAllAssignmentsGrouped('inactive');

        $errors          = $_SESSION['assignment_errors']  ?? [];
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

        // pull school year and semester from active period — not from user input
        $current     = $this->getActivePeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $teacher_id  = isset($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $section_id  = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;
        $subject_ids = isset($_POST['subject_ids']) && is_array($_POST['subject_ids'])
            ? array_map('intval', $_POST['subject_ids'])
            : [];

        $errors = [];

        if (empty($school_year)) {
            $errors['general'] = 'No active academic period found. Please initialize a period first.';
        }
        if (empty($teacher_id)) {
            $errors['teacher_id']  = 'Please select a teacher.';
        }
        if (empty($section_id)) {
            $errors['section_id']  = 'Please select a section.';
        }
        if (empty($subject_ids)) {
            $errors['subject_ids'] = 'Please select at least one subject.';
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

        // block if section belongs to a different school year
        if ($section['school_year'] !== $school_year) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'This section does not belong to the active school year.']);
            }
            $_SESSION['assignment_errors'] = ['section_id' => 'This section does not belong to the active school year.'];
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
            $this->enrollSectionStudentsInSubjects($section_id, $subject_ids, $school_year, $semester);

            if ($this->isAjax()) {
                $activeData = $this->assignment_model->getGroupedAssignmentByTeacherSection(
                    $teacher_id,
                    $section_id,
                    $school_year,
                    $semester,
                    'active'
                );
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

        // pull school year and semester from active period — not from user input
        $current     = $this->getActivePeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $teacher_id  = isset($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $section_id  = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;
        $subject_ids = isset($_POST['subject_ids']) && is_array($_POST['subject_ids'])
            ? array_map('intval', $_POST['subject_ids'])
            : [];

        $errors = [];

        if (empty($school_year)) {
            $errors['general'] = 'No active academic period found. Please initialize a period first.';
        }
        if (empty($teacher_id)) {
            $errors['teacher_id']  = 'Teacher ID is required.';
        }
        if (empty($section_id)) {
            $errors['section_id']  = 'Section ID is required.';
        }
        if (empty($subject_ids)) {
            $errors['subject_ids'] = 'Please select at least one subject.';
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
            $this->enrollSectionStudentsInSubjects($section_id, $subject_ids, $school_year, $semester);

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
                        'success'       => true,
                        'message'       => 'Teacher assignment updated successfully.',
                        'action'        => 'remove',
                        'row_key'       => "{$teacher_id}_{$section_id}_{$school_year}_{$semester}",
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

        $teacher_id  = isset($_GET['teacher_id']) ? (int) $_GET['teacher_id'] : null;
        $section_id  = isset($_GET['section_id']) ? (int) $_GET['section_id'] : null;

        // pull school year from active period
        $current     = $this->getActivePeriod();
        $school_year = $current['school_year'] ?? null;

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

        // pull school year and semester from active period
        $current     = $this->getActivePeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $teacher_id = isset($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;

        $errors = [];

        if (empty($school_year)) {
            $errors['general'] = 'No active academic period found.';
        }
        if (empty($teacher_id)) {
            $errors['teacher_id'] = 'Teacher ID is required.';
        }
        if (empty($section_id)) {
            $errors['section_id'] = 'Section ID is required.';
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
                    'success' => true,
                    'message' => 'Teacher assignment removed. You can restore it from the Removed Assignments section.',
                    'action'  => 'move_to_removed',
                    'row_key' => "{$teacher_id}_{$section_id}_{$school_year}_{$semester}",
                    'data'    => $removedAssignment
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

        // pull school year and semester from active period
        $current     = $this->getActivePeriod();
        $school_year = $current['school_year'] ?? '';
        $semester    = $current['semester']    ?? 'First';

        $teacher_id = isset($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $section_id = isset($_POST['section_id']) ? (int) $_POST['section_id'] : null;

        $errors = [];

        if (empty($school_year)) {
            $errors['general'] = 'No active academic period found.';
        }
        if (empty($teacher_id)) {
            $errors['teacher_id'] = 'Teacher ID is required.';
        }
        if (empty($section_id)) {
            $errors['section_id'] = 'Section ID is required.';
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
            $restoredSubjects = $this->assignment_model->getSubjectIdsBySection($section_id, $school_year, $semester);
            if (!empty($restoredSubjects)) {
                $this->enrollSectionStudentsInSubjects($section_id, $restoredSubjects, $school_year, $semester);
            }

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
                        'success' => true,
                        'message' => 'Teacher assignment restored successfully.',
                        'action'  => 'add',
                        'data'    => $restoredAssignment
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

    // ajax search subjects for assignment form
    public function ajaxSearchSubjectsForAssignment()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $page   = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $limit  = 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        $subjects       = $this->subject_model->getWithPagination($limit, $offset, $search);
        $total_subjects = $this->subject_model->getTotalCount($search);
        $total_pages    = ceil($total_subjects / $limit);

        ob_start();
?>
        <?php if (empty($subjects)): ?>
            <div class="text-center text-muted py-3">
                <i class="bi bi-search fs-4 d-block mb-2"></i>
                <p class="mb-0">No subjects found matching "<?= htmlspecialchars($search) ?>"</p>
            </div>
        <?php else: ?>
            <?php foreach ($subjects as $subject): ?>
                <div class="form-check">
                    <input class="form-check-input subject-checkbox" type="checkbox" name="subject_ids[]"
                        value="<?= $subject['subject_id'] ?>"
                        id="subject_<?= $subject['subject_id'] ?>">
                    <label class="form-check-label" for="subject_<?= $subject['subject_id'] ?>">
                        <strong><?= htmlspecialchars($subject['subject_code']) ?></strong> -
                        <?= htmlspecialchars($subject['subject_name']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php
        $html = ob_get_clean();

        $this->jsonResponse([
            'success'        => true,
            'html'           => $html,
            'total_subjects' => $total_subjects,
            'current_page'   => $page,
            'total_pages'    => $total_pages
        ]);
    }

    // ajax search subjects for reassignment modal
    public function ajaxSearchSubjectsForReassignment()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $page               = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $limit              = 10;
        $offset             = ($page - 1) * $limit;
        $search             = $_GET['search'] ?? '';
        $current_subject_ids = isset($_GET['current_ids']) ? explode(',', $_GET['current_ids']) : [];

        $subjects       = $this->subject_model->getWithPagination($limit, $offset, $search);
        $total_subjects = $this->subject_model->getTotalCount($search);
        $total_pages    = ceil($total_subjects / $limit);

        ob_start();
    ?>
        <?php if (empty($subjects)): ?>
            <div class="text-center text-muted py-3">
                <i class="bi bi-search fs-4 d-block mb-2"></i>
                <p class="mb-0">No subjects found matching "<?= htmlspecialchars($search) ?>"</p>
            </div>
        <?php else: ?>
            <?php foreach ($subjects as $subject): ?>
                <?php $is_checked = in_array($subject['subject_id'], $current_subject_ids); ?>
                <div class="form-check">
                    <input class="form-check-input subject-checkbox" type="checkbox" name="subject_ids[]"
                        value="<?= $subject['subject_id'] ?>"
                        id="reassign_subject_<?= $subject['subject_id'] ?>"
                        <?= $is_checked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="reassign_subject_<?= $subject['subject_id'] ?>">
                        <strong><?= htmlspecialchars($subject['subject_code']) ?></strong> -
                        <?= htmlspecialchars($subject['subject_name']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php
        $html = ob_get_clean();

        $this->jsonResponse([
            'success'        => true,
            'html'           => $html,
            'total_subjects' => $total_subjects,
            'current_page'   => $page,
            'total_pages'    => $total_pages
        ]);
    }

    // ajax search teachers
    public function ajaxSearchTeachers()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $page   = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $limit  = 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        $teachers       = $this->teacher_model->getWithPagination($limit, $offset, $search);
        $total_teachers = $this->teacher_model->getTotalCount($search);
        $total_pages    = ceil($total_teachers / $limit);

        ob_start();
    ?>
        <?php if (empty($teachers)): ?>
            <option value="">No teachers found</option>
        <?php else: ?>
            <option value="">Select a teacher...</option>
            <?php foreach ($teachers as $teacher): ?>
                <option value="<?= $teacher['id'] ?>">
                    <?= htmlspecialchars($teacher['full_name']) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php
        $html = ob_get_clean();

        $this->jsonResponse([
            'success'        => true,
            'html'           => $html,
            'total_teachers' => $total_teachers,
            'current_page'   => $page,
            'total_pages'    => $total_pages
        ]);
    }

    // ajax search sections — only returns sections for active school year
    public function ajaxSearchSections()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $page   = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $limit  = 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        // lock to active school year
        $current     = $this->getActivePeriod();
        $school_year = $current['school_year'] ?? '';

        $sections       = $this->section_model->getWithPagination($limit, $offset, $search, $school_year);
        $total_sections = $this->section_model->getTotalCount($search, $school_year);
        $total_pages    = ceil($total_sections / $limit);

        ob_start();
    ?>
        <?php if (empty($sections)): ?>
            <option value="">No sections found</option>
        <?php else: ?>
            <option value="">Select a section...</option>
            <?php foreach ($sections as $section): ?>
                <option value="<?= $section['section_id'] ?>">
                    <?= htmlspecialchars($section['section_name']) ?>
                    (<?= htmlspecialchars($section['year_level']) ?> - <?= htmlspecialchars($section['strand_course']) ?>)
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
<?php
        $html = ob_get_clean();

        $this->jsonResponse([
            'success'        => true,
            'html'           => $html,
            'total_sections' => $total_sections,
            'current_page'   => $page,
            'total_pages'    => $total_pages
        ]);
    }
}
