<?php
require_once __DIR__ . '/../models/ActivityLog.php';

class ActivityLogController
{
    private $log_model;

    public function __construct()
    {
        $this->log_model = new ActivityLog();
    }

    /**
     * show activity logs page for admin
     */
    public function showAdminLogs()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit();
        }

        // 1. Fetch the data array
        $data = $this->getLogsData();
        
        // 2. Extract it HERE so the view file can access the variables
        extract($data);
        
        require __DIR__ . '/../views/admin/activity_logs.php';
    }

    /**
     * show activity logs page for superadmin
     */
    public function showSuperAdminLogs()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
            header('Location: index.php?page=login');
            exit();
        }

        // 1. Fetch the data array
        $data = $this->getLogsData();
        
        // 2. Extract it HERE so the view file can access the variables
        extract($data);
        
        require __DIR__ . '/../views/superadmin/activity_logs.php';
    }

    /**
     * common method to get logs data and return array
     */
    private function getLogsData()
    {
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        // build filters from request
        $filters = [];
        if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
        if (!empty($_GET['action'])) $filters['action'] = $_GET['action'];
        if (!empty($_GET['table'])) $filters['table_affected'] = $_GET['table'];
        if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];

        // get logs with filters
        $logs = $this->log_model->getLogs($limit, $offset, $filters);
        $total_logs = $this->log_model->getTotalCount($filters);
        $total_pages = ceil($total_logs / $limit);

        // get filter dropdown data
        $actions = $this->log_model->getDistinctActions();
        $tables = $this->log_model->getDistinctTables();
        $users = $this->log_model->getUsersWithLogs();

        // get summary for last 7 days
        $summary = $this->log_model->getActivitySummary(7);

        $success_message = $_SESSION['success'] ?? null;
        $errors = $_SESSION['errors'] ?? [];
        
        unset($_SESSION['success'], $_SESSION['errors']);

        // RETURN the array instead of extracting it here
        return [
            'logs' => $logs,
            'total_logs' => $total_logs,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'filters' => $filters,
            'actions' => $actions,
            'tables' => $tables,
            'users' => $users,
            'summary' => $summary,
            'success_message' => $success_message,
            'errors' => $errors
        ];
    }

    /**
     * ajax get log details for modal
     */
    public function ajaxGetLogDetails()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'superadmin')) {
            http_response_code(403);
            echo json_encode(['error' => 'unauthorized']);
            exit();
        }

        $log_id = $_GET['log_id'] ?? 0;
        $log = $this->log_model->getLogById($log_id);

        if ($log) {
            // decode json data
            $log['old_data'] = $log['old_data'] ? json_decode($log['old_data'], true) : null;
            $log['new_data'] = $log['new_data'] ? json_decode($log['new_data'], true) : null;

            // format for display
            $log['formatted_time'] = date('F j, Y g:i A', strtotime($log['created_at']));

            echo json_encode(['success' => true, 'log' => $log]);
        } else {
            echo json_encode(['success' => false, 'error' => 'log not found']);
        }
        exit();
    }

    /**
     * export logs to csv
     */
    public function exportLogs()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'superadmin')) {
            header('Location: index.php?page=login');
            exit();
        }

        // build filters same as showLogs
        $filters = [];
        if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
        if (!empty($_GET['action'])) $filters['action'] = $_GET['action'];
        if (!empty($_GET['table'])) $filters['table_affected'] = $_GET['table'];
        if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];

        // get all logs (no pagination)
        $logs = $this->log_model->getLogs(10000, 0, $filters);

        // set headers for csv download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // add headers
        fputcsv($output, ['Date/Time', 'User', 'Role', 'Action', 'Description', 'Table', 'Record ID', 'IP Address']);

        // add data rows
        foreach ($logs as $log) {
            $user_name = $log['first_name'] . ' ' . $log['last_name'] . ' (' . $log['username'] . ')';
            fputcsv($output, [
                date('Y-m-d H:i:s', strtotime($log['created_at'])),
                $user_name,
                $log['role'],
                $log['action'],
                $log['description'],
                $log['table_affected'],
                $log['record_id'],
                $log['ip_address']
            ]);
        }

        fclose($output);
        exit();
    }

    /**
     * clear old logs (superadmin only)
     */
    public function clearOldLogs()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
            header('Location: index.php?page=login');
            exit();
        }

        $days = $_POST['days'] ?? 90;
        $this->log_model->clearOldLogs($days);

        $_SESSION['success'] = 'logs older than ' . $days . ' days have been cleared';
        header('Location: index.php?page=activity_logs');
        exit();
    }
}