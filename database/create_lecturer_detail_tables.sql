-- Create tables for detailed lecturer profile

-- Table for work in progress/projects
CREATE TABLE IF NOT EXISTS lecturer_projects (
    id SERIAL PRIMARY KEY,
    team_member_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    start_date DATE,
    end_date DATE,
    status VARCHAR(50) DEFAULT 'ongoing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE
);

-- Table for education history
CREATE TABLE IF NOT EXISTS lecturer_education (
    id SERIAL PRIMARY KEY,
    team_member_id INTEGER NOT NULL,
    institution VARCHAR(255) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    field_of_study VARCHAR(255),
    start_date DATE,
    end_date DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE
);

-- Table for skills
CREATE TABLE IF NOT EXISTS lecturer_skills (
    id SERIAL PRIMARY KEY,
    team_member_id INTEGER NOT NULL,
    category VARCHAR(100) NOT NULL, -- 'Programming', 'Workflow', etc.
    skill_name VARCHAR(100) NOT NULL,
    skill_icon VARCHAR(255), -- Font Awesome icon class or image path
    proficiency INTEGER DEFAULT 3, -- 1-5 scale
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE
);

-- Table for interests
CREATE TABLE IF NOT EXISTS lecturer_interests (
    id SERIAL PRIMARY KEY,
    team_member_id INTEGER NOT NULL,
    interest_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE
);

-- Table for published articles/publications
CREATE TABLE IF NOT EXISTS lecturer_publications (
    id SERIAL PRIMARY KEY,
    team_member_id INTEGER NOT NULL,
    title VARCHAR(500) NOT NULL,
    year INTEGER,
    url VARCHAR(500),
    type VARCHAR(50) DEFAULT 'article', -- article, conference, journal, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE
);

-- Add additional fields to team_members table if needed
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS address TEXT;
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS phone VARCHAR(50);
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS email VARCHAR(255);
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS bio TEXT;
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS linkedin_url VARCHAR(255);
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS google_scholar_url VARCHAR(255);
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS researchgate_url VARCHAR(255);
ALTER TABLE team_members ADD COLUMN IF NOT EXISTS orcid_url VARCHAR(255);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_lecturer_projects_member ON lecturer_projects(team_member_id);
CREATE INDEX IF NOT EXISTS idx_lecturer_education_member ON lecturer_education(team_member_id);
CREATE INDEX IF NOT EXISTS idx_lecturer_skills_member ON lecturer_skills(team_member_id);
CREATE INDEX IF NOT EXISTS idx_lecturer_interests_member ON lecturer_interests(team_member_id);
CREATE INDEX IF NOT EXISTS idx_lecturer_publications_member ON lecturer_publications(team_member_id);
