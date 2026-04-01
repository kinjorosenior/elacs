USE elacs;

CREATE TABLE IF NOT EXISTS library_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  open_weekday TIME DEFAULT '08:00:00',
  close_weekday TIME DEFAULT '22:00:00',
  open_saturday TIME DEFAULT '08:00:00',
  close_saturday TIME DEFAULT '16:00:00',
  open_sunday TIME DEFAULT '14:00:00',
  close_sunday TIME DEFAULT '17:00:00',
  reminder_minutes INT DEFAULT 30,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO library_settings (id, open_weekday, close_weekday, open_saturday, close_saturday, open_sunday, close_sunday, reminder_minutes) VALUES 
(1, '08:00:00', '22:00:00', '08:00:00', '16:00:00', '14:00:00', '17:00:00', 30);

SELECT * FROM library_settings;

