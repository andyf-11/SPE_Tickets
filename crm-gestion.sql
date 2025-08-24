-- Active: 1747067405066@@127.0.0.1@3306@crm-gestion
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `application_approv` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `tech_id` int(11) NOT NULL,
  `apply_date` datetime DEFAULT current_timestamp(),
  `status` enum('pendiente','aprobado','rechazado','resuelto') NOT NULL DEFAULT 'pendiente',
  `comentario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `application_approv` VALUES(1, 47, 19, '2025-06-18 11:30:36', 'aprobado', NULL);
INSERT INTO `application_approv` VALUES(2, 49, 19, '2025-06-20 14:52:23', 'aprobado', NULL);
INSERT INTO `application_approv` VALUES(3, 50, 16, '2025-06-26 09:06:42', 'aprobado', NULL);
INSERT INTO `application_approv` VALUES(4, 52, 16, '2025-07-03 11:30:16', 'resuelto', NULL);
INSERT INTO `application_approv` VALUES(5, 51, 16, '2025-07-16 08:25:08', 'resuelto', NULL);

CREATE TABLE `areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `areas` (name) VALUES 
('Administración'),
('RRHH'),
('Bienes'),
('Contabilidad'),
('Compras'),
('Informatica');

CREATE TABLE `chat_user_tech` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `tech_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_chat` enum('abierto','cerrado') DEFAULT 'cerrado',
  `init_date` datetime DEFAULT current_timestamp(),
  `close_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `chat_user_tech` VALUES(1, 49, 19, 27, 'cerrado', '2025-06-20 11:27:23', '2025-06-23 10:53:19');
INSERT INTO `chat_user_tech` VALUES(2, 50, 16, 27, 'cerrado', '2025-06-26 09:01:33', '2025-06-26 09:02:49');
INSERT INTO `chat_user_tech` VALUES(3, 52, 16, 27, 'cerrado', '2025-07-01 14:32:48', '2025-07-03 11:29:35');
INSERT INTO `chat_user_tech` VALUES(4, 51, 16, 27, 'abierto', '2025-07-15 19:26:03', NULL);

CREATE TABLE `edificios` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `edificios` VALUES(2, 'Palmira');
INSERT INTO `edificios` VALUES(1, 'Santa Esmeralda');
INSERT INTO `edificios` VALUES(3, 'General');

CREATE TABLE `messg_tech_admin` (
  `id` int(11) NOT NULL,
  `apply_id` int(11) NOT NULL,
  `emisor` enum('tecnico','admin') NOT NULL,
  `message` text NOT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `messg_tech_admin` VALUES(1, 1, 'tecnico', 'Solicito la aprobación de la actualización de activación de usuarios', '2025-06-18 15:01:03');
INSERT INTO `messg_tech_admin` VALUES(2, 1, 'admin', 'Reunión el viernes para la confirmación y características de la actualización', '2025-06-18 15:02:22');
INSERT INTO `messg_tech_admin` VALUES(3, 2, 'tecnico', 'hola admin, esta es una prueba del chat de solicitudes de aprobación', '2025-06-20 14:52:44');
INSERT INTO `messg_tech_admin` VALUES(4, 2, 'admin', 'EL chat funciona y el contador de solicitudes también, Prueba exitosa', '2025-06-20 15:05:38');
INSERT INTO `messg_tech_admin` VALUES(5, 3, 'tecnico', 'hola admin, probando las notificaciones del admin', '2025-06-26 09:07:13');
INSERT INTO `messg_tech_admin` VALUES(6, 3, 'admin', 'el contador de solicitudes funciona, las notificaciones de los tickets funcionando', '2025-06-26 09:09:15');
INSERT INTO `messg_tech_admin` VALUES(7, 4, 'tecnico', 'hola admin, probando los mensajes de este chat.', '2025-07-03 11:35:36');
INSERT INTO `messg_tech_admin` VALUES(8, 4, 'admin', 'mensaje recibido.', '2025-07-03 11:38:55');
INSERT INTO `messg_tech_admin` VALUES(9, 5, 'tecnico', 'hola', '2025-07-16 14:45:43');
INSERT INTO `messg_tech_admin` VALUES(10, 5, 'admin', 'hola, se ven los mensajes', '2025-07-16 15:01:12');
INSERT INTO `messg_tech_admin` VALUES(11, 5, 'tecnico', 'si, todo bien', '2025-07-16 15:02:28');

CREATE TABLE `messg_tech_user` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `sender` enum('tecnico','usuario') NOT NULL,
  `message` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `messg_tech_user` VALUES(1, 3, 'tecnico', 'hola', '2025-07-03 11:28:49');
INSERT INTO `messg_tech_user` VALUES(2, 3, 'usuario', 'hola, mensaje recibido', '2025-07-03 11:29:13');

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `type` enum('nuevo_ticket','asignacion_ticket','respuesta_ticket','nuevo_mensaje_chat','solicitud_aprobacion','respuesta_aprobacion','resolucion_ticket','permiso_password') NOT NULL,
  `message` text NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notifications` VALUES(1, 16, NULL, 'nuevo_ticket', 'Se te ha asignado un nuevo ticket (ID interno: 39).', NULL, 0, '2025-06-24 17:05:00');
INSERT INTO `notifications` VALUES(2, 8, NULL, 'nuevo_ticket', 'El técnico ha respondido al ticket (ID interno: 39).', NULL, 0, '2025-06-24 17:11:37');
INSERT INTO `notifications` VALUES(3, 7, NULL, 'nuevo_ticket', 'El técnico ha respondido tu ticket (ID interno: 39). Revisa tu panel de tickets.', NULL, 0, '2025-06-24 17:11:37');
INSERT INTO `notifications` VALUES(4, 18, 13, 'nuevo_ticket', 'Nuevo ticket #13 creado por el usuario elis33@spe.gob.hn.', NULL, 0, '2025-06-26 14:24:56');
INSERT INTO `notifications` VALUES(5, 8, 13, 'nuevo_ticket', 'Nuevo ticket #13 creado por el usuario elis33@spe.gob.hn.', NULL, 0, '2025-06-26 14:24:56');
INSERT INTO `notifications` VALUES(6, 9, 13, 'nuevo_ticket', 'Nuevo ticket #13 creado por el usuario elis33@spe.gob.hn.', NULL, 0, '2025-06-26 14:24:56');
INSERT INTO `notifications` VALUES(7, 16, NULL, 'nuevo_ticket', 'Se te ha asignado un nuevo ticket (ID interno: 50).', NULL, 0, '2025-06-26 14:58:23');
INSERT INTO `notifications` VALUES(8, 8, NULL, 'nuevo_ticket', 'El técnico ha respondido al ticket (ID interno: 50).', NULL, 0, '2025-06-26 15:10:15');
INSERT INTO `notifications` VALUES(9, 27, NULL, 'nuevo_ticket', 'El técnico ha respondido tu ticket (ID interno: 50). Revisa tu panel de tickets.', NULL, 0, '2025-06-26 15:10:15');
INSERT INTO `notifications` VALUES(10, 18, 14, 'nuevo_ticket', 'Nuevo ticket #14 creado por el usuario elis33@spe.gob.hn.', NULL, 0, '2025-07-01 19:56:55');
INSERT INTO `notifications` VALUES(11, 8, 14, 'nuevo_ticket', 'Nuevo ticket #14 creado por el usuario elis33@spe.gob.hn.', NULL, 0, '2025-07-01 19:56:55');
INSERT INTO `notifications` VALUES(12, 9, 14, 'nuevo_ticket', 'Nuevo ticket #14 creado por el usuario elis33@spe.gob.hn.', NULL, 0, '2025-07-01 19:56:55');
INSERT INTO `notifications` VALUES(13, 18, 15, 'nuevo_ticket', 'Nuevo ticket #15 creado por el usuario elis33@spe.gob.hn.', NULL, 0, '2025-07-01 20:31:39');
INSERT INTO `notifications` VALUES(14, 8, 15, 'nuevo_ticket', 'Nuevo ticket #15 creado por el usuario elis33@spe.gob.hn.', NULL, 0, '2025-07-01 20:31:39');
INSERT INTO `notifications` VALUES(15, 9, 15, 'nuevo_ticket', 'Nuevo ticket #15 creado por el usuario elis33@spe.gob.hn.', NULL, 0, '2025-07-01 20:31:39');
INSERT INTO `notifications` VALUES(16, 16, NULL, 'nuevo_ticket', 'Se te ha asignado un nuevo ticket (ID interno: 52).', NULL, 0, '2025-07-01 20:32:25');
INSERT INTO `notifications` VALUES(17, 8, NULL, 'nuevo_ticket', 'El técnico ha respondido al ticket (ID interno: 52).', NULL, 0, '2025-07-03 17:52:12');
INSERT INTO `notifications` VALUES(18, 27, NULL, 'nuevo_ticket', 'El técnico ha respondido tu ticket (ID interno: 52). Revisa tu panel de tickets.', NULL, 0, '2025-07-03 17:52:12');
INSERT INTO `notifications` VALUES(19, 16, NULL, 'nuevo_ticket', 'Se te ha asignado un nuevo ticket (ID interno: 51).', NULL, 0, '2025-07-16 01:20:00');

CREATE TABLE `password_request` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `motive` text NOT NULL,
  `soli_date` datetime DEFAULT current_timestamp(),
  `status` enum('pendiente','atendida') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `permissions` VALUES(3, 'asignar_ticket');
INSERT INTO `permissions` VALUES(2, 'crear_ticket');
INSERT INTO `permissions` VALUES(6, 'gestionar_roles');
INSERT INTO `permissions` VALUES(4, 'responder_ticket');
INSERT INTO `permissions` VALUES(1, 'ver_tickets');
INSERT INTO `permissions` VALUES(5, 'ver_usuarios');

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

INSERT INTO `prequest` VALUES(1, 'Mark Cooper', 'pcliente@cweb.com', '3052589471', 'ConfiguroWeb', '[\\\"Recuperaci\\\\u00f3n de Informaci\\\\u00f3n\\\"]', '', 'Se solicita ayuda analizando el disco duro', 0, '2022-11-29', 'Registro Observaciones');
INSERT INTO `prequest` VALUES(2, 'Juan Cliente', 'jcliente@cweb.com', '3025897461', 'ConfiguroWeb', '[\\\"Recuperaci\\\\u00f3n de Informaci\\\\u00f3n\\\"]', '', 'Se solicita ayuda analizando el disco duro, para recuperar información, ya que fue eliminada.', 0, '2023-01-11', 'Se realiza el proceso solicitado a satisfacción.');
INSERT INTO `prequest` VALUES(3, 'Juan Cliente', 'jcliente@cweb.com', '3025897461', 'ConfiguroWeb', '[\\\"Recuperaci\\\\u00f3n de Informaci\\\\u00f3n\\\"]', '', 'Se necesita buscar información en el tel que previamente fue eliminado.', 0, '2023-01-12', 'Se realiza el proceso solicitado.');
INSERT INTO `prequest` VALUES(4, 'Equis', 'ecorreo@cweb.com', '3052589741', 'ConfiguroWeb', '[\\\" Formateo de Dispositivo\\\"]', '', 'Se solicita el formateo del teléfono', 0, '2023-01-14', 'Se ejecuta el servicio solicitado efectivamente.');
INSERT INTO `prequest` VALUES(5, 'Mini', 'mni@spe.com', '9807-9896', 'SPE', '[\\\"Recuperaci\\\\u00f3n de Informaci\\\\u00f3n\\\"]', '', 'Primera prueba de servicio técnico por este apartado\r\n', 0, '2025-05-26', NULL);

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` VALUES(1, 'admin');
INSERT INTO `roles` VALUES(2, 'supervisor');
INSERT INTO `roles` VALUES(3, 'tecnico');
INSERT INTO `roles` VALUES(4, 'usuario');

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `role_permissions` VALUES(1, 1);
INSERT INTO `role_permissions` VALUES(1, 2);
INSERT INTO `role_permissions` VALUES(1, 3);
INSERT INTO `role_permissions` VALUES(1, 4);
INSERT INTO `role_permissions` VALUES(1, 5);
INSERT INTO `role_permissions` VALUES(1, 6);
INSERT INTO `role_permissions` VALUES(2, 1);
INSERT INTO `role_permissions` VALUES(2, 3);
INSERT INTO `role_permissions` VALUES(2, 4);
INSERT INTO `role_permissions` VALUES(3, 1);
INSERT INTO `role_permissions` VALUES(3, 4);
INSERT INTO `role_permissions` VALUES(4, 1);
INSERT INTO `role_permissions` VALUES(4, 2);

CREATE TABLE `ticket` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `email_id` varchar(300) DEFAULT NULL,
  `subject` varchar(300) DEFAULT NULL,
  `task_type` varchar(300) DEFAULT NULL,
  `priority` varchar(300) DEFAULT NULL,
  `ticket` longtext DEFAULT NULL,
  `status` enum('Abierto','En proceso','Cerrado') NOT NULL DEFAULT 'Abierto',
  `technician_id` int(11) DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `admin_remark` longtext DEFAULT NULL,
  `posting_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_remark_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assigned_to` int(11) DEFAULT NULL,
  `tech_remark` longtext DEFAULT NULL,
  `tech_remark_date` timestamp NULL DEFAULT NULL,
  `edificio_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE `ticket` ADD COLUMN `archivo` VARCHAR(255) NULL AFTER `edificio_id`;

INSERT INTO `ticket` VALUES(13, 6, 'pcliente@cweb.com', 'Fallo con el Servidor IDPROD 26', 'Fallo a Nivel de Servidor', 'importante', 'Es necesario reiniciar la máquina de estados', 'Cerrado', NULL, NULL, 'Se realiza el proceso solicitado a satisfacción.', '2022-11-29 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(14, 7, 'pcliente@cweb.com', 'Fallo con el Servidor IDPROD 26', 'Fallo a Nivel de Servidor', 'non-urgent', 'Es necesario reiniciar la máquina de estados', 'Cerrado', NULL, NULL, 'Se realiza el proceso solicitado a satisfacción.', '2022-11-29 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(15, 1, 'jcliente@cweb.com', 'Fallo con el Servidor IDPROD 26', 'Fallo a Nivel de Servidor', 'Importante', 'Es necesario reiniciar la máquina de estados', 'Cerrado', NULL, NULL, 'Se realiza el proceso solicitado a satisfacción.', '2023-01-11 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(16, 2, 'jcliente@cweb.com', 'Fallo en consulta', 'Error capa de aplicación', 'Urgente-(Problema Funcional)', 'Se confirma que en la consulta se envía un json, con un campo en NULL', 'Cerrado', NULL, NULL, 'Se realiza en ajuste en la consulta, se procede al cierre del ticket.', '2023-01-12 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(17, 3, 'ecorreo@cweb.com', 'Fallo consulta', 'Incidente Lógica', 'Importante', 'La consulta de cédula de cliente no arroja resultados, esto para la operación por completo.', 'Cerrado', NULL, NULL, 'Se soluciona el fallo efectivamente, se confirma la resolución, se procede al cierre', '2023-01-14 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(18, 3, 'mann@cweb.com', 'ljgyfd', 'Incidente Lógica', 'Importante', '..........', 'Cerrado', NULL, NULL, '---------', '2025-05-09 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(19, 4, 'mann@cweb.com', 'No funcionamiento de pagina web', 'Fallo a Nivel de Servidor', '', 'SPE tickets no funciona', 'Cerrado', NULL, NULL, 'lkjhtdx', '2025-05-12 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(20, 5, 'mann@cweb.com', 'ttttttt', 'Incidente Lógica', 'Importante', 'qwaswf', 'Cerrado', NULL, NULL, 'pppppppp', '2025-05-12 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(21, 6, 'mann@cweb.com', 'Impresoras de RRHH', 'Incidente Lógica', 'Importante', 'no funciona la impresora en rrhh', 'Cerrado', NULL, NULL, '\n[2025-06-08 21:04] Admin: hola', '2025-05-12 06:00:00', '2025-06-16 01:09:25', 16, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(22, 7, 'mann@cweb.com', 'hola', 'Fallo a Nivel de Servidor', 'Urgente-(Problema Funcional)', 'hhhhhhhhhh', 'Cerrado', NULL, NULL, 'prueba de visualización de imagen', '2025-05-12 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(39, 15, 'mni@spe.com', 'imagen de prueba', 'Incidente Lógica', 'No-Urgente', 'probando la inserción de imágenes', 'Cerrado', NULL, '2025-06-24 11:11:37', 'enviando respuesta', '2025-05-19 06:00:00', '2025-06-24 17:11:37', 16, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(40, 16, 'mni@spe.com', 'prueba de ticket', 'Incidente Lógica', 'Importante', 'esta es una prueba', 'Cerrado', NULL, NULL, 'gracias por la prueba', '2025-05-19 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(41, 4, 'mni@spe.com', 'Prueba de tickets nuevos', 'Incidente Lógica', 'No-Urgente', 'hola, esta es una prueba', 'Cerrado', NULL, NULL, '.......', '2025-05-20 06:00:00', '2025-06-16 01:09:25', 16, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(42, 5, 'mni@spe.com', 'Prueba de ticket junio', 'Incidente Lógica', 'Importante', 'Esta es una nueva prueba', 'Cerrado', NULL, NULL, 'Esta es una prueba de respuesta por parte del admin', '2025-06-03 06:00:00', '2025-06-16 01:09:25', NULL, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(43, 6, 'mni@spe.com', 'Prueba de Estado de Ticket', 'Incidente Lógica', 'No-Urgente', 'Prueba #1 estados de tickets', 'Cerrado', NULL, NULL, 'Prueba de respuesta por parte del técnico', '2025-06-09 06:00:00', '2025-06-16 01:09:25', 16, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(44, 7, 'mni@spe.com', 'Prueba flujo de ticket', 'Incidente Lógica', 'Importante', 'necesito saber si el ticket hizo todo el recorrido esperado.\r\n', 'Cerrado', NULL, NULL, 'Si, el ticket hizo su recorrido correctamente', '2025-06-10 06:00:00', '2025-06-16 01:09:25', 16, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(45, 8, 'elis33@spe.gob.hn', 'Pruebas usuarios nuevos', 'Error capa de aplicación', 'Importante', 'Revisando funcionalidad de flujo y dashboards', 'Cerrado', NULL, NULL, 'respondiendo desde el punto de vista del técnico', '2025-06-11 06:00:00', '2025-06-16 01:09:25', 19, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(46, 9, 'elis33@spe.gob.hn', 'Prueba #2 estado de tickets', 'Incidente Lógica', 'No-Urgente', 'Esta es una prueba para verificar el estado de los tickets.', 'Cerrado', NULL, '2025-06-11 11:11:50', 'el ticket llegó correctamente en el estado correspondiente al rol del técnico', '2025-06-11 06:00:00', '2025-06-16 01:09:25', 19, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(47, 10, 'mni@spe.com', 'Prueba Toast', NULL, 'No-Urgente', 'Prueba nueva creación de tickets', 'Cerrado', NULL, '2025-06-19 08:38:41', 'Ticket lleva flujo normal hasta ahora.', '2025-06-13 06:00:00', '2025-06-19 14:38:41', 19, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(48, 11, 'elis33@spe.gob.hn', 'Prueba del botón de solicitudes de aprobación', NULL, 'Importante', 'este ticket es para probar el funcionamiento del conteo de solicitudes nuevas', 'Cerrado', NULL, NULL, '\n[2025-06-20 19:19] Admin: el botón de aprobación empezó a fallar, hay que revisar', '2025-06-20 06:00:00', '2025-06-20 17:19:36', 16, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(49, 12, 'elis33@spe.gob.hn', 'Prueba 2 Solicitudes', NULL, 'Urgente-(Problema Funcional)', 'prueba de cambio de email', 'Cerrado', NULL, '2025-06-20 15:07:03', 'prueba existosa', '2025-06-20 06:00:00', '2025-06-20 21:07:03', 19, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(50, 13, 'elis33@spe.gob.hn', 'Notificaciones', NULL, 'Importante', 'probando si llegan las notificaciones', 'Cerrado', NULL, '2025-06-26 09:10:15', 'notificaciones en estado de mantenimiento, la mayoría llegan, hay que revisar las que no llegan.', '2025-06-26 06:00:00', '2025-06-26 15:10:15', 16, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(51, 14, 'elis33@spe.gob.hn', 'Prueba de mensajes en tiempo real', NULL, 'Importante', 'este ticket es para probar los chats entre técnico->usuario y técnico->admin', 'Cerrado', NULL, NULL, '\n[2025-07-17 05:17] Admin: todo bien con los chats, funcionan correctamente\r\n', '2025-07-02 03:56:55', '2025-07-17 03:17:07', 16, NULL, NULL, NULL);
INSERT INTO `ticket` VALUES(52, 15, 'elis33@spe.gob.hn', 'Prueba de mensajes en tiempo real', NULL, 'Importante', 'prueba mensajes y asignación de edificio', 'Cerrado', NULL, '2025-07-03 11:52:12', 'chats  funcionando correctamente al igual que la asignación de edificios', '2025-07-02 04:31:39', '2025-07-03 17:52:12', 16, NULL, NULL, 2);

CREATE TABLE `ticket_images` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `og_name` varchar(255) DEFAULT NULL,
  `route_archivo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ticket_images` VALUES(1, 15, 'Captura de pantalla 2025-05-12 100107.png', 'uploads/1747672669_Captura de pantalla 2025-05-12 100107.png');
INSERT INTO `ticket_images` VALUES(2, 19, 'Captura de pantalla 2025-05-08 094002.png', 'uploads/682b5ffbba43e_Captura de pantalla 2025-05-08 094002.png');

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `posting_date` timestamp NULL DEFAULT current_timestamp(),
  `role` enum('admin','usuario','supervisor','tecnico') NOT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `password_last_changed` datetime DEFAULT NULL,
  `user_status` enum('Activo','Inactivo') DEFAULT 'Activo',
  `edificio_id` int(11) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE `user` ADD COLUMN `area_id` INT NOT NULL;

UPDATE `user` SET `area_id` = 1;

--- Estos 3 comandos solo se utilizaron UNA VEZ
UPDATE `user` SET `gender` = 'Femenino' WHERE `gender` IN ('femenino', 'female');
UPDATE `user` SET `gender` = 'Masculino' WHERE `gender` IN ('masculino', 'male');
UPDATE `user` SET `gender` = 'Otro' WHERE `gender` IN ('otro', 'other');
-------------------------------------------------------------------------------

ALTER TABLE `user` ADD CONSTRAINT `fk_user_area` 
FOREIGN KEY (`area_id`) REFERENCES `areas`(id)
ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `user` MODIFY `gender` ENUM('Femenino', 'Masculino', 'Otro') NOT NULL;

INSERT INTO `user` VALUES(1, 'Mauricio Sevilla', 'hola@cweb.com', '$2y$10$w1x9IAU9onBhL3iMtPJZo.EHIzBAuHnumScN7iaToExRV9B30xHPe', '', 'male', 'Calle 45 23 21', 0, '2021-04-22 18:25:19', '', 0, NULL, 0, NULL, 'Activo', NULL, 1, NULL);
INSERT INTO `user` VALUES(2, 'Pedro Cliente', 'pcliente@cweb.com', '$2y$10$2Hds/QIw1sGAaezQpuCkZOSKYP7n6WAEMEoKmOI5TOl9kdX9bGIT.', '3025869471', 'm', 'Sample Address only', 0, '2022-11-29 09:28:28', '', 0, NULL, 0, NULL, 'Activo', NULL, 1, NULL);
INSERT INTO `user` VALUES(3, 'Juan Cliente', 'jcliente@cweb.com', '$2y$10$wuzlYSufRSapmW3pLuWL6..sZt5GHcTdY2l7lwjeco0QkBEt652.2', '3025897461', 'male', 'Calle 85 94 71', NULL, '2023-01-11 04:30:44', '', 0, NULL, 0, NULL, 'Activo', NULL, 1, NULL);
INSERT INTO `user` VALUES(4, 'Lorena Cliente', 'lcliente@cweb.com', '$2y$10$O28t2UgeXz2OR9GZODO0uepA6En6nmL/nf34YzxIuviNMmm1guRJa', '3052859471', 'male', NULL, NULL, '2023-01-12 20:21:24', '', 0, NULL, 0, NULL, 'Activo', NULL, 1, NULL);
INSERT INTO `user` VALUES(5, 'Equis', 'ecorreo@cweb.com', '$2y$10$5p4ziiVcrA75sGmFMqIEnuBgYXvMQTVGezLQwWlpan30RyGezUXH.', '3052589741', 'male', NULL, NULL, '2023-01-14 20:44:19', '', 0, NULL, 0, NULL, 'Activo', NULL, 1, NULL);
INSERT INTO `user` VALUES(7, 'Mini', 'mni@spe.com', '$2y$10$KZMiKg.YhibKOr5sjS/Fp.O5ds9kD9FtH1zTa9SJBqMbdfLKx4IWi', '9807-9896', 'masculino', '', NULL, '2025-05-16 14:40:14', 'usuario', 0, NULL, 0, NULL, 'Activo', 1, 1, NULL);
INSERT INTO `user` VALUES(8, 'rush_2', 'rush22@spe.com', '$2y$10$jCmWXGlnixWVbITz5.phDup10O4TCu6e/QT5PTbdHHk43OGQntbmu', '99876543', 'masculino', 'colonia un lugar', NULL, '2025-05-20 21:03:28', 'admin', 0, NULL, 0, NULL, 'Activo', 2, 1, NULL);
INSERT INTO `user` VALUES(9, 'manny_1', 'manny@spe.com', '$2y$10$znKIGySqqlObr776v9G8Y.gEm8kEMJTz9T6kgthyDG/WMNVNeefu.', '33456789', 'otro', 'res. redstdr', NULL, '2025-05-20 21:03:28', 'admin', 0, NULL, 0, NULL, 'Activo', 1, 1, NULL);
INSERT INTO `user` VALUES(16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', '$2y$10$VNhFUg6amT1VYfT23cGFxe7x33GMOGDFZ1qBujy8TCWOyhLdVN1c6', '89076543', 'Masculino', 'Res. Las Hadas', NULL, '2025-05-26 19:32:06', 'tecnico', 0, NULL, 0, NULL, 'Activo', NULL, 1, NULL);
INSERT INTO `user` VALUES(17, 'Andrés Lara', 'alrs55@spe.gob.hn', '$2y$10$teznKZa7IryqJqqE09GDyes57FGZ9iPNQu4iiAfVcrxg4h9C8GIAy', '22334455', 'masculino', 'Residencial dondesea', NULL, '2025-05-26 20:08:21', 'usuario', 0, NULL, 1, NULL, 'Activo', NULL, 1, NULL);
INSERT INTO `user` VALUES(18, 'Javier Perez', 'javier_p25@spe.gob.hn', '$2y$10$n6C2QFsiwSnYc8x59hn4VuOBHr9ivfWI0r.3KLn1SVm1j.E107D0C', '99765432', 'masculino', 'Col. Kennedy, Tercera Entrada', NULL, '2025-05-26 20:35:23', 'supervisor', 0, '2025-05-27 17:46:58', 5, NULL, 'Activo', NULL, 1, NULL);
INSERT INTO `user` VALUES(19, 'René López', 'rl2@spe.gob.hn', '$2y$10$tFMm4SAFexN4R0.DRWWU3ODqBtuYQPzy9bIomdheBiG9zHjLWHMeG', '99876543', 'Masculino', 'Col. 15 de Septiembre, Casa 123', 0, '2025-06-11 14:34:58', 'tecnico', 0, NULL, 0, '2025-06-23 11:28:40', 'Activo', NULL, 1, NULL);
INSERT INTO `user` VALUES(27, 'Elisa Solano', 'elis33@spe.gob.hn', '$2y$10$CFR/bn1yb1Af6XdMqif.fOa/5pyBDzUFbv2RGjHiMNBUczXZDT5Eq', '+50433456789', 'femenino', '', NULL, '2025-06-11 16:06:33', 'usuario', 0, NULL, 0, NULL, 'Activo', 2, 1, NULL);
INSERT INTO `user` VALUES(39, 'Andrely Flores', 'andrelyf58@gmail.com', '$2y$10$egvOOphsnecvfzzk91cRB.6EO0yP.vLdyLu.DKZfPUHi6TYWU.Wo.', '12345678', 'femenino', 'Col. Cualquiera', NULL, '2025-07-27 17:42:12', 'usuario', 0, NULL, 0, NULL, 'Activo', NULL, 1, NULL);

CREATE TABLE `usercheck` (
  `id` int(11) NOT NULL,
  `logindatetime` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT '',
  `ip` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `usercheck` VALUES(4, '2022-11-29 00:00:00', 2, 'Pedro Cliente', 'pcliente@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(3, '2022-11-29 00:00:00', 2, 'Pedro Cliente', 'pcliente@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(5, '2023-01-10 00:00:00', 3, 'Juan Cliente', 'jcliente@cweb.com', '127.0.0.1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(6, '2023-01-11 00:00:00', 3, 'Juan Cliente', 'jcliente@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(7, '2023-01-11 00:00:00', 3, 'Juan Cliente', 'jcliente@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(8, '2023-01-11 00:00:00', 3, 'Juan Cliente', 'jcliente@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(9, '2023-01-11 00:00:00', 3, 'Juan Cliente', 'jcliente@cweb.com', '127.0.0.1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(10, '2023-01-12 00:00:00', 4, 'Lorena Cliente', 'lcliente@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(11, '2023-01-14 00:00:00', 5, 'Equis', 'ecorreo@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(12, '2025-05-08 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(13, '2025-05-09 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(14, '2025-05-09 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(15, '2025-05-09 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(16, '2025-05-09 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(17, '2025-05-09 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(18, '2025-05-10 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(19, '2025-05-12 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(20, '2025-05-12 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(21, '2025-05-12 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(22, '2025-05-13 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(23, '2025-05-13 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(24, '2025-05-13 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(25, '2025-05-14 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(26, '2025-05-14 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(27, '2025-05-15 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(28, '2025-05-16 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(29, '2025-05-16 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(30, '2025-05-19 00:00:00', 6, 'Hola', 'mann@cweb.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(31, '2025-05-19 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(32, '2025-05-19 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(33, '2025-05-19 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(34, '2025-05-19 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(35, '2025-05-20 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(36, '2025-05-20 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(37, '2025-05-21 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(38, '2025-05-21 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(39, '2025-05-21 00:00:00', 9, 'manny_1', 'manny@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(40, '2025-05-21 00:00:00', 9, 'manny_1', 'manny@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(41, '2025-05-22 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(42, '2025-05-22 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(43, '2025-05-22 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(44, '2025-05-22 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(45, '2025-05-22 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(46, '2025-05-22 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(47, '2025-05-22 00:00:00', 9, 'manny_1', 'manny@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(48, '2025-05-22 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(49, '2025-05-22 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(50, '2025-05-22 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(51, '2025-05-22 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(52, '2025-05-22 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(53, '2025-05-22 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(54, '2025-05-22 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(55, '2025-05-22 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(56, '2025-05-22 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(57, '2025-05-22 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(58, '2025-05-22 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(59, '2025-05-23 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(60, '2025-05-23 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(61, '2025-05-23 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(62, '2025-05-23 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(63, '2025-05-23 00:00:00', 12, 'John Perezz', 'j_prez15@spe.gob.hn', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(64, '2025-05-26 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(65, '2025-05-26 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(66, '2025-05-26 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(67, '2025-05-26 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(68, '2025-05-26 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(69, '2025-05-26 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(70, '2025-05-26 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(71, '2025-05-26 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(72, '2025-05-26 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(73, '2025-05-26 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(74, '2025-05-26 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(75, '2025-05-26 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(76, '2025-05-26 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(77, '2025-05-26 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(78, '2025-05-26 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(79, '2025-05-26 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(80, '2025-05-26 00:00:00', 15, 'Andrés Lara', 'alrs55@spe.gob.hn', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(81, '2025-05-26 00:00:00', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(82, '2025-05-26 00:00:00', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(83, '2025-05-26 00:00:00', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(84, '2025-05-26 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(85, '2025-05-26 00:00:00', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(86, '2025-05-26 00:00:00', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(87, '2025-05-26 00:00:00', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(88, '2025-05-26 00:00:00', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(89, '2025-05-26 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(90, '2025-05-27 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(91, '2025-05-27 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(92, '2025-05-27 00:00:00', 7, 'Mini', 'mni@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(93, '2025-05-27 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(94, '2025-05-27 00:00:00', 8, 'rush_2', 'rush22@spe.com', '::1', NULL, NULL, '', '');
INSERT INTO `usercheck` VALUES(95, '2025-06-20 08:44:08', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(96, '2025-06-20 08:49:12', 7, 'Mini', 'mni@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(97, '2025-06-20 09:02:24', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(98, '2025-06-20 09:04:19', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(99, '2025-06-20 09:05:28', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(100, '2025-06-20 11:12:51', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(101, '2025-06-20 11:20:25', 19, 'René López', 'rl2@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(102, '2025-06-20 11:22:19', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(103, '2025-06-20 15:04:48', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(104, '2025-06-23 08:43:43', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(105, '2025-06-23 08:56:15', 19, 'René López', 'rl2@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(106, '2025-06-23 09:08:10', 19, 'René López', 'rl2@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(107, '2025-06-23 11:20:50', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(108, '2025-06-24 08:07:16', 7, 'Mini', 'mni@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(109, '2025-06-24 08:23:43', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(110, '2025-06-24 08:32:25', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(111, '2025-06-24 08:34:48', 19, 'René López', 'rl2@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(112, '2025-06-24 08:36:17', 19, 'René López', 'rl2@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(113, '2025-06-24 09:42:24', 19, 'René López', 'rl2@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(114, '2025-06-24 09:42:59', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(115, '2025-06-24 09:46:18', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(116, '2025-06-24 11:10:36', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(117, '2025-06-25 08:52:32', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(118, '2025-06-25 10:17:06', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(119, '2025-06-26 08:20:55', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(120, '2025-06-26 08:23:07', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(121, '2025-06-26 09:00:51', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(122, '2025-06-26 09:08:07', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(123, '2025-06-26 14:36:35', 19, 'René López', 'rl2@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(124, '2025-06-26 15:14:10', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(125, '2025-06-27 08:38:23', 7, 'Mini', 'mni@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(126, '2025-06-27 09:11:21', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(127, '2025-06-27 09:13:06', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(128, '2025-06-27 10:51:30', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(129, '2025-06-29 14:06:32', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(130, '2025-06-29 14:09:24', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(131, '2025-06-29 14:12:20', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(132, '2025-06-29 14:15:10', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(133, '2025-06-30 09:06:49', 19, 'René López', 'rl2@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(134, '2025-07-01 13:53:50', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(135, '2025-07-01 13:54:41', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(136, '2025-07-01 13:55:45', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(137, '2025-07-01 14:34:56', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(138, '2025-07-02 15:56:24', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(139, '2025-07-02 15:59:07', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(140, '2025-07-02 15:59:58', 19, 'René López', 'rl2@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(141, '2025-07-02 16:00:42', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(142, '2025-07-02 16:32:36', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(143, '2025-07-03 08:35:16', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(144, '2025-07-03 09:44:41', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(145, '2025-07-03 11:36:44', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(146, '2025-07-04 10:05:27', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(147, '2025-07-04 10:05:46', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(148, '2025-07-06 16:59:52', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(149, '2025-07-06 17:17:18', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(150, '2025-07-06 17:53:48', 7, 'Mini', 'mni@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(151, '2025-07-06 18:13:40', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(152, '2025-07-06 21:21:28', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(153, '2025-07-07 10:03:57', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(154, '2025-07-07 10:29:58', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(155, '2025-07-07 11:55:43', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(156, '2025-07-07 14:40:50', 19, 'René López', 'rl2@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(157, '2025-07-08 08:53:58', 16, 'Mario Ramos', 'mario_rs3@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(158, '2025-07-08 09:17:48', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(159, '2025-07-08 12:00:28', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(160, '2025-07-09 08:22:35', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(161, '2025-07-09 10:09:04', 27, 'Elisa Solano', 'elis33@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(162, '2025-07-09 10:18:59', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(163, '2025-07-09 10:22:12', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(164, '2025-07-09 10:28:29', 18, 'Javier Perez', 'javier_p25@spe.gob.hn', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(165, '2025-07-09 11:21:55', 8, 'rush_2', 'rush22@spe.com', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `usercheck` VALUES(166, '2025-08-02 15:51:34', 27, 'Elisa Solano', 'elis33@spe.gob.hn', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', 'Windows 10', NULL, NULL);
INSERT INTO `usercheck` VALUES(167, '2025-08-04 08:15:40', 8, 'rush_2', 'rush22@spe.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', 'Windows 10', NULL, NULL);
INSERT INTO `usercheck` VALUES(168, '2025-08-04 08:23:28', 27, 'Elisa Solano', 'elis33@spe.gob.hn', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', 'Windows 10', NULL, NULL);
INSERT INTO `usercheck` VALUES(169, '2025-08-05 11:05:04', 27, 'Elisa Solano', 'elis33@spe.gob.hn', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', 'Windows 10', NULL, NULL);


ALTER TABLE `application_approv`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `tech_id` (`tech_id`);

ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `chat_user_tech`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `tech_id` (`tech_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `edificios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `messg_tech_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `apply_id` (`apply_id`);

ALTER TABLE `messg_tech_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_id` (`chat_id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `password_request`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `prequest`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

ALTER TABLE `ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_assigned_to` (`assigned_to`);

ALTER TABLE `ticket_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ticket` (`ticket_id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `usercheck`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `application_approv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `chat_user_tech`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `edificios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `messg_tech_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `messg_tech_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

ALTER TABLE `password_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `prequest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

ALTER TABLE `ticket_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

ALTER TABLE `usercheck`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;


ALTER TABLE `application_approv`
  ADD CONSTRAINT `application_approv_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`id`),
  ADD CONSTRAINT `application_approv_ibfk_2` FOREIGN KEY (`tech_id`) REFERENCES `user` (`id`);

ALTER TABLE `chat_user_tech`
  ADD CONSTRAINT `chat_user_tech_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`id`),
  ADD CONSTRAINT `chat_user_tech_ibfk_2` FOREIGN KEY (`tech_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `chat_user_tech_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `messg_tech_admin`
  ADD CONSTRAINT `messg_tech_admin_ibfk_1` FOREIGN KEY (`apply_id`) REFERENCES `application_approv` (`id`);

ALTER TABLE `messg_tech_user`
  ADD CONSTRAINT `messg_tech_user_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chat_user_tech` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

ALTER TABLE `ticket`
  ADD CONSTRAINT `fk_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `user` (`id`) ON DELETE SET NULL;

ALTER TABLE `ticket_images`
  ADD CONSTRAINT `fk_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
