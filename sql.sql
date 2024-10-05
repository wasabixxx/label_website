-- Tạo cơ sở dữ liệu
CREATE DATABASE music_website;

USE music_website;

-- Tạo bảng lưu trữ thông tin trang nhạc
CREATE TABLE `music_pages` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `image_path` VARCHAR(255),
    `spotify_link` VARCHAR(255),
    `apple_link` VARCHAR(255),
    `soundcloud_link` VARCHAR(255),
    `youtube_link` VARCHAR(255),
    `instagram_link` VARCHAR(255),
    `slug` VARCHAR(255) UNIQUE NOT NULL
);
