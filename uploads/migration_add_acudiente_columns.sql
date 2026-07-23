ALTER TABLE `el_students`
  ADD COLUMN `cedula_acudiente` varchar(20) DEFAULT NULL AFTER `comuna`,
  ADD COLUMN `nombre_acudiente` varchar(100) DEFAULT NULL AFTER `cedula_acudiente`,
  ADD COLUMN `email_acudiente` varchar(100) DEFAULT NULL AFTER `nombre_acudiente`,
  ADD COLUMN `telefono_acudiente` varchar(20) DEFAULT NULL AFTER `email_acudiente`;
