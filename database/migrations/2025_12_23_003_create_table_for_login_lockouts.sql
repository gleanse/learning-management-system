CREATE TABLE login_lockouts (
    ip_address VARCHAR(45) PRIMARY KEY,
    fail_count INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_locked (locked_until) -- for fast lookups for cleanup
);
