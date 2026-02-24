<?php

require_once __DIR__ . '/../../config/db_connection.php';

class Announcement
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    public function createDraft($title, $content, $target_type, $target_value, $created_by)
    {
        $stmt = $this->connection->prepare("
            INSERT INTO announcements (title, content, target_type, target_value, status, created_by)
            VALUES (?, ?, ?, ?, 'draft', ?)
        ");
        $stmt->execute([$title, $content, $target_type, $target_value ?: null, $created_by]);
        return $this->connection->lastInsertId();
    }

    public function updateDraft($announcement_id, $title, $content, $target_type, $target_value, $created_by)
    {
        $stmt = $this->connection->prepare("
            UPDATE announcements
            SET title = ?, content = ?, target_type = ?, target_value = ?
            WHERE announcement_id = ? AND created_by = ? AND status = 'draft'
        ");
        $stmt->execute([$title, $content, $target_type, $target_value ?: null, $announcement_id, $created_by]);
        return $stmt->rowCount();
    }

    // publish — fans out recipient rows to all matched users
    public function publish($announcement_id, $created_by)
    {
        try {
            $this->connection->beginTransaction();

            // lock and verify ownership + draft status
            $stmt = $this->connection->prepare("
                SELECT * FROM announcements
                WHERE announcement_id = ? AND created_by = ? AND status = 'draft'
                FOR UPDATE
            ");
            $stmt->execute([$announcement_id, $created_by]);
            $announcement = $stmt->fetch();

            if (!$announcement) {
                throw new Exception('Announcement not found or already published.');
            }

            // mark as published
            $stmt = $this->connection->prepare("
                UPDATE announcements
                SET status = 'published', published_at = NOW()
                WHERE announcement_id = ?
            ");
            $stmt->execute([$announcement_id]);

            // fan out recipients based on target
            $user_ids = $this->resolveRecipients(
                $announcement['target_type'],
                $announcement['target_value']
            );

            if (!empty($user_ids)) {
                $this->insertRecipients($announcement_id, $user_ids);
            }

            $this->connection->commit();
            return count($user_ids);
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[Announcement::publish] ' . $e->getMessage());
            throw $e;
        }
    }

    // create and immediately publish in one call
    public function createAndPublish($title, $content, $target_type, $target_value, $created_by)
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("
                INSERT INTO announcements
                    (title, content, target_type, target_value, status, created_by, published_at)
                VALUES (?, ?, ?, ?, 'published', ?, NOW())
            ");
            $stmt->execute([$title, $content, $target_type, $target_value ?: null, $created_by]);
            $announcement_id = $this->connection->lastInsertId();

            $user_ids = $this->resolveRecipients($target_type, $target_value);

            if (!empty($user_ids)) {
                $this->insertRecipients($announcement_id, $user_ids);
            }

            $this->connection->commit();
            return ['announcement_id' => $announcement_id, 'recipient_count' => count($user_ids)];
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[Announcement::createAndPublish] ' . $e->getMessage());
            throw $e;
        }
    }

    private function resolveRecipients($target_type, $target_value)
    {
        switch ($target_type) {
            case 'all':
                return $this->getUserIdsByRole(null);

            case 'role':
                return $this->getUserIdsByRole($target_value);

            case 'student_year_level':
                return $this->getStudentUserIdsByFilter('year_level', $target_value);

            case 'student_education_level':
                return $this->getStudentUserIdsByFilter('education_level', $target_value);

            case 'student_strand_course':
                return $this->getStudentUserIdsByFilter('strand_course', $target_value);

            default:
                return [];
        }
    }

    // get all active user ids, optionally filtered by role
    private function getUserIdsByRole($role)
    {
        if ($role) {
            $stmt = $this->connection->prepare("
                SELECT id FROM users WHERE role = ? AND status = 'active'
            ");
            $stmt->execute([$role]);
        } else {
            $stmt = $this->connection->prepare("
                SELECT id FROM users WHERE status = 'active'
            ");
            $stmt->execute();
        }
        return array_column($stmt->fetchAll(), 'id');
    }

    // get user ids of students filtered by a student column
    private function getStudentUserIdsByFilter($column, $value)
    {
        $allowed = ['year_level', 'education_level', 'strand_course'];
        if (!in_array($column, $allowed)) return [];

        $stmt = $this->connection->prepare("
            SELECT DISTINCT u.id
            FROM users u
            INNER JOIN students s ON s.user_id = u.id
            WHERE s.{$column} = ?
              AND s.enrollment_status = 'active'
              AND u.status = 'active'
        ");
        $stmt->execute([$value]);
        return array_column($stmt->fetchAll(), 'id');
    }

    // bulk insert recipient rows
    private function insertRecipients($announcement_id, array $user_ids)
    {
        $placeholders = implode(',', array_fill(0, count($user_ids), '(?, ?)'));
        $params = [];
        foreach ($user_ids as $uid) {
            $params[] = $announcement_id;
            $params[] = $uid;
        }

        $stmt = $this->connection->prepare("
            INSERT IGNORE INTO announcement_recipients (announcement_id, user_id)
            VALUES {$placeholders}
        ");
        $stmt->execute($params);
    }

    // paginated list for admin announcements page
    public function getAdminList($created_by, $status_filter = null, $page = 1, $per_page = 15)
    {
        $offset = (int) (($page - 1) * $per_page);
        $per_page = (int) $per_page;
        $params = [$created_by];

        $where = 'WHERE a.created_by = ?';
        if ($status_filter) {
            $where .= ' AND a.status = ?';
            $params[] = $status_filter;
        }

        $stmt = $this->connection->prepare("
        SELECT a.*,
               u.first_name, u.last_name,
               (SELECT COUNT(*) FROM announcement_recipients ar WHERE ar.announcement_id = a.announcement_id) AS recipient_count,
               (SELECT COUNT(*) FROM announcement_recipients ar WHERE ar.announcement_id = a.announcement_id AND ar.is_read = 1) AS read_count
        FROM announcements a
        INNER JOIN users u ON u.id = a.created_by
        {$where}
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?
    ");

        $i = 1;
        foreach ($params as $val) {
            $stmt->bindValue($i++, $val);
        }
        $stmt->bindValue($i++, $per_page, PDO::PARAM_INT);
        $stmt->bindValue($i++, $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAdminListCount($created_by, $status_filter = null)
    {
        $params = [$created_by];
        $where  = 'WHERE created_by = ?';

        if ($status_filter) {
            $where .= ' AND status = ?';
            $params[] = $status_filter;
        }

        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM announcements {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getById($announcement_id)
    {
        $stmt = $this->connection->prepare("
            SELECT a.*, u.first_name, u.last_name,
                   (SELECT COUNT(*) FROM announcement_recipients ar WHERE ar.announcement_id = a.announcement_id) AS recipient_count,
                   (SELECT COUNT(*) FROM announcement_recipients ar WHERE ar.announcement_id = a.announcement_id AND ar.is_read = 1) AS read_count
            FROM announcements a
            INNER JOIN users u ON u.id = a.created_by
            WHERE a.announcement_id = ?
        ");
        $stmt->execute([$announcement_id]);
        return $stmt->fetch();
    }

    public function deleteDraft($announcement_id, $created_by)
    {
        $stmt = $this->connection->prepare("
            DELETE FROM announcements
            WHERE announcement_id = ? AND created_by = ? AND status = 'draft'
        ");
        $stmt->execute([$announcement_id, $created_by]);
        return $stmt->rowCount();
    }

    // unread count for bell badge
    public function getUnreadCount($user_id)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) FROM announcement_recipients
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$user_id]);
        return (int) $stmt->fetchColumn();
    }

    // recent announcements for bell dropdown — latest 10
    public function getRecentForUser($user_id, $limit = 10)
    {
        $limit = (int) $limit;

        $stmt = $this->connection->prepare("
        SELECT a.announcement_id, a.title, a.content, a.published_at,
               ar.is_read, ar.read_at,
               u.first_name AS author_first, u.last_name AS author_last
        FROM announcement_recipients ar
        INNER JOIN announcements a ON a.announcement_id = ar.announcement_id
        INNER JOIN users u ON u.id = a.created_by
        WHERE ar.user_id = ?
          AND a.status = 'published'
        ORDER BY a.published_at DESC
        LIMIT ?
    ");

        $stmt->bindValue(1, $user_id);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // paginated full list for user announcements archive page
    public function getAllForUser($user_id, $page = 1, $per_page = 20)
    {
        $offset = (int) (($page - 1) * $per_page);
        $per_page = (int) $per_page;

        $stmt = $this->connection->prepare("
        SELECT a.announcement_id, a.title, a.content, a.published_at,
               ar.is_read, ar.read_at,
               u.first_name AS author_first, u.last_name AS author_last
        FROM announcement_recipients ar
        INNER JOIN announcements a ON a.announcement_id = ar.announcement_id
        INNER JOIN users u ON u.id = a.created_by
        WHERE ar.user_id = ?
          AND a.status = 'published'
        ORDER BY a.published_at DESC
        LIMIT ? OFFSET ?
    ");

        $stmt->bindValue(1, $user_id);
        $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllForUserCount($user_id)
    {
        $stmt = $this->connection->prepare("
            SELECT COUNT(*)
            FROM announcement_recipients ar
            INNER JOIN announcements a ON a.announcement_id = ar.announcement_id
            WHERE ar.user_id = ? AND a.status = 'published'
        ");
        $stmt->execute([$user_id]);
        return (int) $stmt->fetchColumn();
    }

    // mark one announcement as read
    public function markRead($announcement_id, $user_id)
    {
        $stmt = $this->connection->prepare("
            UPDATE announcement_recipients
            SET is_read = 1, read_at = NOW()
            WHERE announcement_id = ? AND user_id = ? AND is_read = 0
        ");
        $stmt->execute([$announcement_id, $user_id]);
        return $stmt->rowCount();
    }

    // mark all as read for a user
    public function markAllRead($user_id)
    {
        $stmt = $this->connection->prepare("
            UPDATE announcement_recipients
            SET is_read = 1, read_at = NOW()
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$user_id]);
        return $stmt->rowCount();
    }

    // distinct year levels from active students
    public function getDistinctYearLevels()
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT year_level FROM students
            WHERE enrollment_status = 'active'
            ORDER BY year_level ASC
        ");
        $stmt->execute();
        return array_column($stmt->fetchAll(), 'year_level');
    }

    // distinct strand/courses from active students
    public function getDistinctStrandCourses()
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT strand_course FROM students
            WHERE enrollment_status = 'active'
            ORDER BY strand_course ASC
        ");
        $stmt->execute();
        return array_column($stmt->fetchAll(), 'strand_course');
    }

    // hard delete a published announcement and all its recipients
    public function deletePublished($announcement_id, $created_by)
    {
        $stmt = $this->connection->prepare("
        DELETE FROM announcements
        WHERE announcement_id = ? AND created_by = ? AND status = 'published'
    ");
        $stmt->execute([$announcement_id, $created_by]);
        return $stmt->rowCount();
    }

    // update published announcement and re-fanout recipients
    public function updatePublished($announcement_id, $title, $content, $target_type, $target_value, $created_by)
    {
        try {
            $this->connection->beginTransaction();

            // verify ownership
            $stmt = $this->connection->prepare("
            SELECT * FROM announcements
            WHERE announcement_id = ? AND created_by = ? AND status = 'published'
            FOR UPDATE
        ");
            $stmt->execute([$announcement_id, $created_by]);
            $ann = $stmt->fetch();

            if (!$ann) {
                throw new Exception('Announcement not found.');
            }

            // update fields
            $stmt = $this->connection->prepare("
            UPDATE announcements
            SET title = ?, content = ?, target_type = ?, target_value = ?
            WHERE announcement_id = ?
        ");
            $stmt->execute([$title, $content, $target_type, $target_value ?: null, $announcement_id]);

            // delete old recipients
            $stmt = $this->connection->prepare("
            DELETE FROM announcement_recipients WHERE announcement_id = ?
        ");
            $stmt->execute([$announcement_id]);

            // re-fanout to new target
            $user_ids = $this->resolveRecipients($target_type, $target_value);
            if (!empty($user_ids)) {
                $this->insertRecipients($announcement_id, $user_ids);
            }

            $this->connection->commit();
            return count($user_ids);
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log('[Announcement::updatePublished] ' . $e->getMessage());
            throw $e;
        }
    }
}
