-- altering the users table cause i forgot that middlename should be optional so i modified it back to nullable value so it wont be required
ALTER TABLE users MODIFY middle_name VARCHAR(100) NULL;
