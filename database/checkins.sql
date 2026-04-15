CREATE TABLE checkins (
    id INT AUTO_INCREMENT PRIMARY KEY,

    library_id INT NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    serial_number VARCHAR(255) NOT NULL,
    admin_id INT NOT NULL,

    checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    status ENUM('IN', 'OUT') NOT NULL,

    -- Foreign Keys
    CONSTRAINT fk_checkins_library
        FOREIGN KEY (library_id) REFERENCES libraries(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_checkins_student
        FOREIGN KEY (student_id) REFERENCES students(student_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_checkins_device
        FOREIGN KEY (serial_number) REFERENCES devices(serial_number)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_checkins_admin
        FOREIGN KEY (admin_id) REFERENCES admins(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
ALTER TABLE checkins
ADD UNIQUE (student_id, serial_number, status);