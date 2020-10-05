CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100)NOT NULL,
    first_name VARCHAR(100)NOT NULL,
    last_name VARCHAR(100)NOT NULL,
    password VARCHAR(255)NOT NULL,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME DEFAULT CURRENT_TIMESTAMP,
    key_value VARCHAR(100),
    user_level INT,
    fb_user_id VARCHAR(100),
    fb_access_token VARCHAR(500)
);