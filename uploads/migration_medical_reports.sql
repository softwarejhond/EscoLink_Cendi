CREATE TABLE IF NOT EXISTS `el_medical_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `description` TEXT NOT NULL,
    `observation` TEXT DEFAULT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `file_name` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`student_id`) REFERENCES `el_students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
