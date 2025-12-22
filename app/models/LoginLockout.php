<?php

class LoginLockout
{
    
    private $connection;
    
    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }
    
    public function checkLockout($ip)
    {
        $stmt = $this->connection->prepare("SELECT * FROM login_lockouts WHERE ip_address = ? AND locked_until > NOW()");
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        
        if ($result) {
            return [
                'locked'=> true,
                'locked_until'=> $result['locked_until'],
                'fail_count'=> $result['fail_count'],
            ];
        } else {
            return ['locked'=>false];
        }
    }
    
    public function recordFail($ip)
    {
        $stmt = $this->connection->prepare("INSERT INTO login_lockouts (
                ip_address,
                fail_count,
                locked_until
            ) VALUES (?, ?, NULL) 
            ON DUPLICATE KEY UPDATE 
                fail_count = fail_count + 1,
                locked_until = CASE
                    WHEN fail_count + 1 >= 41 THEN DATE_ADD(NOW(), INTERVAL 1440 MINUTE)
                    WHEN fail_count + 1 >= 36 THEN DATE_ADD(NOW(), INTERVAL 240 MINUTE)
                    WHEN fail_count + 1 >= 31 THEN DATE_ADD(NOW(), INTERVAL 120 MINUTE)
                    WHEN fail_count + 1 >= 26 THEN DATE_ADD(NOW(), INTERVAL 60 MINUTE)
                    WHEN fail_count + 1 >= 21 THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                    WHEN fail_count + 1 >= 16 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                    WHEN fail_count + 1 >= 11 THEN DATE_ADD(NOW(), INTERVAL 5 MINUTE)
                    WHEN fail_count + 1 >= 6 THEN DATE_ADD(NOW(), INTERVAL 1 MINUTE)
                    ELSE NULL
                END
            ");
        $stmt->execute([$ip, 1]);
    }
    
    public function clearLockout($ip)
    {
        $stmt = $this->connection->prepare("DELETE FROM login_lockouts WHERE ip_address = ?");
        $stmt->execute([$ip]);
    }
    
}