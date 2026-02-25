<?php
require_once __DIR__ . '/../../config/db_connection.php';

class ActivityLog
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    /**
     * log an action
     */
    public function log($user_id, $action, $description = null, $table_affected = null, $record_id = null, $old_data = null, $new_data = null)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $old_json = $old_data ? json_encode($old_data) : null;
        $new_json = $new_data ? json_encode($new_data) : null;

        $stmt = $this->connection->prepare("
            INSERT INTO activity_logs 
                (user_id, action, description, table_affected, record_id, old_data, new_data, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([$user_id, $action, $description, $table_affected, $record_id, $old_json, $new_json, $ip, $user_agent]);
    }

    /**
     * get all logs with pagination and filters
     */
    public function getLogs($limit = 50, $offset = 0, $filters = [])
    {
        $sql = "
    SELECT 
        l.*,
        u.username,
        u.first_name,
        u.last_name,
        u.role
    FROM activity_logs l
    INNER JOIN users u ON l.user_id = u.id
    WHERE 1=1
    ";

        $params = [];

        // apply filters
        if (!empty($filters['user_id'])) {
            $sql .= " AND l.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND l.action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['table_affected'])) {
            $sql .= " AND l.table_affected = ?";
            $params[] = $filters['table_affected'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (l.description LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";

        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->connection->prepare($sql);

        // bind values dynamically with proper types
        foreach ($params as $index => $value) {
            $position = $index + 1;
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($position, $value, $type);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }


    /**
     * get total count for pagination
     */
    public function getTotalCount($filters = [])
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM activity_logs l
            INNER JOIN users u ON l.user_id = u.id
            WHERE 1=1
        ";

        $params = [];

        // same filters as above
        if (!empty($filters['user_id'])) {
            $sql .= " AND l.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND l.action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['table_affected'])) {
            $sql .= " AND l.table_affected = ?";
            $params[] = $filters['table_affected'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (l.description LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * get distinct actions for filter dropdown
     */
    public function getDistinctActions()
    {
        $stmt = $this->connection->prepare("SELECT DISTINCT action FROM activity_logs ORDER BY action");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * get distinct tables for filter dropdown
     */
    public function getDistinctTables()
    {
        $stmt = $this->connection->prepare("SELECT DISTINCT table_affected FROM activity_logs WHERE table_affected IS NOT NULL ORDER BY table_affected");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * get users who have logs for filter dropdown
     */
    public function getUsersWithLogs()
    {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT 
                u.id,
                u.username,
                u.first_name,
                u.last_name,
                u.role
            FROM activity_logs l
            INNER JOIN users u ON l.user_id = u.id
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * get single log by id with full details
     */
    public function getLogById($log_id)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                l.*,
                u.username,
                u.first_name,
                u.last_name,
                u.role,
                u.email
            FROM activity_logs l
            INNER JOIN users u ON l.user_id = u.id
            WHERE l.log_id = ?
        ");
        $stmt->execute([$log_id]);
        return $stmt->fetch();
    }

    /**
     * clear logs older than specified days
     */
    public function clearOldLogs($days = 90)
    {
        $stmt = $this->connection->prepare("
            DELETE FROM activity_logs 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        return $stmt->execute([$days]);
    }

    /**
     * get activity summary for dashboard
     */
    public function getActivitySummary($days = 7)
    {
        $stmt = $this->connection->prepare("
            SELECT 
                DATE(l.created_at) as date,
                COUNT(*) as total_actions,
                COUNT(DISTINCT l.user_id) as active_users,
                SUM(CASE WHEN l.action = 'login' THEN 1 ELSE 0 END) as logins,
                SUM(CASE WHEN l.action LIKE 'create%' THEN 1 ELSE 0 END) as creates,
                SUM(CASE WHEN l.action LIKE 'update%' THEN 1 ELSE 0 END) as updates,
                SUM(CASE WHEN l.action LIKE 'delete%' THEN 1 ELSE 0 END) as deletes
            FROM activity_logs l
            WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(l.created_at)
            ORDER BY date DESC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}
