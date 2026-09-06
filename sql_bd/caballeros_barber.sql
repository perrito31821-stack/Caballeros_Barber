-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Adaptado para Caballeros Barber · 09-08-2026
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
-- Base de datos: `salon_bello`
--


-- --------------------------------------------------------
--
-- Estructura de tabla para las agendas / profesionales
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `type` enum('barber','beauty') NOT NULL DEFAULT 'barber',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `staff` (`id`, `name`, `username`, `password_hash`, `type`, `active`, `sort_order`) VALUES
(1, 'Barbero 1', 'Barbero1', '$2y$12$FX6psmClKtuU.AteI4mb1OmPoVPld1sZlMFFj9AsHwvIA1JTj1LoG', 'barber', 1, 1),
(2, 'Barbero 2', 'Barbero2', '$2y$12$fWMz.8rEqdtQ7n9CQ5TA7O7xvQuKsWlG9zEaGY8EfKtoLpzni/bOq', 'barber', 1, 2),
(3, 'Barbero 3', 'Barbero3', '$2y$12$.BWc9Nt51X/5fWPabrEG.uBmx8pHHW1zQJUcSSLbHzoMO482RHBnm', 'barber', 1, 3),
(4, 'Barbero 4', 'Barbero4', '$2y$12$FAsQMNADBDHlHWM/7BOT/OzkNIDcJOq6Adii4sAXMR83KhaXr.jF6', 'barber', 1, 4),
(5, 'Barbero 5', 'Barbero5', '$2y$12$CZvewXykTRqjp.8Cke4Y0.JVdfGSTlmLotywg51OFLR8SuKPwuoly', 'barber', 1, 5),
(6, 'Barbero 6', 'Barbero6', '$2y$12$WIlTbSsuCVXriFPgzcLoZOdNvT7C3klxHFFD9rIq9SCdd8Z7Qnioa', 'barber', 1, 6),
(7, 'Belleza', 'Belleza', '$2y$12$iDLS81KI6BYIxIVlfQlePOwWwS1SovN.A2kME3juqIJWKneWwLMEy', 'beauty', 1, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL DEFAULT 1,
  `appt_datetime` datetime NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `status` enum('pendiente','confirmada','completada','cancelada') DEFAULT 'pendiente',
  `code` varchar(8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `service_id`, `appt_datetime`, `notes`, `status`, `code`, `created_at`) VALUES
(1, 3, 1, '2025-08-06 13:05:00', 'Ya voy saliendo a recoger al niño y paso', 'pendiente', 'F8CAA3', '2025-09-06 03:36:57'),
(2, 3, 1, '2025-09-06 14:58:00', '', 'cancelada', '71C2CC', '2025-09-06 19:56:08'),
(3, 3, 4, '2025-09-06 17:00:00', '', 'cancelada', '24E9F6', '2025-09-06 21:59:52'),
(4, 3, 4, '2025-09-06 05:04:00', '', 'cancelada', 'A3E3BC', '2025-09-06 22:01:59'),
(5, 3, 4, '2025-09-05 17:04:00', '', 'pendiente', '095A6E', '2025-09-06 22:02:16'),
(6, 3, 4, '2025-09-06 17:42:00', '', 'cancelada', 'B1F972', '2025-09-06 22:07:39'),
(7, 3, 2, '2025-09-07 17:20:00', '', 'cancelada', 'DD00F0', '2025-09-06 22:17:03'),
(8, 3, 1, '2025-09-06 18:15:00', '', 'cancelada', 'BFAEDA', '2025-09-06 22:51:25'),
(9, 3, 5, '2025-09-07 18:05:00', '', 'cancelada', 'F95D75', '2025-09-06 23:01:26'),
(10, 3, 6, '2025-09-07 18:30:00', '', 'cancelada', '88F63F', '2025-09-06 23:07:39'),
(11, 3, 5, '2025-09-06 19:20:00', 'Aja.. aqui voy', 'pendiente', '5A16CE', '2025-09-07 00:13:41'),
(12, 3, 8, '2025-09-07 16:19:00', '', 'pendiente', '3BD643', '2025-09-07 21:19:18'),
(13, 3, 8, '2025-09-08 16:22:00', '', 'pendiente', '431DEE', '2025-09-07 21:21:55'),
(14, 3, 3, '2025-09-07 18:56:00', '', 'pendiente', '97D4E0', '2025-09-07 21:38:22'),
(15, 3, 5, '2025-09-07 17:40:00', '', 'pendiente', '25A3F9', '2025-09-07 21:40:40'),
(16, 3, 6, '2025-09-08 16:42:00', '', 'pendiente', '3C1C1A', '2025-09-07 21:41:17'),
(17, 3, 11, '2025-09-07 19:26:00', '', 'pendiente', 'B5E145', '2025-09-07 21:48:00'),
(18, 3, 9, '2025-09-07 19:55:00', '', 'pendiente', '00B69B', '2025-09-07 21:58:04'),
(19, 5, 6, '2025-09-10 16:30:00', '', 'completada', '4BDE48', '2025-09-10 19:36:44'),
(20, 6, 7, '2025-09-10 16:50:00', '', 'cancelada', '4A74F0', '2025-09-10 19:38:01'),
(21, 6, 9, '2025-09-10 17:23:00', '', 'pendiente', '720F55', '2025-09-10 20:23:40'),
(22, 6, 11, '2025-09-10 16:03:00', '', 'cancelada', '733F3C', '2025-09-10 20:29:00'),
(23, 3, 9, '2025-09-10 17:58:00', '', 'completada', '1116E2', '2025-09-10 20:32:38'),
(24, 7, 2, '2025-09-10 18:25:00', 'guyg', 'completada', 'A6B2B3', '2025-09-10 21:21:10'),
(25, 8, 2, '2025-09-11 18:50:00', '', 'cancelada', 'C7623D', '2025-09-11 21:37:51'),
(26, 3, 4, '2025-09-11 21:58:00', '', 'confirmada', '392367', '2025-09-12 02:37:03'),
(27, 3, 4, '2025-09-11 23:04:00', '', 'pendiente', 'AFBDFC', '2025-09-12 04:00:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pendiente','pagado','cancelado') DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `appointment_id`, `total`, `status`, `created_at`) VALUES
(1, 3, NULL, 5000.00, 'pagado', '2025-09-06 03:49:52'),
(2, 3, NULL, 77000.00, 'pagado', '2025-09-06 05:15:45'),
(3, 3, NULL, 10000.00, 'pagado', '2025-09-06 22:52:34'),
(7, 3, NULL, 36000.00, 'pagado', '2025-09-06 23:19:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `qty`, `price`) VALUES
(1, 1, 8, 1, 5000.00),
(2, 2, 10, 1, 4000.00),
(3, 2, 3, 1, 28000.00),
(4, 2, 11, 1, 45000.00),
(5, 3, 8, 2, 5000.00),
(12, 7, 10, 2, 4000.00),
(13, 7, 3, 1, 28000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `method` enum('efectivo','transferencia','tarjeta') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(80) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `stock`, `active`) VALUES
(1, 'Tijeras profesionales', 'Herramientas', 45000.00, 10, 1),
(2, 'Cuchillas', 'Herramientas', 12000.00, 30, 1),
(3, 'Tinte para cabello', 'Cosméticos', 28000.00, 38, 1),
(4, 'Cabello humano', 'Extensiones', 180000.00, 5, 1),
(5, 'Cabello semi humano', 'Extensiones', 90000.00, 8, 1),
(6, 'Canecalón', 'Extensiones', 30000.00, 20, 1),
(7, 'Cabello para trenzas postizo', 'Extensiones', 25000.00, 25, 1),
(8, 'Cerveza', 'Bebidas', 5000.00, 97, 1),
(9, 'Papas fritas', 'Snacks', 3500.00, 80, 1),
(10, 'Jugo', 'Bebidas', 4000.00, 57, 1),
(11, 'Aguardiente', 'Licores', 45000.00, 11, 1),
(12, 'Ron', 'Licores', 55000.00, 10, 1),
(13, 'Arrechón por 1/2', 'Licores', 30000.00, 6, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `duration_minutes` int(11) NOT NULL DEFAULT 30,
  `category` enum('barberia','belleza') NOT NULL DEFAULT 'barberia',
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `services`
--

INSERT INTO `services` (`id`, `name`, `price`, `duration_minutes`, `active`) VALUES
(1, 'Peluqueada', 18000.00, 30, 1),
(2, 'Peinado', 15000.00, 30, 1),
(3, 'Uñas Permanentes', 35000.00, 50, 1),
(4, 'Cerquillo', 10000.00, 15, 1),
(5, 'Trenzas', 35000.00, 60, 1),
(6, 'Tinturada', 65000.00, 90, 1),
(7, 'Pestañas', 12000.00, 12, 1),
(8, 'Peluqueada + Cerquillo', 25000.00, 40, 1),
(9, 'Peluqueada Niño', 15000.00, 30, 1),
(10, 'Uñas Semipermanente', 20000.00, 50, 1),
(11, 'Uñas en Gel', 60000.00, 60, 1);

-- Clasificación para mostrar el catálogo correcto según la agenda elegida.
UPDATE `services` SET `category`='belleza' WHERE `id` IN (2,3,5,6,7,10,11);
UPDATE `services` SET `category`='barberia' WHERE `id` IN (1,4,8,9);


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `password_hash`, `created_at`, `reset_token`, `reset_expira`) VALUES
(3, 'JEFFERSON IBARGUEN MATURANA', 'jmaturana31821@gmail.com', '3114201687', 'user', '$2y$10$gLK0dHihHGTUztgre/wEjO3j9AzTgT3Asn6Vu4C2sZplhm4caa9Wq', '2025-09-06 03:35:10', NULL, NULL),
(4, 'Administrador', 'admin@salon.com', '3226398715', 'admin', '$2y$10$S1Yh.hrbrRQpyrWY5jSDhOvzFBXaXGBCKtswJJBuIIiuaKl5fzfZu', '2025-09-06 08:35:10', NULL, NULL),
(5, 'Lucas Orrego', 'lucas123@gmail.com', '3027422567', 'user', '$2y$10$xKSS7fc.IqALMuMiouENv.pgR08osDOzWVPY8HbJmxufyQlVsc9ja', '2025-09-10 19:21:35', NULL, NULL),
(6, 'Kevin Ortiz', 'kevin123@gmail.com', '3287632901', 'user', '$2y$10$lInTU1PzEnHRuyEX0O.gBeWtdXkKPjD3tp4aEF.ra3CbA5rl.Fh.a', '2025-09-10 19:23:02', NULL, NULL),
(7, 'emmanuel', 'morenoeaunuel066@gmail.com', '313131331', 'user', '$2y$10$RVyo5dEcfXTpwjL4g/ttJ.0ZuikqgK8/QXnbwExR3q9M2VpcL.OHm', '2025-09-10 21:18:25', NULL, NULL),
(8, 'Juan manuel Naranjo Gallego', 'jmanuelgallego2010@gmail.com', '322 7268394', 'user', '$2y$10$zybgwPkfyPB/Hs2V0pe2iOqorJR6IhzNnXUUVhRcIyMtWM7nNVsgW', '2025-09-11 21:36:20', NULL, NULL);

-- Dos administradores listos para ingresar al panel.
UPDATE `users` SET `name`='Administrador 1', `password_hash`='$2y$12$ujm5cm94UFHKB8Q/KE9ZVupfh.n8x4lmXPoHG7oO1Wnl4unxYkCdq' WHERE `id`=4;
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `password_hash`, `created_at`, `reset_token`, `reset_expira`) VALUES
(9, 'Administrador 2', 'admin2@salon.com', NULL, 'admin', '$2y$12$ujm5cm94UFHKB8Q/KE9ZVupfh.n8x4lmXPoHG7oO1Wnl4unxYkCdq', CURRENT_TIMESTAMP, NULL, NULL);


--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `idx_staff_status_datetime` (`staff_id`,`status`,`appt_datetime`);

--
-- Indices de la tabla `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `idx_payments_created_at` (`created_at`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Filtros para la tabla `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
