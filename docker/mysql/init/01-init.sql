-- SuiteCRM v8 Database Initialization
ALTER DATABASE suitecrm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant full permissions to suitecrm_user on suitecrm_db
GRANT ALL PRIVILEGES ON suitecrm_db.* TO 'suitecrm_user'@'%';

-- Set max_allowed_packet for large imports
SET GLOBAL max_allowed_packet = 67108864;
SET GLOBAL group_concat_max_len = 4096;

FLUSH PRIVILEGES;
