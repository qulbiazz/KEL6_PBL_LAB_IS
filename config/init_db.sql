-- Create database
CREATE DATABASE inlet_db;

-- Connect to the database
\c inlet_db;

-- Create tables

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP
);

-- Carousel banners table
CREATE TABLE IF NOT EXISTS carousel_banners (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    button_text VARCHAR(50),
    button_link VARCHAR(255),
    display_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Card data table
CREATE TABLE IF NOT EXISTS card_data (
    id SERIAL PRIMARY KEY,
    icon VARCHAR(100) NOT NULL,
    value VARCHAR(50) NOT NULL,
    label VARCHAR(100) NOT NULL,
    display_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    full_description TEXT,
    features TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Partners table
CREATE TABLE IF NOT EXISTS partners (
    id SERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    logo VARCHAR(255) NOT NULL,
    website VARCHAR(255),
    display_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Team members table
CREATE TABLE IF NOT EXISTS team_members (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    photo VARCHAR(255) NOT NULL,
    bio TEXT,
    email VARCHAR(100),
    linkedin VARCHAR(255),
    display_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- News table
CREATE TABLE IF NOT EXISTS news (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    author VARCHAR(100),
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Gallery table
CREATE TABLE IF NOT EXISTS gallery (
    id SERIAL PRIMARY KEY,
    image VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Research activities table
CREATE TABLE IF NOT EXISTS research_activities (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    full_description TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Page visits tracking table
CREATE TABLE IF NOT EXISTS page_visits (
    id SERIAL PRIMARY KEY,
    page_url VARCHAR(255) NOT NULL,
    visit_date DATE DEFAULT CURRENT_DATE,
    visit_count INTEGER DEFAULT 1,
    UNIQUE(page_url, visit_date)
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password, email, full_name)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@inlet.com', 'Administrator');

-- Insert sample data

-- Carousel banners
INSERT INTO carousel_banners (title, description, image, button_text, button_link, display_order) VALUES
('Welcome to InLet', 'Information and Learning Engineering Technology Laboratory', 'banner1.jpg', 'Learn More', 'about.php', 1),
('Innovative Research', 'Advancing the future of educational technology', 'banner2.jpg', 'Our Research', '#research', 2),
('Join Our Team', 'Be part of cutting-edge technology development', 'banner3.jpg', 'Contact Us', '#contact', 3);

-- Card data
INSERT INTO card_data (icon, value, label, display_order) VALUES
('fas fa-project-diagram', '50+', 'Projects', 1),
('fas fa-users', '30+', 'Team Members', 2),
('fas fa-file-alt', '100+', 'Publications', 3),
('fas fa-handshake', '20+', 'Partners', 4);

-- Products
INSERT INTO products (name, description, image, full_description) VALUES
('Learning Management System', 'Comprehensive LMS solution for modern education', 'product1.jpg', 'A full-featured learning management system designed for educational institutions.'),
('AI Tutor Assistant', 'Intelligent tutoring system powered by AI', 'product2.jpg', 'AI-powered assistant that provides personalized learning experiences.'),
('Virtual Laboratory', 'Interactive virtual lab for STEM education', 'product3.jpg', 'Conduct experiments in a safe, virtual environment.');

-- Partners
INSERT INTO partners (name, logo, website, display_order) VALUES
('University Partner', 'partner1.png', 'https://example.com', 1),
('Tech Company', 'partner2.png', 'https://example.com', 2),
('Research Institute', 'partner3.png', 'https://example.com', 3);

-- Team members
INSERT INTO team_members (name, position, photo, email, display_order) VALUES
('Dr. John Doe', 'Laboratory Director', 'team1.jpg', 'john@inlet.com', 1),
('Jane Smith', 'Senior Researcher', 'team2.jpg', 'jane@inlet.com', 2),
('Bob Johnson', 'Software Engineer', 'team3.jpg', 'bob@inlet.com', 3);

-- News
INSERT INTO news (title, content, image, author) VALUES
('New Research Project Launched', 'We are excited to announce our latest research project in AI education...', 'news1.jpg', 'Admin'),
('Partnership with Leading University', 'InLet has formed a strategic partnership with a leading university...', 'news2.jpg', 'Admin'),
('Product Launch: AI Tutor', 'Our new AI Tutor Assistant is now available for beta testing...', 'news3.jpg', 'Admin');

-- Gallery
INSERT INTO gallery (image, caption) VALUES
('gallery1.jpg', 'Team meeting'),
('gallery2.jpg', 'Research presentation'),
('gallery3.jpg', 'Laboratory workspace');

-- Research activities
INSERT INTO research_activities (title, description, image) VALUES
('AI in Education', 'Exploring the use of artificial intelligence in personalized learning', 'research1.jpg'),
('Virtual Reality Learning', 'Developing immersive VR experiences for education', 'research2.jpg'),
('Data Analytics', 'Using big data to improve learning outcomes', 'research3.jpg');
