-- ============================================================================
--  Silver Oak International School — School Website + Admin Panel
--  MySQL 8 / MariaDB schema, indexes, and demo content.
--  Pure PHP 8 + MySQL. No frameworks.
--
--  INSTALL (cPanel / XAMPP)
--  1. Create a database (and a user on cPanel), e.g. `school_website`.
--  2. Import this file:  phpMyAdmin → Import → database.sql
--  3. Create the first admin password hash:
--        php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT);"
--  4. Replace the placeholder hash in the INSERT below (or run the UPDATE).
--  5. Put your real credentials in school-php/includes/config.php.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS school_website
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_website;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------- admins ----
CREATE TABLE IF NOT EXISTS admins (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(80)  NOT NULL,
  email         VARCHAR(160) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name          VARCHAR(120) NOT NULL DEFAULT 'Administrator',
  role          VARCHAR(20)  NOT NULL DEFAULT 'admin',
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY admins_username_unique (username),
  UNIQUE KEY admins_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Brute-force protection log
CREATE TABLE IF NOT EXISTS login_attempts (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip         VARCHAR(45)  NOT NULL,
  username   VARCHAR(80)  NOT NULL DEFAULT '',
  success    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY login_attempts_ip_idx (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------- settings ---
CREATE TABLE IF NOT EXISTS settings (
  `key`      VARCHAR(80) NOT NULL,
  value      MEDIUMTEXT,
  updated_at TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------ academic programs ---
CREATE TABLE IF NOT EXISTS academic_programs (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug        VARCHAR(140) NOT NULL,
  title       VARCHAR(160) NOT NULL,
  level       VARCHAR(40)  NOT NULL DEFAULT 'General',
  summary     TEXT,
  content     MEDIUMTEXT,
  image       VARCHAR(255) NOT NULL DEFAULT '',
  sort_order  INT          NOT NULL DEFAULT 0,
  published   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY academic_programs_slug_unique (slug),
  KEY academic_programs_published_idx (published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subjects (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(160) NOT NULL,
  level       VARCHAR(40)  NOT NULL DEFAULT 'All Levels',
  description TEXT,
  sort_order  INT          NOT NULL DEFAULT 0,
  published   TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY subjects_published_idx (published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------- people ---
CREATE TABLE IF NOT EXISTS faculty (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(160) NOT NULL,
  position      VARCHAR(160) NOT NULL DEFAULT '',
  department    VARCHAR(80)  NOT NULL DEFAULT 'General',
  qualification VARCHAR(200) NOT NULL DEFAULT '',
  bio           TEXT,
  photo         VARCHAR(255) NOT NULL DEFAULT '',
  email         VARCHAR(160) NOT NULL DEFAULT '',
  sort_order    INT          NOT NULL DEFAULT 0,
  published     TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY faculty_published_idx (published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(160) NOT NULL,
  designation VARCHAR(160) NOT NULL DEFAULT '',
  quote       TEXT         NOT NULL,
  photo       VARCHAR(255) NOT NULL DEFAULT '',
  sort_order  INT          NOT NULL DEFAULT 0,
  published   TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY testimonials_published_idx (published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------- campus ---
CREATE TABLE IF NOT EXISTS facilities (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title       VARCHAR(160) NOT NULL,
  description TEXT,
  image       VARCHAR(255) NOT NULL DEFAULT '',
  icon        VARCHAR(10)  NOT NULL DEFAULT '',
  sort_order  INT          NOT NULL DEFAULT 0,
  published   TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY facilities_published_idx (published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student life: sports, clubs, competitions, trips, cultural, achievements
CREATE TABLE IF NOT EXISTS activities (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title       VARCHAR(160) NOT NULL,
  category    VARCHAR(40)  NOT NULL DEFAULT 'Clubs',
  description TEXT,
  image       VARCHAR(255) NOT NULL DEFAULT '',
  sort_order  INT          NOT NULL DEFAULT 0,
  published   TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY activities_category_idx (category, published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- content ---
CREATE TABLE IF NOT EXISTS news (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug         VARCHAR(160) NOT NULL,
  title        VARCHAR(200) NOT NULL,
  category     VARCHAR(40)  NOT NULL DEFAULT 'News',
  excerpt      TEXT,
  content      MEDIUMTEXT,
  image        VARCHAR(255) NOT NULL DEFAULT '',
  published    TINYINT(1)   NOT NULL DEFAULT 1,
  published_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY news_slug_unique (slug),
  KEY news_published_idx (published, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug       VARCHAR(160) NOT NULL,
  title      VARCHAR(200) NOT NULL,
  excerpt    TEXT,
  content    MEDIUMTEXT,
  image      VARCHAR(255) NOT NULL DEFAULT '',
  event_date DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  location   VARCHAR(200) NOT NULL DEFAULT '',
  published  TINYINT(1)   NOT NULL DEFAULT 1,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY events_slug_unique (slug),
  KEY events_date_idx (published, event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notices (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title       VARCHAR(200) NOT NULL,
  category    VARCHAR(40)  NOT NULL DEFAULT 'General',
  description TEXT,
  document    VARCHAR(255) NOT NULL DEFAULT '',
  notice_date TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  published   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY notices_published_idx (published, notice_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------ media ---
CREATE TABLE IF NOT EXISTS gallery_albums (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title       VARCHAR(160) NOT NULL,
  category    VARCHAR(40)  NOT NULL DEFAULT 'Campus',
  description TEXT,
  cover_image VARCHAR(255) NOT NULL DEFAULT '',
  published   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY gallery_albums_published_idx (published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gallery_images (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  album_id   INT UNSIGNED NOT NULL,
  src        VARCHAR(255) NOT NULL,
  caption    VARCHAR(220) NOT NULL DEFAULT '',
  sort_order INT          NOT NULL DEFAULT 0,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY gallery_images_album_idx (album_id),
  CONSTRAINT gallery_images_album_fk FOREIGN KEY (album_id)
    REFERENCES gallery_albums (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS downloads (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title           VARCHAR(200) NOT NULL,
  category        VARCHAR(40)  NOT NULL DEFAULT 'General',
  description     TEXT,
  file_path       VARCHAR(255) NOT NULL DEFAULT '',
  file_name       VARCHAR(200) NOT NULL DEFAULT '',
  published       TINYINT(1)   NOT NULL DEFAULT 1,
  downloads_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY downloads_published_idx (published, category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------- forms ---
CREATE TABLE IF NOT EXISTS admissions (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_name      VARCHAR(160) NOT NULL,
  dob               VARCHAR(20)  NOT NULL DEFAULT '',
  gender            VARCHAR(20)  NOT NULL DEFAULT '',
  grade             VARCHAR(20)  NOT NULL DEFAULT '',
  previous_school   VARCHAR(200) NOT NULL DEFAULT '',
  parent_name       VARCHAR(160) NOT NULL DEFAULT '',
  relationship      VARCHAR(40)  NOT NULL DEFAULT '',
  phone             VARCHAR(40)  NOT NULL DEFAULT '',
  email             VARCHAR(160) NOT NULL DEFAULT '',
  address           TEXT,
  emergency_contact VARCHAR(200) NOT NULL DEFAULT '',
  additional_info   TEXT,
  document          VARCHAR(255) NOT NULL DEFAULT '',
  status            VARCHAR(20)  NOT NULL DEFAULT 'new',
  created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY admissions_status_idx (status),
  KEY admissions_created_idx (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admission_documents (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  admission_id  INT UNSIGNED NOT NULL,
  file_path     VARCHAR(255) NOT NULL,
  original_name VARCHAR(200) NOT NULL DEFAULT '',
  uploaded_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY admission_documents_admission_idx (admission_id),
  CONSTRAINT admission_documents_admission_fk FOREIGN KEY (admission_id)
    REFERENCES admissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(160) NOT NULL,
  email      VARCHAR(160) NOT NULL,
  phone      VARCHAR(40)  NOT NULL DEFAULT '',
  subject    VARCHAR(200) NOT NULL DEFAULT '',
  message    TEXT         NOT NULL,
  is_read    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY contact_messages_created_idx (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
--  INITIAL ADMIN ACCOUNT
-- ----------------------------------------------------------------------------
--  Replace this hash with a unique password hash before going live:
--     php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT);"
--  then:
--     UPDATE admins SET password_hash='<new hash>' WHERE id=1;
-- ============================================================================
INSERT INTO admins (username, email, password_hash, name, role) VALUES
('admin', 'm.qazim1997@gmail.com',
 '$2y$10$aYEux01Fc.6TZXLNwpQdlOC1dmrAJurfXyEo8JH6iFiy8VRhRX5u2',
 'School Administrator', 'admin');

-- ============================================================================
--  SETTINGS — every field here is editable from Admin → General Settings.
-- ============================================================================
INSERT INTO settings (`key`, value) VALUES
  ('school_name',        'Silver Oak International School'),
  ('school_short',       'Silver Oak'),
  ('tagline',            'Nurturing Minds. Building Character.'),
  ('accent_color',       '#1B3A6B'),
  ('phone',              '+1 (555) 014-2450'),
  ('whatsapp',           '15550142450'),
  ('email',              'info@silveroak.example.edu'),
  ('admissions_email',   'admissions@silveroak.example.edu'),
  ('address',            '14 Knowledge Parkway, Greenfield, CA 94105'),
  ('office_hours',       'Monday – Friday: 7:30 AM – 3:30 PM'),
  ('facebook',           ''),
  ('instagram',          ''),
  ('twitter',            ''),
  ('youtube',            ''),
  ('linkedin',           ''),
  ('hero_image',         'assets/images/hero.jpg'),
  ('hero_kicker',        'Admissions Open · 2025–26'),
  ('hero_title',         'Where Curiosity Becomes Confidence'),
  ('hero_subtitle',      'A private day school offering an enriched, globally-minded education from Early Years through Grade 12 — small classes, caring teachers, and a campus built for discovery.'),
  ('stat_students',      '1250'),
  ('stat_teachers',      '85'),
  ('stat_classes',       '48'),
  ('stat_years',         '25'),
  ('about_title',        'A Legacy of Learning Since 2000'),
  ('about_short',        'For 25 years we have blended academic rigour with warmth, creativity and character. (Demo content)'),
  ('about_content',      'Founded in 2000, our school began with 12 classrooms, 9 teachers and a simple belief: children learn best when they are known, challenged and cared for.\n\nToday our green campus serves students from Early Years to Grade 12 with a curriculum that unites the sciences, humanities, arts and athletics.\n\nOur graduates carry with them curiosity, integrity and the confidence to lead.'),
  ('about_image',        'assets/images/about.jpg'),
  ('mission',            'To provide a world-class education that inspires every student to think critically, act compassionately and lead with integrity.'),
  ('vision',             'A school where every child is known, every talent is discovered, and every graduate steps into the world ready to shape it for the better.'),
  ('core_values',        'Academic Excellence: High expectations and a lifelong love of learning.\nIntegrity: We do the right thing — even when no one is watching.\nCompassion: Kindness and service are practiced daily.\nCuriosity: Questions are celebrated as much as answers.\nLeadership: Every student learns to lead themselves first.\nGlobal Citizenship: We prepare students for an interconnected world.'),
  ('principal_name',        'Dr. Eleanor A. Whitfield'),
  ('principal_designation', 'Principal'),
  ('principal_photo',       'assets/images/principal.jpg'),
  ('principal_message',     'Thank you for considering our school for your child''s education.\n\nA school is its people — and ours are extraordinary. Walk our corridors and you will find robotics beside poetry, debate beside chemistry, and laughter beside serious study.\n\nMy door is always open to parents and students alike.'),
  ('admission_open',        '1'),
  ('admission_note',        'Applications for the 2025–26 academic year are now open. Limited seats remain in select grades.'),
  ('admission_requirements', 'Completed application form\nBirth certificate (copy)\nPrevious 2 years of school reports\nTransfer certificate (if applicable)\nPassport-size photographs\nCopy of parent / guardian ID\nImmunisation record'),
  ('fee_structure',         'Early Years (Pre-KG – KG): 1,200 per term\nPrimary (Grades 1 – 5): 1,500 per term\nMiddle School (Grades 6 – 8): 1,800 per term\nSecondary (Grades 9 – 12): 2,200 per term\nOne-time admission fee: 500\nSibling discount: 10% for the second child'),
  ('admission_dates',       'Applications open — October 1, 2025\nPriority deadline — January 31, 2026\nEntrance assessments — February 10 – 21, 2026\nFamily interviews — March 2 – 13, 2026\nDecisions released — March 20, 2026\nEnrollment & orientation — April 6 – 17, 2026'),
  ('curriculum',            'Our curriculum blends a strong academic core with inquiry-based learning, digital literacy and the arts.\n\nEarly Years follows a play-based framework building literacy, numeracy and social confidence.\n\nSecondary students pursue a college-preparatory program with electives and university counselling from Grade 9.'),
  ('exam_system',           'The year has two semesters. Continuous assessment carries 40%; midterm and final examinations 60%.\n\nProgress reports are issued each quarter, followed by parent–teacher conferences.\n\nGrades 10 and 12 sit pre-board examinations each January.'),
  ('seo_title',             'Silver Oak International School | Private Day School'),
  ('seo_description',       'A private day school offering Early Years to Grade 12 education with small classes, expert faculty and a world-class campus. Demo website.'),
  ('seo_keywords',          'private school, international school, admissions, academics'),
  ('og_image',              'assets/images/hero.jpg')
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- ============================================================================
--  DEMO CONTENT
--  Images point at assets/images/*.jpg in this package. To use your own photos,
--  upload them through the admin panel — the paths update automatically.
-- ============================================================================
INSERT INTO academic_programs (slug, title, level, summary, content, image, sort_order, published) VALUES
('early-years','Early Years','Early Years','Play-based learning for Pre-KG to KG where curiosity, confidence and kindness take root.','Our Early Years program provides a warm, stimulating first school experience for children aged 3 to 6.\n\nThrough structured play, story-telling, music and outdoor exploration, children build early literacy and numeracy alongside social skills.\n\nSmall classes with a teacher and an assistant mean every child is truly known.','assets/images/hero.jpg',1,1),
('primary-school','Primary School','Primary','Grades 1–5: strong foundations in literacy, numeracy, science and character.','In Primary School, students develop the academic habits that carry them through life — reading deeply, questioning boldly and working collaboratively.\n\nOur curriculum balances English, mathematics, science and social studies with art, music, physical education and values education.\n\nFrom Grade 4, specialist teachers lead subjects.','assets/images/about.jpg',2,1),
('middle-school','Middle School','Middle School','Grades 6–8: exploration, identity and academic challenge in equal measure.','Middle School is a time of remarkable growth. Our program channels that energy into hands-on science, project-based humanities, robotics, debate and the arts.\n\nAdvisory groups meet daily, giving every student a mentor who tracks their development.\n\nElectives begin in Grade 7 — coding, drama and environmental club.','assets/images/hero.jpg',3,1),
('secondary-school','Secondary School','Secondary','Grades 9–12: a rigorous college-preparatory journey with world-ready outcomes.','Our Secondary School program prepares students for top universities with advanced coursework, research projects and personalised counselling.\n\nStudents choose from STEM, humanities and arts pathways, sit advanced placements and complete a capstone service project.\n\nLeadership opportunities abound — student council, MUN and peer mentoring.','assets/images/about.jpg',4,1);

INSERT INTO subjects (name, level, description, sort_order, published) VALUES
('English Language & Literature','Primary','Reading, writing and speaking with confidence.',0,1),
('Mathematics','Primary','Number sense, problem solving and reasoning.',1,1),
('Environmental Science','Primary','Understanding the natural world.',2,1),
('Art & Craft','Primary','Creativity and fine motor development.',3,1),
('Physical Education','Primary','Movement, teamwork and health.',4,1),
('English Language & Literature','Middle School','Analysis, argument and creative writing.',5,1),
('Mathematics','Middle School','Algebra, geometry and statistics.',6,1),
('Integrated Science','Middle School','Practical, laboratory-based science.',7,1),
('Social Studies','Middle School','History, geography and civics.',8,1),
('Computer Science','Middle School','Coding fundamentals and digital citizenship.',9,1),
('Second Language (Spanish / French)','Middle School','Communicative language skills.',10,1),
('Physics','Secondary','Mechanics, waves, electricity and modern physics.',11,1),
('Chemistry','Secondary','Physical, organic and inorganic chemistry.',12,1),
('Biology','Secondary','Cells, genetics, ecology and human biology.',13,1),
('Mathematics (Core & Advanced)','Secondary','Calculus, algebra and discrete mathematics.',14,1),
('Economics & Business','Secondary','Micro, macro and enterprise.',15,1),
('Computer Science & Robotics','Secondary','Programming, data and engineering design.',16,1),
('Psychology','Secondary','Human behaviour and research methods.',17,1),
('World History & Civics','Secondary','Global history and citizenship.',18,1),
('Visual & Performing Arts','Secondary','Studio art, music and theatre.',19,1);

INSERT INTO faculty (name, position, department, qualification, bio, photo, email, sort_order, published) VALUES
('Dr. Maya Coleman','Vice Principal','Leadership','Ed.D. Educational Leadership','Two decades in international education, leading curriculum and faculty development.','','',1,1),
('Sarah Mitchell','Head of Primary Years','Leadership','M.Ed. Primary Education','Champions early literacy and a joyful, structured start to school life.','','',2,1),
('David Okafor','Head of Science','Leadership','M.Sc. Physics, PGCE','Leads our STEM labs and the award-winning robotics team.','','',3,1),
('Priya Raman','Mathematics Teacher','Mathematics','M.Sc. Mathematics, B.Ed.','Makes abstract concepts tangible through modelling and real-world problems.','','',4,1),
('Elena Vasquez','English & Literature Teacher','Languages','M.A. English Literature','Debate coach and literary magazine mentor.','','',5,1),
('Amara Diallo','Creative Arts Teacher','Arts','B.F.A. Fine Arts','Runs the studio arts program and the annual student exhibition.','','',6,1),
('James Carter','Physical Education Instructor','Sports','B.Sc. Sports Science','Coordinates athletics teams and the morning fitness program.','','',7,1),
('Lena Fischer','Computer Science Teacher','General','M.Sc. Computer Science','Teaches coding, robotics and digital citizenship.','','',8,1);

INSERT INTO testimonials (name, designation, quote, photo, sort_order, published) VALUES
('Rachel Thompson','Parent of Grade 3 & Grade 7 students','Silver Oak didn''t just teach my children — it discovered them. The teachers noticed talents we hadn''t even seen at home.','',1,1),
('Anil & Meera Kapoor','Parents of a Grade 10 student','The university counselling team guided our daughter every step of the way. She walked into her exams calm, prepared and confident.','',2,1),
('Daniel Osei','Alumnus, Class of 2019','The debate room taught me to think; the science lab taught me to question. I use both every single day at university.','',3,1),
('Grace Liu','Parent of a Grade 1 student','Our son runs to school every morning. That says everything about the warmth of this community.','',4,1);

INSERT INTO facilities (title, description, image, icon, sort_order, published) VALUES
('Smart Classrooms','Every classroom features interactive displays, ergonomic furniture and natural light.','assets/images/hero.jpg','',1,1),
('Science Laboratories','Purpose-built physics, chemistry and biology labs with modern equipment and safety systems.','assets/images/about.jpg','',2,1),
('Computer Laboratory','A 30-seat computing lab with current hardware, high-speed internet and robotics kits.','assets/images/hero.jpg','',3,1),
('Library & Learning Commons','Over 12,000 books, digital resources and quiet study zones across two levels.','assets/images/about.jpg','',4,1),
('Playgrounds & Sports Fields','Age-appropriate playgrounds, a full-size field, basketball courts and athletics track.','assets/images/hero.jpg','',5,1),
('Auditorium','A 400-seat theatre for assemblies, performances, concerts and graduation ceremonies.','assets/images/about.jpg','',6,1),
('School Transport','GPS-tracked buses with trained attendants serving the whole metropolitan area.','assets/images/hero.jpg','',7,1),
('Cafeteria','Nutritionist-planned menus, fresh meals daily and a bright, social dining hall.','assets/images/about.jpg','',8,1),
('Medical & First Aid','A supervised medical room, full-time nurse and documented emergency protocols.','assets/images/hero.jpg','',9,1);

INSERT INTO activities (title, category, description, image, sort_order, published) VALUES
('Athletics & Team Sports','Sports','Soccer, basketball, track and field with inter-school tournaments every term.','assets/images/hero.jpg',1,1),
('Swimming Program','Sports','Learn-to-swim through squad training in our partnership pool facility.','assets/images/about.jpg',2,1),
('Robotics & Coding Club','Clubs','Build, program and compete — from first robots to regional championships.','assets/images/hero.jpg',3,1),
('Debate & Model United Nations','Clubs','Sharpen public speaking and diplomacy in citywide and national conferences.','assets/images/about.jpg',4,1),
('Math Olympiad Team','Competitions','Weekly training and national mathematics olympiad participation.','assets/images/hero.jpg',5,1),
('Science Fair Championship','Competitions','Annual fair with judged projects and a community open day.','assets/images/about.jpg',6,1),
('Outdoor Education Trips','Field Trips','Overnight camps, museum visits and nature expeditions by grade.','assets/images/hero.jpg',7,1),
('Cultural Festival','Cultural','A celebration of music, dance, food and traditions from our community.','assets/images/about.jpg',8,1),
('University Acceptances','Achievements','The Class of 2025 earned 90+ offers from top universities worldwide.','assets/images/hero.jpg',9,1);

INSERT INTO news (slug, title, category, excerpt, content, image, published, published_at) VALUES
('science-exhibition-2025-success','Science Exhibition 2025: A Resounding Success','Achievement','Over 120 student projects impressed judges and families at our biggest science exhibition yet.','This year''s Science Exhibition welcomed more than 600 visitors across two days. Students from Grade 4 to Grade 12 presented 120 projects — from water purification prototypes to AI-assisted wildlife cameras.\n\nThe Grade 9 team took the top prize for their low-cost water quality sensor, developed with mentorship from our science faculty.\n\nWe are grateful to our parent volunteers for judging. Full results are available from the school office.','assets/images/hero.jpg',1, NOW() - INTERVAL 9 DAY),
('annual-sports-meet-records','Annual Sports Meet: Three School Records Broken','Achievement','A glorious day of athletics saw three school records fall and four house trophies awarded.','Blue House lifted the Championship Trophy at this year''s Annual Sports Meet, but the day belonged to every student who competed.\n\nRecords fell in the 100m sprint, the 400m and long jump. Parents cheered from packed stands as relay teams battled to the line.\n\nOur thanks go to the PE department and the parent volunteer team.','assets/images/about.jpg',1, NOW() - INTERVAL 21 DAY),
('debate-team-city-champions','Debate Team Crowned City Champions','Achievement','Our senior debate team won the Greenfield City Schools Debating Championship.','After five rounds against twelve schools, our senior debaters argued their way to the city title.\n\nThe team captain was named Best Speaker of the tournament. The team now prepares for the national invitationals.\n\nInterested in joining? The Debate Club meets every Tuesday in Room 204.','assets/images/hero.jpg',1, NOW() - INTERVAL 35 DAY),
('stem-lab-opening','New STEM & Robotics Lab Opens This Fall','Announcement','Construction begins on a dedicated STEM wing with robotics lab, maker space and design studio.','We are delighted to announce that our new STEM & Robotics Lab will open at the start of the fall term.\n\nThe space will include a fabrication maker space, a dedicated robotics arena and a design studio, serving students from Grade 4 upward.\n\nThe project is funded by our development campaign with support from the Parents'' Association.','assets/images/about.jpg',1, NOW() - INTERVAL 48 DAY),
('university-acceptances-2025','Class of 2025: 90% Receive First-Choice University Offers','Achievement','Graduates earned offers from leading universities across the country and abroad.','The Class of 2025 has set a new school record, with 90% of graduates receiving offers from their first-choice universities.\n\nStudents will study engineering, medicine, economics, design and the liberal arts on three continents.\n\nOur counselling program begins in Grade 9 — one more way students graduate world-ready.','assets/images/hero.jpg',1, NOW() - INTERVAL 60 DAY),
('cultural-festival-highlights','Cultural Festival Showcases Extraordinary Student Talent','Event','Music, dance and theatre from 40 cultures filled the auditorium for two unforgettable evenings.','Our annual Cultural Festival featured over 300 student performers across music, dance and drama — celebrating the forty cultures represented in our school community.\n\nHighlights included a senior orchestra piece, a fusion dance performance and the Grade 6 choir.\n\nProceeds from ticket sales support the student arts fund.','assets/images/about.jpg',1, NOW() - INTERVAL 75 DAY);

INSERT INTO events (slug, title, excerpt, content, image, event_date, location, published) VALUES
('open-house-spring','Spring Open House & Campus Tours','Prospective families are invited to tour the campus and meet our faculty.','Join us for a guided tour of our campus, meet teachers from every department and learn about admissions for the coming year.\n\nSessions run at 9:00 AM and 11:00 AM. Registration is required as places are limited.','assets/images/hero.jpg', NOW() + INTERVAL 14 DAY, 'Main Campus — Reception Hall', 1),
('inter-school-swimming-gala','Inter-School Swimming Gala','Our swim squad hosts six schools for the annual gala.','Events range from 25m novice races to 100m championship finals. Spectators welcome — entry via the aquatics centre gate.','assets/images/about.jpg', NOW() + INTERVAL 28 DAY, 'Aquatics Centre', 1),
('parent-teacher-conference','Parent–Teacher Conferences','One-on-one progress meetings for Grades 6–12 families.','Booking slots open one week prior via the school office. Conferences run in 15-minute sessions from 2:00 PM to 6:00 PM.','assets/images/hero.jpg', NOW() - INTERVAL 12 DAY, 'Respective Classrooms', 1),
('graduation-ceremony-2025','Graduation Ceremony — Class of 2025','The community celebrated the graduating class.','Families, faculty and friends gathered as the Class of 2025 received their diplomas and shared their plans for the future.\n\nPhotographs are available in the gallery.','assets/images/about.jpg', NOW() - INTERVAL 30 DAY, 'Main Auditorium', 1);

INSERT INTO notices (title, category, description, document, notice_date, published) VALUES
('Winter Break — School Closed December 22 to January 5','Holiday','School reopens on Monday, January 6. Buses run on the regular schedule.','', NOW() - INTERVAL 3 DAY, 1),
('Second Semester Examination Schedule Released','Examination','Examinations run February 3–14 for Grades 6–12. Download the detailed schedule from Downloads.','', NOW() - INTERVAL 7 DAY, 1),
('Admissions Open for 2026–27','Admission','Applications for the next academic year are open. Priority deadline: January 31.','', NOW() - INTERVAL 10 DAY, 1),
('Parent Workshop: Digital Wellbeing at Home','Announcement','A practical session with our counsellors on healthy screen habits. RSVP at the front office.','', NOW() - INTERVAL 14 DAY, 1),
('Library Extended Hours During Exams','General','The library will remain open until 6:00 PM on school days from February 1.','', NOW() - INTERVAL 25 DAY, 1);

INSERT INTO gallery_albums (title, category, description, cover_image, published) VALUES
('Around Our Campus','Campus','Classrooms, corridors and everyday moments.','assets/images/hero.jpg',1),
('Celebrations & Ceremonies','Events','Graduations, festivals and milestone moments.','assets/images/about.jpg',1),
('Sports & Games','Sports','From house matches to championship finals.','assets/images/hero.jpg',1),
('Clubs & Activities','Activities','Robotics, computing, art and beyond.','assets/images/about.jpg',1);

INSERT INTO gallery_images (album_id, src, caption, sort_order) VALUES
(1,'assets/images/hero.jpg','A morning mathematics lesson',0),
(1,'assets/images/about.jpg','Study time in the learning commons',1),
(1,'assets/images/hero.jpg','Hands-on biology class',2),
(1,'assets/images/about.jpg','Discussion-based learning in Grade 8',3),
(2,'assets/images/hero.jpg','Class of 2025 celebrations',0),
(2,'assets/images/about.jpg','Graduation day joy',1),
(2,'assets/images/hero.jpg','Senior class circle',2),
(2,'assets/images/about.jpg','Cap toss on the lawn',3),
(3,'assets/images/hero.jpg','Lunchtime soccer',0),
(3,'assets/images/about.jpg','Team huddle before the final',1),
(3,'assets/images/hero.jpg','Dribbling drills',2),
(3,'assets/images/about.jpg','House cup football',3),
(4,'assets/images/hero.jpg','Coding club in the lab',0),
(4,'assets/images/about.jpg','Pair programming practice',1),
(4,'assets/images/hero.jpg','Debate team practice',2),
(4,'assets/images/about.jpg','Community sports day',3);

INSERT INTO downloads (title, category, description, file_path, file_name, published, downloads_count) VALUES
('Admission Form 2026–27','Admission Form','Complete and return to the admissions office, or apply online.','assets/docs/admission-form.pdf','admission-form-2026.pdf',1,42),
('School Prospectus','Prospectus','An introduction to life and learning at our school.','assets/docs/prospectus.pdf','school-prospectus.pdf',1,88),
('Academic Calendar 2025–26','Academic Calendar','Term dates, breaks and key events for the school year.','assets/docs/academic-calendar.pdf','academic-calendar-2025-26.pdf',1,61),
('Examination Schedule — February 2026','Exam Schedule','Dates and timings for second semester examinations.','assets/docs/exam-schedule.pdf','exam-schedule-feb-2026.pdf',1,37);

INSERT INTO admissions (student_name, dob, gender, grade, previous_school, parent_name, relationship, phone, email, address, emergency_contact, additional_info, document, status) VALUES
('Aarav Sharma','2015-04-12','Male','Grade 5','Greenfield Elementary','Rohan Sharma','Father','+1 555 201 8845','rohan.sharma@example.com','22 Maple Street, Greenfield','Nina Sharma — +1 555 201 9911','Aarav plays competitive chess and loves mathematics.','','under_review'),
('Zoe Fernandez','2018-09-02','Female','Grade 1','','Lucia Fernandez','Mother','+1 555 318 2276','lucia.f@example.com','8 Birch Lane, Greenfield','Marcos Fernandez — +1 555 318 3342','Zoe is excited about art and swimming.','','new'),
('Ethan Wright','2012-01-25','Male','Grade 8','Hillside Middle School','Karen Wright','Mother','+1 555 442 9034','karen.wright@example.com','193 Cedar Court, Greenfield','Paul Wright — +1 555 442 1188','Transferring mid-year; strong interest in robotics.','','contacted'),
('Mia Chen','2010-11-30','Female','Grade 10','City International School','David Chen','Father','+1 555 587 6621','d.chen@example.com','75 Ash Avenue, Greenfield','Lena Chen — +1 555 587 7733','Mia is a competitive swimmer and debater.','','accepted');

INSERT INTO contact_messages (name, email, phone, subject, message, is_read) VALUES
('Jennifer Moore','j.moore@example.com','+1 555 771 2200','Campus tour request','Hello, we would love to visit the campus this month with our daughter (Grade 4). Are weekend tours available?',0),
('Samuel Adeniyi','s.adeniyi@example.com','+1 555 662 8810','Bus route information','Could you share the transport routes and fees for the Oakwood neighbourhood? Thank you.',1),
('Priya Nair','priya.nair@example.com','','Scholarship enquiry','Do you offer merit scholarships for middle school? Happy to provide documentation.',0);

-- ============================================================================
--  Done. Default login (until you change it):
--     URL:      /school-php/admin/login.php
--     Email:    admin@example.edu
--     Password: Admin@1234
-- ============================================================================
