<?php

require_once __DIR__ . '/../models/Announcement.php';

class AnnouncementController
{
    private $model;

    public function __construct()
    {
        $this->model = new Announcement();
    }

    private function requireAdmin()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
            header('Location: index.php?page=login');
            exit();
        }
    }

    private function requireLogin()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit();
        }
    }

    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public function showAnnouncementsPage()
    {
        $this->requireAdmin();

        $created_by    = $_SESSION['user_id'];
        $status_filter = $_GET['status'] ?? null;
        $page          = max(1, (int) ($_GET['p'] ?? 1));
        $per_page      = 15;

        $announcements = $this->model->getAdminList($created_by, $status_filter, $page, $per_page);
        $total         = $this->model->getAdminListCount($created_by, $status_filter);
        $total_pages   = ceil($total / $per_page);

        $year_levels    = $this->model->getDistinctYearLevels();
        $strand_courses = $this->model->getDistinctStrandCourses();

        require __DIR__ . '/../views/admin/announcements.php';
    }

    public function ajaxSaveDraft()
    {
        $this->requireAdmin();

        $title        = trim($_POST['title']        ?? '');
        $content      = trim($_POST['content']      ?? '');
        $target_type  = trim($_POST['target_type']  ?? 'all');
        $target_value = trim($_POST['target_value'] ?? '');
        $edit_id      = (int) ($_POST['announcement_id'] ?? 0);

        if (!$title || !$content) {
            $this->jsonResponse(['success' => false, 'message' => 'Title and content are required.'], 422);
        }

        if (!$this->validateTargetType($target_type)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid target type.'], 422);
        }

        $created_by = $_SESSION['user_id'];

        if ($edit_id) {
            $rows = $this->model->updateDraft($edit_id, $title, $content, $target_type, $target_value, $created_by);
            $this->jsonResponse(['success' => true, 'message' => 'Draft updated.', 'announcement_id' => $edit_id]);
        } else {
            $id = $this->model->createDraft($title, $content, $target_type, $target_value, $created_by);
            $this->jsonResponse(['success' => true, 'message' => 'Draft saved.', 'announcement_id' => $id]);
        }
    }

    public function ajaxPublish()
    {
        $this->requireAdmin();

        $title        = trim($_POST['title']        ?? '');
        $content      = trim($_POST['content']      ?? '');
        $target_type  = trim($_POST['target_type']  ?? 'all');
        $target_value = trim($_POST['target_value'] ?? '');
        $edit_id      = (int) ($_POST['announcement_id'] ?? 0);

        if (!$title || !$content) {
            $this->jsonResponse(['success' => false, 'message' => 'Title and content are required.'], 422);
        }

        if (!$this->validateTargetType($target_type)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid target type.'], 422);
        }

        $created_by = $_SESSION['user_id'];

        try {
            if ($edit_id) {
                // update draft first then publish
                $this->model->updateDraft($edit_id, $title, $content, $target_type, $target_value, $created_by);
                $count = $this->model->publish($edit_id, $created_by);
                $this->jsonResponse([
                    'success'         => true,
                    'message'         => "Announcement published to {$count} recipient(s).",
                    'announcement_id' => $edit_id,
                    'recipient_count' => $count,
                ]);
            } else {
                $result = $this->model->createAndPublish($title, $content, $target_type, $target_value, $created_by);
                $this->jsonResponse([
                    'success'         => true,
                    'message'         => "Announcement published to {$result['recipient_count']} recipient(s).",
                    'announcement_id' => $result['announcement_id'],
                    'recipient_count' => $result['recipient_count'],
                ]);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function ajaxDeleteDraft()
    {
        $this->requireAdmin();

        $announcement_id = (int) ($_POST['announcement_id'] ?? 0);
        $created_by      = $_SESSION['user_id'];

        if (!$announcement_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing announcement ID.'], 422);
        }

        $rows = $this->model->deleteDraft($announcement_id, $created_by);

        if ($rows) {
            $this->jsonResponse(['success' => true, 'message' => 'Draft deleted.']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Draft not found or already published.'], 404);
        }
    }

    public function ajaxGetAnnouncement()
    {
        $this->requireAdmin();

        $announcement_id = (int) ($_GET['announcement_id'] ?? 0);
        $row             = $this->model->getById($announcement_id);

        if (!$row) {
            $this->jsonResponse(['success' => false, 'message' => 'Not found.'], 404);
        }

        $this->jsonResponse(['success' => true, 'data' => $row]);
    }

    public function ajaxGetUnreadCount()
    {
        $this->requireLogin();
        $count = $this->model->getUnreadCount($_SESSION['user_id']);
        $this->jsonResponse(['count' => $count]);
    }

    public function ajaxGetRecent()
    {
        $this->requireLogin();
        $limit = min(100, max(1, (int) ($_GET['limit'] ?? 10)));
        $items = $this->model->getRecentForUser($_SESSION['user_id'], $limit);
        $this->jsonResponse(['success' => true, 'data' => $items]);
    }

    public function ajaxMarkRead()
    {
        $this->requireLogin();

        $announcement_id = (int) ($_POST['announcement_id'] ?? 0);

        if (!$announcement_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing ID.'], 422);
        }

        $this->model->markRead($announcement_id, $_SESSION['user_id']);
        $unread_count = $this->model->getUnreadCount($_SESSION['user_id']);
        $this->jsonResponse(['success' => true, 'unread_count' => $unread_count]);
    }

    public function ajaxMarkAllRead()
    {
        $this->requireLogin();
        $this->model->markAllRead($_SESSION['user_id']);
        $this->jsonResponse(['success' => true, 'unread_count' => 0]);
    }

    private function validateTargetType($type)
    {
        return in_array($type, [
            'all',
            'role',
            'student_year_level',
            'student_education_level',
            'student_strand_course',
        ]);
    }

    public function ajaxDeletePublished()
    {
        $this->requireAdmin();

        $announcement_id = (int) ($_POST['announcement_id'] ?? 0);
        $created_by      = $_SESSION['user_id'];

        if (!$announcement_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing announcement ID.'], 422);
        }

        $rows = $this->model->deletePublished($announcement_id, $created_by);

        if ($rows) {
            $this->jsonResponse(['success' => true, 'message' => 'Announcement deleted.']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Announcement not found.'], 404);
        }
    }

    public function ajaxUpdatePublished()
    {
        $this->requireAdmin();

        $announcement_id = (int) ($_POST['announcement_id'] ?? 0);
        $title           = trim($_POST['title']        ?? '');
        $content         = trim($_POST['content']      ?? '');
        $target_type     = trim($_POST['target_type']  ?? 'all');
        $target_value    = trim($_POST['target_value'] ?? '');
        $created_by      = $_SESSION['user_id'];

        if (!$announcement_id) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing announcement ID.'], 422);
        }

        if (!$title || !$content) {
            $this->jsonResponse(['success' => false, 'message' => 'Title and content are required.'], 422);
        }

        if (!$this->validateTargetType($target_type)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid target type.'], 422);
        }

        try {
            $count = $this->model->updatePublished($announcement_id, $title, $content, $target_type, $target_value, $created_by);
            $this->jsonResponse([
                'success'         => true,
                'message'         => "Announcement updated and re-sent to {$count} recipient(s).",
                'announcement_id' => $announcement_id,
                'recipient_count' => $count,
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
