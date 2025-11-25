-- Create about_settings table
CREATE TABLE IF NOT EXISTS about_settings (
    id SERIAL PRIMARY KEY,
    hero_title VARCHAR(255),
    hero_subtitle VARCHAR(255),
    vision_text TEXT,
    mission_items JSONB,
    who_we_are_text TEXT,
    quote_text TEXT,
    focus_areas JSONB,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert initial data
INSERT INTO about_settings (hero_title, hero_subtitle, vision_text, mission_items, who_we_are_text, quote_text, focus_areas) 
VALUES (
    'About InLet',
    'Information and Learning Engineering Technology',
    'To become a leading research and development center in information and learning engineering technology, driving innovation and excellence in education technology solutions that transform the way people learn and interact with information.',
    '["Develop innovative learning technologies and information systems", "Conduct cutting-edge research in educational technology", "Foster collaboration with industry and academic partners", "Create impactful solutions for modern learning challenges"]',
    'InLet (Information and Learning Engineering Technology) is a dynamic research laboratory dedicated to advancing the field of educational technology and information systems. Founded with the vision of transforming learning experiences through technology, we bring together researchers, developers, and educators to create innovative solutions.

Our team consists of experienced professionals with diverse backgrounds in computer science, education, and engineering. We work on various projects ranging from learning management systems to artificial intelligence applications in education.',
    'Through our research activities, product development, and industry partnerships, we aim to make a significant impact on how technology is used in education and information management.',
    '[
        {"title": "Educational Technology", "description": "Developing innovative learning platforms and tools", "icon": "fas fa-graduation-cap", "color": "blue"},
        {"title": "Artificial Intelligence", "description": "AI applications in education and information systems", "icon": "fas fa-robot", "color": "green"},
        {"title": "Information Systems", "description": "Building robust and scalable information management solutions", "icon": "fas fa-database", "color": "purple"},
        {"title": "Mobile Applications", "description": "Creating mobile-first learning experiences", "icon": "fas fa-mobile-alt", "color": "orange"}
    ]'
)
ON CONFLICT DO NOTHING;
