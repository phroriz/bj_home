CREATE DATABASE IF NOT EXISTS login_db;
USE login_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    senha CHAR(32) NOT NULL
);

INSERT INTO usuarios (usuario, senha) VALUES ('geral', MD5('!123456!'));
