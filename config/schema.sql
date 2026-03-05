-- Tablas para el sitio Brenda Melgar
-- Ejecutar en la base de datos 'brendamelgar'

CREATE TABLE IF NOT EXISTS visitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pagina VARCHAR(255) NOT NULL,
    ip VARCHAR(45),
    user_agent VARCHAR(500),
    referrer VARCHAR(500),
    fecha DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS descargas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libro VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    nombre VARCHAR(255),
    ip VARCHAR(45),
    fecha DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contacto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    servicio VARCHAR(100),
    mensaje TEXT,
    ip VARCHAR(45),
    fecha DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS speaking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    pais VARCHAR(100),
    fecha_evento DATE,
    asistentes INT DEFAULT 0,
    ip VARCHAR(45),
    fecha DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
