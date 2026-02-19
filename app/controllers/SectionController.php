<?php

require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/AcademicPeriod.php';

class SectionController
{
    private $section_model;
    private $student_model;
    private $academic_model;

    public function __construct()
    {
        $this->section_model  = new Section();
        $this->student_model  = new Student();
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

        $page        = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $limit       = 10;
        $offset      = ($page - 1) * $limit;
        $search      = $_GET['search'] ?? '';

        // pull school year from active period instead of hardcoding
        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $_GET['school_year'] ?? ($current['school_year'] ?? '');

        $sections        = $this->section_model->getWithPagination($limit, $offset, $search, $school_year);
        $total_sections  = $this->section_model->getTotalCount($search, $school_year);
        $total_pages     = ceil($total_sections / $limit);
        $available_years = $this->section_model->getDistinctSchoolYears();

        $errors          = $_SESSION['section_errors']  ?? [];
        $success_message = $_SESSION['section_success'] ?? null;
        unset($_SESSION['section_errors'], $_SESSION['section_success']);

        require __DIR__ . '/../views/admin/manage_sections.php';
    }

    // ajax search for sections
    public function ajaxSearchSections()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $page   = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $limit  = 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        // pull school year from active period as default
        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $_GET['school_year'] ?? ($current['school_year'] ?? '');

        $sections       = $this->section_model->getWithPagination($limit, $offset, $search, $school_year);
        $total_sections = $this->section_model->getTotalCount($search, $school_year);
        $total_pages    = ceil($total_sections / $limit);

        ob_start();
?>

        <?php if (empty($sections)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">No sections found</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="sectionsTable">
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Education Level</th>
                            <th>Year Level</th>
                            <th>Strand/Course</th>
                            <th>Students</th>
                            <th>School Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sectionsTableBody">
                        <?php foreach ($sections as $section): ?>
                            <tr data-section-id="<?= $section['section_id'] ?>">
                                <td>
                                    <span class="section-name"><?= htmlspecialchars($section['section_name']) ?></span>
                                </td>
                                <td>
                                    <span class="education-level-badge <?= $section['education_level'] === 'senior_high' ? 'badge-shs' : 'badge-college' ?>">
                                        <?= $section['education_level'] === 'senior_high' ? 'Senior High' : 'College' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="year-level"><?= htmlspecialchars($section['year_level']) ?></span>
                                </td>
                                <td>
                                    <span class="strand-course"><?= htmlspecialchars($section['strand_course']) ?></span>
                                </td>
                                <td>
                                    <span class="student-count">
                                        <i class="bi bi-people-fill"></i>
                                        <?= $section['student_count'] ?><?= $section['max_capacity'] ? '/' . $section['max_capacity'] : '' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="school-year"><?= htmlspecialchars($section['school_year']) ?></span>
                                </td>
                                <td>
                                    <a href="index.php?page=view_section&section_id=<?= $section['section_id'] ?>"
                                        class="btn btn-sm btn-outline-info me-1">
                                        <i class="bi bi-eye-fill"></i> View
                                    </a>
                                    <a href="index.php?page=edit_section&section_id=<?= $section['section_id'] ?>"
                                        class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger btn-delete"
                                        data-section-id="<?= $section['section_id'] ?>"
                                        data-section-name="<?= htmlspecialchars($section['section_name']) ?>">
                                        <i class="bi bi-trash-fill"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper mt-3">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="#" data-page="<?= $page - 1 ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page   = min($total_pages, $page + 2);
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="#" data-page="<?= $page + 1 ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div class="pagination-info text-center mt-2 text-muted small">
                        Showing page <?= $page ?> of <?= $total_pages ?> (<?= $total_sections ?> total sections)
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php
        $html = ob_get_clean();

        echo json_encode([
            'success'        => true,
            'html'           => $html,
            'total_sections' => $total_sections,
            'current_page'   => $page,
            'total_pages'    => $total_pages,
        ]);
        exit();
    }

    public function showCreateSection()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // get active period — passes $current_school_year to view
        $current             = $this->academic_model->getCurrentPeriod();
        $current_school_year = $current['school_year'] ?? '';

        require __DIR__ . '/../views/admin/create_section.php';
    }

    public function showEditSection()
    {
        $this->requireAdmin();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : null;

        if (empty($section_id)) {
            $_SESSION['section_errors'] = ['general' => 'Invalid section.'];
            header('Location: index.php?page=manage_sections');
            exit();
        }

        $section = $this->section_model->getSectionById($section_id);

        if (!$section) {
            $_SESSION['section_errors'] = ['general' => 'Section not found.'];
            header('Location: index.php?page=manage_sections');
            exit();
        }

        require __DIR__ . '/../views/admin/edit_section.php';
    }

    public function processCreateSection()
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $section_name    = trim($_POST['section_name']    ?? '');
        $education_level = $_POST['education_level']      ?? '';
        $year_level      = trim($_POST['year_level']      ?? '');
        $strand_course   = trim($_POST['strand_course']   ?? '');
        $max_capacity    = trim($_POST['max_capacity']    ?? '');

        // always use the active period school year — ignore any client-submitted value
        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';

        $errors = [];

        if (empty($school_year)) {
            $errors['general'] = 'No active academic period found. Please initialize a period first.';
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

        $capacity_value = null;
        if (empty($max_capacity)) {
            $errors['max_capacity'] = 'Maximum capacity is required.';
        } elseif (!is_numeric($max_capacity) || intval($max_capacity) < 1) {
            $errors['max_capacity'] = 'Maximum capacity must be a positive number.';
        } else {
            $capacity_value = intval($max_capacity);
        }

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

        $section_id      = isset($_POST['section_id']) ? intval($_POST['section_id']) : null;
        $section_name    = trim($_POST['section_name']    ?? '');
        $education_level = $_POST['education_level']      ?? '';
        $year_level      = trim($_POST['year_level']      ?? '');
        $strand_course   = trim($_POST['strand_course']   ?? '');
        $max_capacity    = trim($_POST['max_capacity']    ?? '');

        // always use the active period school year
        $current     = $this->academic_model->getCurrentPeriod();
        $school_year = $current['school_year'] ?? '';

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

        $capacity_value = null;
        if (!empty($max_capacity)) {
            if (!is_numeric($max_capacity) || intval($max_capacity) < 1) {
                $errors['max_capacity'] = 'Maximum capacity must be a positive number.';
            } else {
                $capacity_value = intval($max_capacity);
            }
        }

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
        $search     = $_GET['search'] ?? '';
        $page       = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $limit      = 10;
        $offset     = ($page - 1) * $limit;

        if (empty($section_id)) {
            $_SESSION['section_errors'] = ['general' => 'Invalid section.'];
            header('Location: index.php?page=manage_sections');
            exit();
        }

        $section = $this->section_model->getSectionWithStudentCount($section_id);

        if (!$section) {
            $_SESSION['section_errors'] = ['general' => 'Section not found.'];
            header('Location: index.php?page=manage_sections');
            exit();
        }

        // determine if this is a historical section or current
        $current    = $this->academic_model->getCurrentPeriod();
        $is_history = $current && $section['school_year'] !== $current['school_year'];

        if ($is_history) {
            $students       = $this->section_model->getHistoricalStudents($section_id, $limit, $offset, $search);
            $total_students = $this->section_model->getTotalHistoricalStudents($section_id, $search);
        } else {
            $students       = $this->student_model->getStudentsBySectionWithPagination($section_id, $limit, $offset, $search);
            $total_students = $this->student_model->getTotalStudentsInSectionCount($section_id, $search);
        }

        $total_pages = ceil($total_students / $limit);

        require __DIR__ . '/../views/admin/view_section.php';
    }

    public function ajaxSectionStudents()
    {
        header('Content-Type: application/json');
        $this->requireAdmin();

        $section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : null;
        $page       = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $limit      = 10;
        $offset     = ($page - 1) * $limit;
        $search     = $_GET['search'] ?? '';

        if (!$section_id) {
            echo json_encode(['success' => false, 'message' => 'missing section id']);
            exit();
        }

        // check if historical
        $section     = $this->section_model->getSectionWithStudentCount($section_id);
        $current    = $this->academic_model->getCurrentPeriod();
        $is_history = $current && $section['school_year'] !== $current['school_year'];

        if ($is_history) {
            $students       = $this->section_model->getHistoricalStudents($section_id, $limit, $offset, $search);
            $total_students = $this->section_model->getTotalHistoricalStudents($section_id, $search);
        } else {
            $students       = $this->student_model->getStudentsBySectionWithPagination($section_id, $limit, $offset, $search);
            $total_students = $this->student_model->getTotalStudentsInSectionCount($section_id, $search);
        }

        $total_pages = ceil($total_students / $limit);

        ob_start();
        if (empty($students)): ?>
            <tr>
                <td colspan="5">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-inbox"></i>
                        </div>
                        <p class="empty-state-text">No students found</p>
                    </div>
                </td>
            </tr>
            <?php else:
            foreach ($students as $student): ?>
                <tr>
                    <td>
                        <span class="subject-code"><?= htmlspecialchars($student['student_number']) ?></span>
                    </td>
                    <td>
                        <span class="section-name">
                            <?= htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="strand-course">
                            <?= htmlspecialchars($student['email'] ?? 'No email') ?>
                        </span>
                    </td>
                    <td>
                        <span class="year-level"><?= htmlspecialchars($student['year_level']) ?></span>
                    </td>
                    <td>
                        <?php
                        $statusClass = match ($student['enrollment_status']) {
                            'active'   => 'badge-shs',
                            default    => 'badge-college',
                        };
                        ?>
                        <span class="education-level-badge <?= $statusClass ?>">
                            <?= ucfirst($student['enrollment_status']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach;
        endif;
        $table_html = ob_get_clean();

        ob_start();
        if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="#" data-page="<?= $page - 1 ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page   = min($total_pages, $page + 2);
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="#" data-page="<?= $page + 1 ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="pagination-info text-center mt-2 text-muted small">
                Showing page <?= $page ?> of <?= $total_pages ?> (<?= $total_students ?> total students)
            </div>
<?php endif;
        $pagination_html = ob_get_clean();

        echo json_encode([
            'success'         => true,
            'table_html'      => $table_html,
            'pagination_html' => $pagination_html,
            'total_students'  => $total_students,
        ]);
        exit();
    }
}
