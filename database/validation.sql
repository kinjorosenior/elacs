-- Add constraints
ALTER TABLE students ADD CONSTRAINT chk_kabarak_email 
CHECK (email LIKE '%@kabarak.ac.ke');

-- Add brand column to devices
ALTER TABLE devices ADD COLUMN brand VARCHAR(50);

-- Update serial validation (trigger/example)
DELIMITER //
CREATE TRIGGER validate_serial BEFORE INSERT ON devices
FOR EACH ROW
BEGIN
  IF NEW.serial_number NOT REGEXP '^(HP|DELL|LENOVO|ASUS|ACER)[0-9A-Z]{6,}$' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid serial format (HP/DELL/LENOVO prefix)';
  END IF;
END//
DELIMITER ;

