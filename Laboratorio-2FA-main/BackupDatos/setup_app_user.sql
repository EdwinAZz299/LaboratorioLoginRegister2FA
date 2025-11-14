-- Script para crear un usuario de base de datos con privilegios mínimos
-- Reemplaza 'app_user' y 'app_password' si necesitas credenciales distintas.

CREATE USER IF NOT EXISTS 'app_user'@'localhost' IDENTIFIED BY 'app_password';

GRANT SELECT, INSERT, UPDATE, DELETE
ON company_info.*
TO 'app_user'@'localhost';

FLUSH PRIVILEGES;

-- Visualizar los privilegios concedidos
SHOW GRANTS FOR 'app_user'@'localhost';

