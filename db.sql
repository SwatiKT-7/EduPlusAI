-- Full schema in one file
CREATE DATABASE IF NOT EXISTS edupulse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edupulse;

-- Tenancy & Users
CREATE TABLE IF NOT EXISTS tenants (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(60) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS departments (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(120) NOT NULL,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  role ENUM('admin','hod','faculty','student') NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) UNIQUE,
  phone VARCHAR(20),
  enrollment_no VARCHAR(60),
  dept_id BIGINT,
  device_fingerprint VARCHAR(120),
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Curriculum
CREATE TABLE IF NOT EXISTS courses(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  dept_id BIGINT,
  code VARCHAR(40) NOT NULL,
  name VARCHAR(200) NOT NULL,
  credits INT DEFAULT 3,
  semester INT,
  capacity INT DEFAULT 60,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS course_prereq(
  course_id BIGINT NOT NULL,
  prereq_course_id BIGINT NOT NULL,
  PRIMARY KEY(course_id, prereq_course_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (prereq_course_id) REFERENCES courses(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS competencies(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(160) NOT NULL,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS course_competency(
  course_id BIGINT NOT NULL,
  competency_id BIGINT NOT NULL,
  weight TINYINT DEFAULT 1,
  PRIMARY KEY(course_id,competency_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (competency_id) REFERENCES competencies(id) ON DELETE CASCADE
);

-- Classes & Attendance
CREATE TABLE IF NOT EXISTS classes(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  course_id BIGINT NOT NULL,
  faculty_id BIGINT NOT NULL,
  room VARCHAR(60),
  start_at DATETIME,
  end_at DATETIME,
  geo_lat DECIMAL(10,7),
  geo_lng DECIMAL(10,7),
  radius_m INT DEFAULT 60,
  status ENUM('scheduled','live','closed') DEFAULT 'scheduled',
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS qr_tokens(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  class_id BIGINT NOT NULL,
  token CHAR(64) NOT NULL,
  valid_from DATETIME NOT NULL,
  valid_to DATETIME NOT NULL,
  issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  used_count INT DEFAULT 0,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attendance_events(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  class_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  status ENUM('P','A','L') NOT NULL,
  method ENUM('qr','kiosk') NOT NULL,
  device_id VARCHAR(120),
  lat DECIMAL(10,7),
  lng DECIMAL(10,7),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_mark (class_id, user_id),
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS audit_logs(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  actor_id BIGINT,
  entity VARCHAR(40),
  entity_id BIGINT,
  action VARCHAR(40),
  details JSON,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Assessments & Analytics
CREATE TABLE IF NOT EXISTS assessments(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  course_id BIGINT NOT NULL,
  type ENUM('quiz','mid','lab','assignment'),
  max_marks INT DEFAULT 100,
  weight TINYINT DEFAULT 10,
  date DATE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS assessment_scores(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  assessment_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  marks DECIMAL(5,2) NOT NULL,
  FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS student_metrics(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  course_id BIGINT,
  risk_score TINYINT,
  attn_trend DECIMAL(5,2),
  attn_cum DECIMAL(5,2),
  score_z DECIMAL(5,2),
  submissions_ratio DECIMAL(5,2),
  term_attn_projection DECIMAL(5,2),
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

-- Knowledge Base & AI
CREATE TABLE IF NOT EXISTS kb_sources(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  title VARCHAR(200) NOT NULL,
  type ENUM('pdf','url','doc') NOT NULL,
  url TEXT,
  status ENUM('queued','indexed','failed') DEFAULT 'queued',
  visibility ENUM('admin','faculty','student','all') DEFAULT 'all',
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS kb_chunks(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  source_id BIGINT NOT NULL,
  chunk_no INT NOT NULL,
  text MEDIUMTEXT NOT NULL,
  embedding VARBINARY(6144),
  meta JSON,
  FOREIGN KEY (source_id) REFERENCES kb_sources(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ai_interactions(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT,
  topic VARCHAR(120),
  prompt_tokens INT,
  completion_tokens INT,
  cost_cents INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Placement
CREATE TABLE IF NOT EXISTS placements_jd(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  company VARCHAR(160),
  role VARCHAR(160),
  text MEDIUMTEXT,
  skills_vector VARBINARY(6144),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS student_skills(
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  skill VARCHAR(120),
  level TINYINT,
  vector VARBINARY(6144),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------------- SEED ----------------
INSERT INTO tenants(name, slug) VALUES ('Sample College', 'sample')
  ON DUPLICATE KEY UPDATE name=VALUES(name);
SET @tenant := (SELECT id FROM tenants WHERE slug='sample');

INSERT INTO departments(tenant_id,name) VALUES (@tenant,'CSE')
  ON DUPLICATE KEY UPDATE name=VALUES(name);
SET @dept := (SELECT id FROM departments WHERE name='CSE' AND tenant_id=@tenant);

-- Demo users (Password seeded via MySQL PASSWORD(); switch to password_hash() in prod)
INSERT IGNORE INTO users(tenant_id,role,name,email,password_hash,dept_id)
VALUES
(@tenant,'admin','Admin User','admin@example.com', PASSWORD('password'), @dept),
(@tenant,'faculty','Dr. Rao','rao@example.com', PASSWORD('password'), @dept),
(@tenant,'student','Kulwant','kulwant@example.com', PASSWORD('password'), @dept),
(@tenant,'student','Asha','asha@example.com', PASSWORD('password'), @dept),
(@tenant,'student','Ravi','ravi@example.com', PASSWORD('password'), @dept);

INSERT IGNORE INTO courses(tenant_id,dept_id,code,name,credits,semester,capacity)
VALUES
(@tenant,@dept,'CS101','Programming I',4,1,60),
(@tenant,@dept,'CS102','Data Structures',4,2,60);

INSERT IGNORE INTO course_prereq(course_id, prereq_course_id)
SELECT c2.id, c1.id FROM courses c1, courses c2
WHERE c1.code='CS101' AND c2.code='CS102';

-- a class scheduled now for CS101 by faculty 'rao'
SET @faculty := (SELECT id FROM users WHERE email='rao@example.com');
SET @course := (SELECT id FROM courses WHERE code='CS101');
INSERT INTO classes(tenant_id,course_id,faculty_id,room,start_at,end_at,geo_lat,geo_lng,radius_m,status)
VALUES(@tenant,@course,@faculty,'R-101', NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR), 28.6139, 77.2090, 80, 'scheduled');
ALTER TABLE classes
  ADD COLUMN mode ENUM('offline','online','hybrid') DEFAULT 'offline' AFTER status,
  ADD COLUMN meeting_url VARCHAR(255) NULL AFTER mode,
  ADD COLUMN started_at DATETIME NULL AFTER meeting_url,
  ADD COLUMN closed_at DATETIME NULL AFTER started_at,
  ADD COLUMN wifi_bssid VARCHAR(64) NULL AFTER geo_lng,
  ADD COLUMN ble_beacon_id VARCHAR(64) NULL AFTER wifi_bssid;
ALTER TABLE attendance_events
  ADD COLUMN window_start INT NULL AFTER method,
  ADD COLUMN anomaly_flag TINYINT(1) DEFAULT 0 AFTER window_start,
  ADD COLUMN anomaly_reason VARCHAR(120) NULL AFTER anomaly_flag,
  ADD COLUMN ip_addr VARCHAR(45) NULL AFTER lng,
  ADD COLUMN user_agent VARCHAR(160) NULL AFTER ip_addr;
CREATE TABLE IF NOT EXISTS alerts (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  course_id BIGINT NULL,
  type ENUM('low_attendance','absence_streak','projection_fail') NOT NULL,
  severity ENUM('low','medium','high') NOT NULL,
  message VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  resolved_at DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);
ALTER TABLE student_metrics
  ADD COLUMN attn_streak_absent INT DEFAULT 0 AFTER attn_cum,
  ADD COLUMN attn_last14 DECIMAL(5,2) NULL AFTER attn_streak_absent,
  ADD COLUMN risk_reason JSON NULL AFTER term_attn_projection;
