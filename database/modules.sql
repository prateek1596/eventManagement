USE pateek;

CREATE TABLE IF NOT EXISTS event_roles (
    Role_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    Role_Name VARCHAR(50) NOT NULL,
    Description VARCHAR(255) NULL,
    PRIMARY KEY (Role_ID),
    UNIQUE KEY uq_event_role_name (Role_Name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_permissions (
    Permission_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    Permission_Key VARCHAR(80) NOT NULL,
    Description VARCHAR(255) NULL,
    PRIMARY KEY (Permission_ID),
    UNIQUE KEY uq_event_permission_key (Permission_Key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_role_permissions (
    Role_ID INT UNSIGNED NOT NULL,
    Permission_ID INT UNSIGNED NOT NULL,
    PRIMARY KEY (Role_ID, Permission_ID),
    CONSTRAINT fk_role_permission_role FOREIGN KEY (Role_ID) REFERENCES event_roles(Role_ID) ON DELETE CASCADE,
    CONSTRAINT fk_role_permission_permission FOREIGN KEY (Permission_ID) REFERENCES event_permissions(Permission_ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_messages (
    Message_ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NOT NULL,
    Audience VARCHAR(80) NOT NULL DEFAULT 'All attendees',
    Subject VARCHAR(180) NOT NULL,
    Body TEXT NOT NULL,
    Channel ENUM('Email','SMS','WhatsApp','In-app') NOT NULL DEFAULT 'Email',
    Status ENUM('Draft','Scheduled','Sent','Cancelled') NOT NULL DEFAULT 'Draft',
    Scheduled_At DATETIME NULL,
    Sent_At DATETIME NULL,
    Created_By VARCHAR(100) NOT NULL,
    Created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Message_ID),
    KEY idx_event_message_event (EventID),
    CONSTRAINT fk_event_message_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_surveys (
    Survey_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NOT NULL,
    Survey_Name VARCHAR(160) NOT NULL,
    Is_Active TINYINT(1) NOT NULL DEFAULT 1,
    Created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Survey_ID),
    CONSTRAINT fk_event_survey_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_feedback (
    Feedback_ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NOT NULL,
    Survey_ID INT UNSIGNED NULL,
    Guest_Email VARCHAR(180) NULL,
    Rating TINYINT UNSIGNED NULL,
    Comments TEXT NULL,
    Sentiment ENUM('Positive','Neutral','Negative','Mixed','Unclassified') NOT NULL DEFAULT 'Unclassified',
    Created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Feedback_ID),
    KEY idx_event_feedback_event (EventID),
    CONSTRAINT fk_event_feedback_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE,
    CONSTRAINT fk_event_feedback_survey FOREIGN KEY (Survey_ID) REFERENCES event_surveys(Survey_ID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_ticket_types (
    Ticket_Type_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NOT NULL,
    Ticket_Name VARCHAR(120) NOT NULL,
    Description VARCHAR(255) NULL,
    Price DECIMAL(15,2) NOT NULL DEFAULT 0,
    Quantity INT UNSIGNED NULL,
    Sold_Quantity INT UNSIGNED NOT NULL DEFAULT 0,
    Sale_Start DATETIME NULL,
    Sale_End DATETIME NULL,
    Is_Active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (Ticket_Type_ID),
    CONSTRAINT fk_event_ticket_type_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_inventory (
    Inventory_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    Item_Name VARCHAR(160) NOT NULL,
    Category VARCHAR(100) NULL,
    Quantity INT UNSIGNED NOT NULL DEFAULT 0,
    Available_Quantity INT UNSIGNED NOT NULL DEFAULT 0,
    Condition_Status ENUM('Good','Needs repair','Unavailable') NOT NULL DEFAULT 'Good',
    Notes TEXT NULL,
    PRIMARY KEY (Inventory_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_inventory_assignments (
    Assignment_ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NOT NULL,
    Inventory_ID INT UNSIGNED NOT NULL,
    Quantity INT UNSIGNED NOT NULL DEFAULT 1,
    Return_Date DATE NULL,
    Returned_At DATETIME NULL,
    PRIMARY KEY (Assignment_ID),
    CONSTRAINT fk_inventory_assignment_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE,
    CONSTRAINT fk_inventory_assignment_item FOREIGN KEY (Inventory_ID) REFERENCES event_inventory(Inventory_ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_documents (
    Document_ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NULL,
    Document_Name VARCHAR(180) NOT NULL,
    Document_Type VARCHAR(80) NULL,
    File_Path VARCHAR(255) NOT NULL,
    Uploaded_By VARCHAR(100) NOT NULL,
    Created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Document_ID),
    CONSTRAINT fk_event_document_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_calendar_items (
    Calendar_Item_ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NULL,
    Title VARCHAR(180) NOT NULL,
    Description TEXT NULL,
    Starts_At DATETIME NOT NULL,
    Ends_At DATETIME NULL,
    Assigned_To VARCHAR(100) NULL,
    Created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Calendar_Item_ID),
    CONSTRAINT fk_event_calendar_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_sponsors (
    Sponsor_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NOT NULL,
    Sponsor_Name VARCHAR(160) NOT NULL,
    Package_Name VARCHAR(120) NULL,
    Contract_Value DECIMAL(15,2) NULL,
    Payment_Status ENUM('Proposed','Pending','Paid','Cancelled') NOT NULL DEFAULT 'Proposed',
    Logo_Path VARCHAR(255) NULL,
    Notes TEXT NULL,
    PRIMARY KEY (Sponsor_ID),
    CONSTRAINT fk_event_sponsor_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_speakers (
    Speaker_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NOT NULL,
    Speaker_Name VARCHAR(160) NOT NULL,
    Bio TEXT NULL,
    Organization VARCHAR(160) NULL,
    Photo_Path VARCHAR(255) NULL,
    PRIMARY KEY (Speaker_ID),
    CONSTRAINT fk_event_speaker_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_sessions (
    Session_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NOT NULL,
    Speaker_ID INT UNSIGNED NULL,
    Session_Title VARCHAR(180) NOT NULL,
    Description TEXT NULL,
    Starts_At DATETIME NULL,
    Ends_At DATETIME NULL,
    Capacity INT UNSIGNED NULL,
    PRIMARY KEY (Session_ID),
    CONSTRAINT fk_event_session_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE,
    CONSTRAINT fk_event_session_speaker FOREIGN KEY (Speaker_ID) REFERENCES event_speakers(Speaker_ID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_risks (
    Risk_ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EventID INT NOT NULL,
    Risk_Name VARCHAR(180) NOT NULL,
    Description TEXT NULL,
    Severity ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
    Status ENUM('Open','Mitigated','Accepted','Closed') NOT NULL DEFAULT 'Open',
    Mitigation TEXT NULL,
    Created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Risk_ID),
    CONSTRAINT fk_event_risk_event FOREIGN KEY (EventID) REFERENCES Event(EventID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
