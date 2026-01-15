USE blood_don;

-- Create accepted_donations table
CREATE TABLE IF NOT EXISTS accepted_donations (
    id VARCHAR(36) PRIMARY KEY,
    donor_id VARCHAR(36) NOT NULL,
    request_id VARCHAR(36) NOT NULL,
    date_accepted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES profiles (id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES donation_requests (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Add last_donation_date to profiles table
ALTER TABLE profiles
ADD COLUMN last_donation_date TIMESTAMP NULL AFTER cooldown_end_date;