<?php

require_once __DIR__ . '/../../config/db_connection.php';

class User
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    public function authenticate($username_or_email, $password)
    {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username_or_email, $username_or_email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function register(array $user_data)
    {
        $errors = [];

        $check_username = $this->connection->prepare("SELECT id FROM users WHERE username = ?");
        $check_username->execute([$user_data['username']]);

        if ($check_username->fetch()) {
            $errors[] = 'username_exists';
        }

        if (!empty($user_data['email'])) {
            $check_email = $this->connection->prepare("SELECT id FROM users WHERE email = ?");
            $check_email->execute([$user_data['email']]);

            if ($check_email->fetch()) {
                $errors[] = 'email_exists';
            }
        }

        // return if theres any validation error
        if (!empty($errors)) {
            return $errors;
        }

        $stmt = $this->connection->prepare("
            INSERT INTO users (username, email, password, role, first_name, middle_name, last_name, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // if insert execution succeed return the id of created user
        if ($stmt->execute([
            $user_data['username'],
            $user_data['email'],
            password_hash($user_data['password'], PASSWORD_DEFAULT),
            $user_data['role'],
            $user_data['first_name'],
            $user_data['middle_name'],
            $user_data['last_name'],
            $user_data['created_by']
        ])) {
            return $this->connection->lastInsertId();
        }

        return false;
    }

    // REMEMBER ME FEATURE MODEL METHODS
    public function saveRememberToken($userId, $tokenHash, $expiresAt)
    {
        $stmt = $this->connection->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at) 
                VALUES (?, ?, ?)");

        return $stmt->execute([
            $userId,
            $tokenHash,
            $expiresAt,
        ]);
    }

    public function getUserByRememberToken($tokenHash)
    {
        $stmt = $this->connection->prepare("SELECT users.* FROM users
            INNER JOIN remember_tokens ON users.id = remember_tokens.user_id
            WHERE remember_tokens.token_hash = ? AND remember_tokens.expires_at > NOW()");

        $stmt->execute([$tokenHash]);

        return $stmt->fetch();
    }

    public function deleteRememberToken($tokenHash)
    {
        $stmt = $this->connection->prepare("DELETE FROM remember_tokens WHERE token_hash = ?");
        return $stmt->execute([$tokenHash]);
    }

    public function getAllUsers($limit, $offset, $search = '', $role = '')
    {
        $sql = "
        SELECT 
            u.id,
            u.username,
            u.email,
            u.role,
            u.status,
            u.first_name,
            u.middle_name,
            u.last_name,
            u.created_at,
            u.last_login,
            creator.username as created_by_username
        FROM users u
        LEFT JOIN users creator ON u.created_by = creator.id
        WHERE 1=1
    ";

        $params = [];

        if (!empty($role)) {
            $sql .= " AND u.role = :role";
            $params[':role'] = $role;
        }

        if (!empty($search)) {
            $sql .= " AND (u.username LIKE :search OR u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalUsersCount($search = '', $role = '')
    {
        $sql = "SELECT COUNT(*) as count FROM users u WHERE 1=1";
        $params = [];

        if (!empty($role)) {
            $sql .= " AND u.role = ?";
            $params[] = $role;
        }

        if (!empty($search)) {
            $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getUserById($id)
    {
        $stmt = $this->connection->prepare("
        SELECT 
            u.id,
            u.username,
            u.email,
            u.role,
            u.status,
            u.first_name,
            u.middle_name,
            u.last_name,
            u.created_at,
            u.last_login
        FROM users u
        WHERE u.id = ?
    ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateUser($id, array $data)
    {
        $errors = [];

        // check username uniqueness excluding current user
        $stmt = $this->connection->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$data['username'], $id]);
        if ($stmt->fetch()) $errors[] = 'username_exists';

        if (!empty($data['email'])) {
            $stmt = $this->connection->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $id]);
            if ($stmt->fetch()) $errors[] = 'email_exists';
        }

        if (!empty($errors)) return $errors;

        $sql = "
        UPDATE users SET
            username = ?,
            email = ?,
            role = ?,
            status = ?,
            first_name = ?,
            middle_name = ?,
            last_name = ?,
            updated_at = NOW()
    ";

        $params = [
            $data['username'],
            $data['email'] ?? null,
            $data['role'],
            $data['status'],
            $data['first_name'],
            $data['middle_name'] ?? null,
            $data['last_name'],
        ];

        // only update password if provided
        if (!empty($data['password'])) {
            $sql .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->connection->prepare("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    // for real-time ajax validation
    public function usernameExists($username, $exclude_id = null)
    {
        if ($exclude_id) {
            $stmt = $this->connection->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $exclude_id]);
        } else {
            $stmt = $this->connection->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
        }
        return (bool)$stmt->fetch();
    }

    public function emailExists($email, $exclude_id = null)
    {
        if ($exclude_id) {
            $stmt = $this->connection->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $exclude_id]);
        } else {
            $stmt = $this->connection->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
        return (bool)$stmt->fetch();
    }
    public function getTotalUsersByRole()
    {
        $stmt = $this->connection->prepare("
        SELECT role, COUNT(*) as count 
        FROM users 
        GROUP BY role
    ");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        // reformat into role => count for easy access in view
        $result = [];
        foreach ($rows as $row) {
            $result[$row['role']] = $row['count'];
        }
        return $result;
    }

    public function getRecentUsers($limit = 10)
    {
        $stmt = $this->connection->prepare("
        SELECT 
            u.id,
            u.username,
            u.email,
            u.role,
            u.status,
            u.first_name,
            u.middle_name,
            u.last_name,
            u.created_at,
            creator.username as created_by_username
        FROM users u
        LEFT JOIN users creator ON u.created_by = creator.id
        ORDER BY u.created_at DESC
        LIMIT :limit
    ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
