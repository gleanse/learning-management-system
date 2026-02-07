<?php

require_once __DIR__ . '/../models/Subject.php';

class SubjectController
{
    private $subject_model;

    public function __construct()
    {
        $this->subject_model = new Subject();
    }

    public function showSubjectList()
    {
        // check if admin is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit();
        }

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        $subjects = $this->subject_model->getWithPagination($limit, $offset, $search);
        $total_subjects = $this->subject_model->getTotalCount($search);
        $total_pages = ceil($total_subjects / $limit);

        require __DIR__ . '/../views/admin/subject_list.php';
    }

    // AJAX search for subjects
    public function ajaxSearchSubjects()
    {
        header('Content-Type: application/json');

        // check if admin is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'success' => false,
                'message' => 'unauthorized access'
            ]);
            exit();
        }

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        $subjects = $this->subject_model->getWithPagination($limit, $offset, $search);
        $total_subjects = $this->subject_model->getTotalCount($search);
        $total_pages = ceil($total_subjects / $limit);

        // start output buffering to capture HTML
        ob_start();
        ?>

        <?php if (empty($subjects)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">No subjects found</p>
                <?php if (!empty($search)): ?>
                    <button class="btn btn-outline-primary mt-3" onclick="window.location.href='index.php?page=subjects'">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Clear Search
                    </button>
                <?php else: ?>
                    <a href="index.php?page=create_subject" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle-fill"></i>
                        Add Your First Subject
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="subjectsTable">
                    <thead>
                        <tr>
                            <th><i class="bi bi-hash"></i> Subject Code</th>
                            <th><i class="bi bi-book"></i> Subject Name</th>
                            <th><i class="bi bi-text-left"></i> Description</th>
                            <th><i class="bi bi-gear-fill"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td>
                                    <span class="subject-code"><?= htmlspecialchars($subject['subject_code']) ?></span>
                                </td>
                                <td>
                                    <span class="subject-name"><?= htmlspecialchars($subject['subject_name']) ?></span>
                                </td>
                                <td>
                                    <span class="subject-description">
                                        <?= htmlspecialchars($subject['description'] ?? 'No description') ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?page=edit_subject&id=<?= $subject['subject_id'] ?>"
                                        class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger btn-delete"
                                        data-subject-id="<?= $subject['subject_id'] ?>"
                                        data-subject-code="<?= htmlspecialchars($subject['subject_code']) ?>"
                                        data-subject-name="<?= htmlspecialchars($subject['subject_name']) ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mb-0">
                            <!-- previous button -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="#" data-page="<?= $page - 1 ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <!-- page numbers -->
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);

                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="#" data-page="<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- next button -->
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="#" data-page="<?= $page + 1 ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div class="pagination-info">
                        Showing page <?= $page ?> of <?= $total_pages ?> (<?= $total_subjects ?> total subjects)
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php
        $html = ob_get_clean();

        echo json_encode([
            'success' => true,
            'html' => $html,
            'total_subjects' => $total_subjects,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
        exit();
    }

    public function showCreateSubject()
    {
        // check if admin is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit();
        }

        require __DIR__ . '/../views/admin/create_subject.php';
    }

    public function createSubject()
    {
        header('Content-Type: application/json');

        // check if admin is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'success' => false,
                'message' => 'unauthorized access'
            ]);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'invalid request method'
            ]);
            exit();
        }

        // csrf token validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode([
                'success' => false,
                'message' => 'invalid csrf token'
            ]);
            exit();
        }

        $subject_code = trim($_POST['subject_code'] ?? '');
        $subject_name = trim($_POST['subject_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $errors = [];

        // validation
        if (empty($subject_code)) {
            $errors['subject_code'] = 'subject code is required';
        }

        if (empty($subject_name)) {
            $errors['subject_name'] = 'subject name is required';
        }

        // check if subject code already exists
        if (!empty($subject_code) && $this->subject_model->isSubjectCodeExists($subject_code)) {
            $errors['subject_code'] = 'subject code already exists';
        }

        // check if subject name already exists
        if (!empty($subject_name) && $this->subject_model->isSubjectNameExists($subject_name)) {
            $errors['subject_name'] = 'subject name already exists';
        }

        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'validation failed',
                'errors' => $errors
            ]);
            exit();
        }

        // create subject
        if ($this->subject_model->create($subject_code, $subject_name, $description)) {
            echo json_encode([
                'success' => true,
                'message' => 'subject created successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'failed to create subject'
            ]);
        }
        exit();
    }

    public function showEditSubject()
    {
        // check if admin is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit();
        }

        $subject_id = $_GET['id'] ?? null;

        if (empty($subject_id)) {
            header('Location: index.php?page=subjects');
            exit();
        }

        $subject = $this->subject_model->getById($subject_id);

        if (!$subject) {
            $_SESSION['error'] = 'subject not found';
            header('Location: index.php?page=subjects');
            exit();
        }

        require __DIR__ . '/../views/admin/edit_subject.php';
    }

    public function updateSubject()
    {
        header('Content-Type: application/json');

        // check if admin is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'success' => false,
                'message' => 'unauthorized access'
            ]);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'invalid request method'
            ]);
            exit();
        }

        // csrf token validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode([
                'success' => false,
                'message' => 'invalid csrf token'
            ]);
            exit();
        }

        $subject_id = $_POST['subject_id'] ?? null;
        $subject_code = trim($_POST['subject_code'] ?? '');
        $subject_name = trim($_POST['subject_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $errors = [];

        // validation
        if (empty($subject_id)) {
            $errors['subject_id'] = 'subject id is required';
        }

        if (empty($subject_code)) {
            $errors['subject_code'] = 'subject code is required';
        }

        if (empty($subject_name)) {
            $errors['subject_name'] = 'subject name is required';
        }

        // check if subject exists
        $subject = $this->subject_model->getById($subject_id);
        if (!$subject) {
            echo json_encode([
                'success' => false,
                'message' => 'subject not found'
            ]);
            exit();
        }

        // check if subject code already exists for other subjects
        if (!empty($subject_code) && $this->subject_model->isSubjectCodeExists($subject_code, $subject_id)) {
            $errors['subject_code'] = 'subject code already exists';
        }

        // check if subject name already exists for other subjects
        if (!empty($subject_name) && $this->subject_model->isSubjectNameExists($subject_name, $subject_id)) {
            $errors['subject_name'] = 'subject name already exists';
        }

        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'validation failed',
                'errors' => $errors
            ]);
            exit();
        }

        // update subject
        if ($this->subject_model->update($subject_id, $subject_code, $subject_name, $description)) {
            echo json_encode([
                'success' => true,
                'message' => 'subject updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'failed to update subject'
            ]);
        }
        exit();
    }

    public function deleteSubject()
    {
        header('Content-Type: application/json');

        // check if admin is logged in
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode([
                'success' => false,
                'message' => 'unauthorized access'
            ]);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'invalid request method'
            ]);
            exit();
        }

        // csrf token validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode([
                'success' => false,
                'message' => 'invalid csrf token'
            ]);
            exit();
        }

        $subject_id = $_POST['subject_id'] ?? null;

        if (empty($subject_id)) {
            echo json_encode([
                'success' => false,
                'message' => 'subject id is required'
            ]);
            exit();
        }

        // check if subject exists
        $subject = $this->subject_model->getById($subject_id);
        if (!$subject) {
            echo json_encode([
                'success' => false,
                'message' => 'subject not found'
            ]);
            exit();
        }

        // check if subject is in use
        if ($this->subject_model->isSubjectInUse($subject_id)) {
            echo json_encode([
                'success' => false,
                'message' => 'cannot delete subject, it is being used in enrollments'
            ]);
            exit();
        }

        // delete subject
        if ($this->subject_model->delete($subject_id)) {
            echo json_encode([
                'success' => true,
                'message' => 'subject deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'failed to delete subject'
            ]);
        }
        exit();
    }
}