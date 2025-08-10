-- Delete any existing superadmin user (if exists)
DELETE FROM admin WHERE username='superadmin';

-- Create new super_admin user
INSERT INTO admin (username, name, contact_number, password, user_type, deleted, status, admin_id, start_date, expired_date)
VALUES ('superadmin', 'Super Administrator', '5582999999999', SHA1('Super@2024'), 'super_admin', 'no', 'true', 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 YEAR));

-- Verify the creation
SELECT id, username, name, user_type, status FROM admin WHERE username IN ('admin', 'superadmin') ORDER BY id;
