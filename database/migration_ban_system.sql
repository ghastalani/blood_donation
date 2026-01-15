-- Migration: Add is_banned to users table
ALTER TABLE users ADD COLUMN is_banned TINYINT(1) DEFAULT 0;