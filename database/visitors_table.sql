-- Visitors table for ELACS library management
CREATE TABLE IF NOT EXISTS visitors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(15),
  email VARCHAR(100),
  purpose TEXT,
  visit_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  leave_time DATETIME NULL,
  status ENUM('IN', 'OUT') DEFAULT 'IN',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data
INSERT INTO visitors (name, phone, purpose) VALUES
('John Doe', '0712345678', 'Research project'),
('Jane Smith', '0723456789', 'Group study'),
('Mike Johnson', '0734567890', 'Book borrowing');

