-- Active: 1747067405066@@127.0.0.1@3306@crm-gestion
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-05-2025 a las 18:03:29
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `crm-gestion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `admin`
--

INSERT INTO `admin` (`id`, `name`, `password`) VALUES
(1, 'configuroweb', '1234abcd..');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prequest`
--

CREATE TABLE `prequest` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contactno` varchar(11) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `services` text DEFAULT NULL,
  `others` varchar(255) DEFAULT NULL,
  `query` longtext DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `posting_date` date DEFAULT NULL,
  `remark` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `prequest`
--

INSERT INTO `prequest` (`id`, `name`, `email`, `contactno`, `company`, `services`, `others`, `query`, `status`, `posting_date`, `remark`) VALUES
(1, 'Mark Cooper', 'pcliente@cweb.com', '3052589471', 'ConfiguroWeb', '[\\\"Recuperaci\\\\u00f3n de Informaci\\\\u00f3n\\\"]', '', 'Se solicita ayuda analizando el disco duro', 0, '2022-11-29', 'Registro Observaciones'),
(2, 'Juan Cliente', 'jcliente@cweb.com', '3025897461', 'ConfiguroWeb', '[\\\"Recuperaci\\\\u00f3n de Informaci\\\\u00f3n\\\"]', '', 'Se solicita ayuda analizando el disco duro, para recuperar información, ya que fue eliminada.', 0, '2023-01-11', 'Se realiza el proceso solicitado a satisfacción.'),
(3, 'Juan Cliente', 'jcliente@cweb.com', '3025897461', 'ConfiguroWeb', '[\\\"Recuperaci\\\\u00f3n de Informaci\\\\u00f3n\\\"]', '', 'Se necesita buscar información en el tel que previamente fue eliminado.', 0, '2023-01-12', 'Se realiza el proceso solicitado.'),
(4, 'Equis', 'ecorreo@cweb.com', '3052589741', 'ConfiguroWeb', '[\\\" Formateo de Dispositivo\\\"]', '', 'Se solicita el formateo del teléfono', 0, '2023-01-14', 'Se ejecuta el servicio solicitado efectivamente.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket`
--

CREATE TABLE `ticket` (
  `id` int(11) NOT NULL,
  `ticket_id` varchar(11) DEFAULT NULL,
  `email_id` varchar(300) DEFAULT NULL,
  `subject` varchar(300) DEFAULT NULL,
  `task_type` varchar(300) DEFAULT NULL,
  `prioprity` varchar(300) DEFAULT NULL,
  `ticket` longtext DEFAULT NULL,
  `attachment` varchar(300) DEFAULT NULL,
  `status` varchar(300) DEFAULT NULL,
  `admin_remark` longtext DEFAULT NULL,
  `posting_date` date DEFAULT NULL,
  `admin_remark_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE `ticket` MODIFY `posting_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
--
-- Volcado de datos para la tabla `ticket`
--

INSERT INTO `ticket` (`id`, `ticket_id`, `email_id`, `subject`, `task_type`, `prioprity`, `ticket`, `attachment`, `status`, `admin_remark`, `posting_date`, `admin_remark_date`) VALUES
(13, '6', 'pcliente@cweb.com', 'Fallo con el Servidor IDPROD 26', 'Fallo a Nivel de Servidor', 'importante', 'Es necesario reiniciar la máquina de estados', NULL, 'closed', 'Se realiza el proceso solicitado a satisfacción.', '2022-11-29', '2023-01-12 04:28:16'),
(14, '7', 'pcliente@cweb.com', 'Fallo con el Servidor IDPROD 26', 'Fallo a Nivel de Servidor', 'non-urgent', 'Es necesario reiniciar la máquina de estados', NULL, 'closed', 'Se realiza el proceso solicitado a satisfacción.', '2022-11-29', '2023-01-12 04:28:20'),
(15, '1', 'jcliente@cweb.com', 'Fallo con el Servidor IDPROD 26', 'Fallo a Nivel de Servidor', 'Importante', 'Es necesario reiniciar la máquina de estados', NULL, 'closed', 'Se realiza el proceso solicitado a satisfacción.', '2023-01-11', '2023-01-12 03:31:11'),
(16, '2', 'jcliente@cweb.com', 'Fallo en consulta', 'Error capa de aplicación', 'Urgente-(Problema Funcional)', 'Se confirma que en la consulta se envía un json, con un campo en NULL', NULL, 'closed', 'Se realiza en ajuste en la consulta, se procede al cierre del ticket.', '2023-01-12', '2023-01-12 22:12:12'),
(17, '3', 'ecorreo@cweb.com', 'Fallo consulta', 'Incidente Lógica', 'Importante', 'La consulta de cédula de cliente no arroja resultados, esto para la operación por completo.', NULL, 'closed', 'Se soluciona el fallo efectivamente, se confirma la resolución, se procede al cierre', '2023-01-14', '2023-01-14 20:50:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `posting_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `alt_email`, `password`, `mobile`, `gender`, `address`, `status`, `posting_date`) VALUES
(1, 'Mauricio Sevilla', 'hola@cweb.com', 'admin@cweb.com', '1234abcd..', '3162430081', 'male', 'Calle 45 23 21', NULL, '2021-04-22 18:25:19'),
(2, 'Pedro Cliente', 'pcliente@cweb.com', 'pecliente@cweb.com', '1234abcd..', '3025869471', 'm', 'Sample Address only', NULL, '2022-11-29 09:28:28'),
(3, 'Juan Cliente', 'jcliente@cweb.com', 'jacliente@cweb.com', '12345abcde..', '3025897461', 'male', 'Calle 85 94 71', NULL, '2023-01-11 04:30:44'),
(4, 'Lorena Cliente', 'lcliente@cweb.com', NULL, '1234abcd..', '3052859471', 'male', NULL, NULL, '2023-01-12 20:21:24'),
(5, 'Equis', 'ecorreo@cweb.com', NULL, '1234abcd..', '3052589741', 'male', NULL, NULL, '2023-01-14 20:44:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usercheck`
--

CREATE TABLE `usercheck` (
  `id` int(11) NOT NULL,
  `logindate` varchar(255) DEFAULT '',
  `logintime` varchar(255) DEFAULT '',
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT '',
  `ip` varbinary(16) DEFAULT NULL,
  `mac` varbinary(16) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usercheck`
ALTER TABLE `usercheck` 
MODIFY COLUMN `ip` VARCHAR(255) DEFAULT NULL,
DROP COLUMN `mac`,
ADD COLUMN `user_agent` VARCHAR(255) AFTER `ip`,
ADD COLUMN `os` VARCHAR(100) AFTER `user_agent`,
CHANGE COLUMN `logindate` `logindatetime` DATETIME DEFAULT CURRENT_TIMESTAMP,
DROP COLUMN `logintime`;
--

INSERT INTO `usercheck` (`id`, `logindate`, `logintime`, `user_id`, `username`, `email`, `ip`, `mac`, `city`, `country`) VALUES
(4, '2022/11/29', '09:36:21am', 2, 'Pedro Cliente', 'pcliente@cweb.com', 0x3a3a31, 0x30302d30422d32422d30322d36352d44, '', ''),
(3, '2022/11/29', '09:01:36am', 2, 'Pedro Cliente', 'pcliente@cweb.com', 0x3a3a31, 0x30302d30422d32422d30322d36352d44, '', ''),
(5, '2023/01/10', '04:00:59am', 3, 'Juan Cliente', 'jcliente@cweb.com', 0x3132372e302e302e31, 0x4e6f6d62726520646520686f73742e20, '', ''),
(6, '2023/01/11', '10:17:10pm', 3, 'Juan Cliente', 'jcliente@cweb.com', 0x3a3a31, 0x4e6f6d62726520646520686f73742e20, '', ''),
(7, '2023/01/11', '02:38:50am', 3, 'Juan Cliente', 'jcliente@cweb.com', 0x3a3a31, 0x4e6f6d62726520646520686f73742e20, '', ''),
(8, '2023/01/11', '02:40:06am', 3, 'Juan Cliente', 'jcliente@cweb.com', 0x3a3a31, 0x4e6f6d62726520646520686f73742e20, '', ''),
(9, '2023/01/11', '03:23:15am', 3, 'Juan Cliente', 'jcliente@cweb.com', 0x3132372e302e302e31, 0x4e6f6d62726520646520686f73742e20, '', ''),
(10, '2023/01/12', '07:54:47pm', 4, 'Lorena Cliente', 'lcliente@cweb.com', 0x3a3a31, 0x4e6f6d62726520646520686f73742e20, '', ''),
(11, '2023/01/14', '08:14:36pm', 5, 'Equis', 'ecorreo@cweb.com', 0x3a3a31, 0x4e6f6d62726520646520686f73742e20, '', '');


CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE permissions (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE `role_permissions` (
  `role_id` INT NOT NULL,
  `permission_id` INT NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY(`role_id`) REFERENCES `roles`(id) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(id) ON DELETE CASCADE
);

CREATE TABLE `chat_user_tech` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `tech_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `status_chat` ENUM ('abierto', 'cerrado') DEFAULT 'cerrado',
  `init_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `close_date` DATETIME NULL,
  FOREIGN KEY (`ticket_id`) REFERENCES `ticket`(id),
  FOREIGN KEY (`tech_id`) REFERENCES `user`(id),
  FOREIGN KEY (`user_id`) REFERENCES `user`(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--RENAME TABLE `chat-user-tech` TO `chat_user_tech`; //esta línea era para usar una sola vez

CREATE TABLE `messg_tech_user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `chat_id` INT NOT NULL,
  `sender` ENUM('tecnico', 'usuario') NOT NULL,
  `message` TEXT NOT NULL,
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`chat_id`) REFERENCES `chat_user_tech`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

  ALTER TABLE `messg_tech_user`
ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

DROP TABLE IF EXISTS `messg_tech_user`;

ALTER TABLE `messg_tech_user`
ADD CONSTRAINT `fk_chat_id`
FOREIGN KEY (`chat_id`)
REFERENCES `chat_user_tech`(id)
ON DELETE CASCADE;


CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `ticket_id` INT DEFAULT NULL,
  `type` ENUM('nuevo_ticket', 'asignacion_ticket', 'respuesta_ticket', 
  'nuevo_mensaje_chat', 'solicitud_aprobacion', 'respuesta_aprobacion',
  'resolucion_ticket', 'permiso_password') NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(500) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `application_approv` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `tech_id` INT NOT NULL,
  `apply_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM ('pendiente' , 'aprobado') DEFAULT 'pendiente',
  `comentario` TEXT,
  FOREIGN KEY (`ticket_id`) REFERENCES `ticket`(id),
  FOREIGN KEY (`tech_id`) REFERENCES `user`(id)
);

CREATE TABLE `messg_tech_admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `apply_id` INT NOT NULL,
  `emisor` ENUM ('tecnico', 'admin') NOT NULL,
  `message` TEXT NOT NULL,
  `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`apply_id`) REFERENCES `application_approv`(id)
);

CREATE TABLE `areas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE `edificios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE `password_request` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL,
  `motive` TEXT NOT NULL,
  `soli_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM ('pendiente', 'atendida') DEFAULT 'pendiente'
);

INSERT INTO `edificios`(name) VALUES ('Santa Esmeralda'),
('Palmira');
INSERT INTO `areas`(name) VALUES ('Administración'),
('Dirección General de Planificación'), ('Información y Prensa'), ('Poder Popular'), ('');

ALTER TABLE `application_approv` MODIFY COLUMN `status` ENUM('pendiente', 'aprobado', 'rechazado', 'resuelto') NOT NULL DEFAULT 'pendiente';

ALTER TABLE `user` ADD `puede_cambiar_password` TINYINT(1) NOT NULL DEFAULT 1;

-- Insertar roles
INSERT INTO roles (name) VALUES ('admin'), ('supervisor'), ('tecnico'), ('usuario');

-- Insertar permisos
INSERT INTO permissions (name) VALUES 
('ver_tickets'), 
('crear_ticket'), 
('asignar_ticket'), 
('responder_ticket'), 
('ver_usuarios'), 
('gestionar_roles');

-- Asignar permisos al rol 'admin' (id 1)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
VALUES 
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6);

-- Asignar permisos al rol 'supervisor' (id 2)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
VALUES 
(2, 1), (2, 3), (2, 4);

-- Asignar permisos al rol 'tecnico' (id 3)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
VALUES 
(3, 1), (3, 4);

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
VALUES 
(4, 1), (4, 2);
--
-- Índices para tablas volcadas
--
ALTER TABLE `ticket`
ADD COLUMN `technician_id` INT DEFAULT NULL AFTER `status`;

ALTER TABLE `ticket` ADD `edificio_id` INT;


--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `prequest`
--
ALTER TABLE `prequest`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `user` ADD COLUMN `edificio_id` INT NULL;
--
-- Indices de la tabla `usercheck`
--
ALTER TABLE `usercheck`
  ADD PRIMARY KEY (`id`);

--
-- Cambio de logindate en usercheck
ALTER TABLE `usercheck`
  MODIFY `logindate` DATETIME DEFAULT NULL;

--

--
-- AUTO_INCREMENT de la tabla `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `prequest`
--
ALTER TABLE `prequest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ticket`
--
ALTER TABLE `ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
--
-- Respuesta del Técnico en el ticket
ALTER TABLE `ticket`
ADD COLUMN `tech_remark` LONGTEXT DEFAULT NULL,
ADD COLUMN `tech_remark_date` TIMESTAMP NULL DEFAULT NULL;


---
ALTER TABLE `ticket` ADD COLUMN  `fecha_cierre` DATETIME NULL AFTER status;

---

-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
--
ALTER TABLE user ADD COLUMN password_last_changed DATETIME NULL;
--

-- AUTO_INCREMENT de la tabla `usercheck`
--
ALTER TABLE `usercheck`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
----

-- 1️ Verificar valores actuales para identificar inconsistencias
SELECT DISTINCT status FROM ticket;

-- 2️ Corregir valores incorrectos y normalizar (ajusta si encuentras más valores)
UPDATE ticket SET status = 'Abierto' WHERE status IN ('open', 'Open', 'ABIERTO', 'abierto');
UPDATE ticket SET status = 'En proceso' WHERE status IN ('en proceso', 'En Proceso', 'EN PROCESO', 'Enproceso');
UPDATE ticket SET status = 'Cerrado' WHERE status IN ('closed', 'Closed', 'CERRADO', 'cerrado');

-- 3️ (Opcional) Verificar de nuevo los valores únicos después de las actualizaciones
SELECT DISTINCT status FROM ticket;

-- 4️ Cambiar el campo a ENUM restringido con los valores correctos
ALTER TABLE ticket 
MODIFY COLUMN status ENUM('Abierto', 'En proceso', 'Cerrado') NOT NULL DEFAULT 'Abierto';
---

---Agrgar el campo status a la tabla user
ALTER TABLE `user` ADD COLUMN `user_status` ENUM ('Activo', 'Inactivo') DEFAULT 'Activo';
--
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
