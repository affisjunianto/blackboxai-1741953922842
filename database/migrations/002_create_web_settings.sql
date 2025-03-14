CREATE TABLE IF NOT EXISTS web_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO web_settings (setting_key, setting_value, setting_group) VALUES
-- General Settings
('site_name', 'Ampibet', 'general'),
('site_description', 'A comprehensive platform for managing agents and sub-agents', 'general'),
('site_keywords', 'betting, agents, management, balance system', 'general'),
('company_address', '', 'general'),
('company_phone', '', 'general'),
('company_email', '', 'general'),

-- SEO Settings
('meta_title', 'Ampibet - Agent Management System', 'seo'),
('meta_description', 'Comprehensive platform for managing agents and sub-agents with integrated balance system', 'seo'),
('meta_keywords', 'betting, agents, management, balance system', 'seo'),
('google_analytics_id', '', 'seo'),

-- Social Media Settings
('facebook_url', '', 'social'),
('twitter_url', '', 'social'),
('instagram_url', '', 'social'),
('linkedin_url', '', 'social'),

-- Logo Settings
('site_logo', '', 'appearance'),
('site_favicon', '', 'appearance'),
('login_background', '', 'appearance'),

-- Footer Settings
('footer_text', '© 2024 Ampibet. All rights reserved.', 'footer'),
('footer_links', '', 'footer');