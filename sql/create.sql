CREATE TABLE users(
    id VARCHAR(50) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    admin BOOLEAN,
    added_by VARCHAR(50),
    PRIMARY KEY (id)
);

CREATE TABLE sites(
    id VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    uid VARCHAR(50) NOT NULL,
    dir VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE users_sites(
    id INT NOT NULL auto_increment,
    user_id VARCHAR(50) NOT NULL,
    site_id VARCHAR(50) NOT NULL,
    authorized_by VARCHAR(50) NOT NULL,
    authorized_at DATETIME  NOT NULL default NOW(),
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

CREATE TABLE temp_access(
    access_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL default NOW(),
    expires_at DATETIME NOT NULL,
    PRIMARY KEY (username),
    FOREIGN KEY (access_id) REFERENCES users_sites(id) ON DELETE CASCADE
);


CREATE PROCEDURE get_password(IN username VARCHAR(100))
    SELECT password FROM temp_access tmp 
    WHERE tmp.username = username
    AND tmp.expires_at >= NOW();

CREATE PROCEDURE get_uid(IN username VARCHAR(100))
    SELECT uid FROM temp_access tmp 
    JOIN admins ON tmp.access_id = users_sites.id 
    JOIN sites ON users_sites.site_id = sites.id 
    WHERE tmp.username = username
    AND tmp.expires_at >= NOW();

CREATE PROCEDURE get_gid(IN username VARCHAR(100))
    CALL get_uid(username);

CREATE PROCEDURE get_dir(IN username VARCHAR(100))
    SELECT dir FROM temp_access tmp 
    JOIN admins ON tmp.access_id = users_sites.id 
    JOIN sites ON users_sites.site_id = sites.id 
    WHERE tmp.username = username
    AND tmp.expires_at >= NOW();
