CREATE TABLE homepage_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homepage_id INT NOT NULL,
    title VARCHAR(255),
    spotify_link VARCHAR(255),
    apple_link VARCHAR(255),
    soundcloud_link VARCHAR(255),
    youtube_link VARCHAR(255),
    instagram_link VARCHAR(255),
    image VARCHAR(255),
    version_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
