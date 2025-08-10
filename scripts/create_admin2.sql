DELETE FROM admin WHERE username='admin2';
INSERT INTO admin (username,name,contact_number,password,user_type,deleted,status,admin_id,start_date,expired_date)
VALUES ('admin2','Admin 2','5582999999999',SHA1('Admin@123'),'admin','no','true',1,CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR));
SELECT id, username, user_type, status FROM admin WHERE username='admin2';

