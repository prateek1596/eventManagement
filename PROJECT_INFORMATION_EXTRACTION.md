# Project Information Extraction Document

## Verification scope and evidence policy

This document is an extraction of the current repository, not a newly designed
system and not the final academic report. Facts were taken from the PHP, SQL,
JavaScript, TypeScript, Markdown, SVG, and repository metadata present in the
workspace. The reference material available in the repository is
`EventSpace_IEEE_Paper.docx`/`.pdf`; its title and academic framing were used
only to align the organization requested by the user.

The following labels are used throughout:

- **Implemented**: executable code exists in the repository.
- **Database foundation**: SQL creates the table, but a complete user-facing
  workflow is not evidenced.
- **Referenced legacy dependency**: application code uses the object, but its
  creation DDL is not present in the checked-in SQL files.
- **Proposed**: useful future work, not claimed as current functionality.
- **Not implemented/evidenced**: no corresponding implementation was found.

Repository: `https://github.com/prateek1596/eventManagement`  
Local database configured by the example environment: `pateek`  
Application title used in source and documentation: **EventSpace**  
Project type: **full-stack server-rendered web application**.

---

# 1. Title Page

**EventSpace: An AI-Assisted Web Application for Natural-Language Event
Management**

The repository reference paper uses the same title and identifies the work as
a 2026 project by “Prateek [Surname]”. The full author name, institution,
department, guide, academic programme, and submission date are not present in
the application source and must be completed from institutional records.

# 2. Bonafide Certificate

No certificate template, guide name, institution name, or registration details
were found in the repository. This page must be supplied by the institution;
do not infer these values from the code.

# 3. Abstract

EventSpace is a PHP/MySQL event-management platform for organizers, staff,
attendees, and public visitors. It centralizes event records, attendees,
venues, organizers, RSVPs, tickets, tasks, notifications, analytics, public
event pages, feedback, and event photographs. The application adds optional
natural-language assistance: an event brief can be converted into form fields,
an event-planning prompt can produce a summary and checklist, and a catalog
query can be converted into keyword, location, date, and budget filters.

The interface is rendered by PHP pages and enhanced with HTML5, Bootstrap 5.3,
CSS themes, vanilla JavaScript, and Chart.js. MySQL/MariaDB is accessed through
the current MySQLi page layer; a PDO helper also exists for service-layer
access. Authentication is session-based, with password hashing, role checks,
CSRF tokens, prepared statements in the newer workflows, output escaping, and
validated image uploads. A transaction with row locking protects the capacity
check and RSVP insertion. The checked-in SQL also contains a larger foundation
for vendors, expenses, feedback, surveys, inventory, documents, sponsors,
speakers, sessions, risks, permissions, and communication workflows; these
tables must not be presented as complete UI modules unless separately
implemented.

# 4. Problem Statement

Manual event administration scatters schedules, venue information, contacts,
attendee responses, ticket capacity, operational tasks, and feedback across
spreadsheets, messages, and disconnected tools. This makes duplicate data
entry, capacity mistakes, poor follow-up, and limited operational visibility
more likely. Users also have to translate natural event descriptions into many
structured form fields.

EventSpace addresses this by providing one browser-based workspace with event
CRUD, attendee and venue records, RSVP and ticket workflows, check-in,
analytics, notifications, exports, feedback, and optional AI assistance.

---

# TABLE OF CONTENTS

1. Title Page  
2. Bonafide Certificate  
3. Abstract  
4. Problem Statement  
5. Table of Contents  
6. Chapter 1 – Introduction  
7. Chapter 2 – Relational Model  
8. Chapter 3 – SQL Implementation  
9. Chapter 4 – Pitfalls, Dependencies and Normalization  
10. Chapter 5 – Concurrency Control and Recovery  
11. Chapter 6 – Project Source Code  
12. Chapter 7 – Results and Discussion  
13. Chapter 8 – Conclusion  
14. Chapter 9 – NPTEL Hallticket and Progress  
15. Chapter 10 – GitHub Repository

---

# CHAPTER 1 – INTRODUCTION

## 1.1 Problem Understanding

### 1.1.1 Objective

The verified objectives are to:

1. Create and maintain events with date, time, venue, contact, status, budget,
   capacity, recurrence, and optional image data.
2. Centralize attendee, organizer, venue, RSVP, ticket, task, notification,
   and feedback operations.
3. Support public event discovery and RSVP registration.
4. Prevent overbooking through capacity checks, waitlisting, row locking, and
   an atomic RSVP write.
5. Provide dashboard totals, upcoming-event views, revenue information,
   charts, attendance signals, and sentiment summaries.
6. Reduce data-entry effort using optional AI event extraction and planning.
7. Maintain usable local fallbacks when no external AI key is configured.
8. Provide a database foundation for additional event-management modules.

### 1.1.2 User Requirements

| User/role | Verified requirements |
|---|---|
| Administrator | Access restricted administrative/event operations; role is supported in authorization checks. |
| Organizer | Create, edit, delete, and inspect events; manage tasks, tickets, RSVPs, and operational data. |
| Staff | Support check-in, coordination, and feedback/operational views. |
| Attendee | Sign in, discover events, RSVP, receive a token/QR representation, and submit feedback. |
| Public visitor | Browse the catalog and open an event RSVP page; the repository also contains public-facing event presentation. |

### 1.1.3 Challenges

- Preserving existing root-level PHP URLs while grouping shared code.
- Coordinating a legacy MySQLi page layer with a reusable PDO helper.
- Enforcing authorization and CSRF verification across form submissions.
- Validating uploaded files by actual MIME type, size, and generated filename.
- Handling event capacity and concurrent RSVP requests safely.
- Converting unstructured prompts to bounded, validated JSON.
- Providing meaningful behavior when the AI provider or key is unavailable.
- Maintaining compatibility with legacy tables (`Event`, `Attendee`, `Venue`,
  and `Organiser`) whose creation DDL is not in the current repository.

## 1.2 Identification of Entities and Relationships

### 1.2.1 Entities

The SQL files define or extend the following entities:

| Entity/table | Status | Purpose |
|---|---|---|
| `users` | Implemented | Login identity, profile, role, and contact data. |
| `Event` | Referenced legacy dependency + extended | Core event record. Base DDL is absent; `upgrade.sql` adds capacity, recurrence, and image fields. |
| `Attendee` | Referenced legacy dependency | Legacy attendee records used by dashboard and attendee pages; DDL absent. |
| `Venue` | Referenced legacy dependency | Legacy venue records; DDL absent. |
| `Organiser` | Referenced legacy dependency | Legacy organizer records; DDL absent. |
| `event_rsvps` | Implemented schema | Guest RSVP, status, token, check-in, ticket, and payment state. |
| `event_tickets` | Implemented schema | Event ticket types represented by name, price, capacity, and timestamps. |
| `event_tasks` | Implemented schema | Event task board. |
| `event_vendors` | Database foundation | Event vendor contacts and contract state. |
| `event_expenses` | Database foundation | Estimated/actual event expenses and vendor association. |
| `event_seating_tables` | Database foundation | Named seating tables and capacities. |
| `event_seating_assignments` | Database foundation | RSVP-to-table assignment; one assignment per RSVP is enforced. |
| `event_templates` | Database foundation | Reusable event defaults. |
| `event_public_pages` | Database foundation | Tokenized publication metadata for events. |
| `event_reviews` | Database foundation | Guest ratings and comments. |
| `event_notifications` | Implemented schema/partial workflow | User notifications linked optionally to events. |
| `event_audit_log` | Database foundation | Generic audit records. No complete writer workflow is evidenced. |
| `event_reminders` | Implemented schema/partial workflow | User/event reminder scheduling state. |
| `event_occurrences` | Implemented schema/partial workflow | Recurring event date/time occurrences. |
| `event_roles` | Database foundation | Role catalog. |
| `event_permissions` | Database foundation | Permission catalog. |
| `event_role_permissions` | Database foundation | Many-to-many role/permission bridge. |
| `event_messages` | Database foundation | Event communications. |
| `event_surveys` | Database foundation | Event survey definitions. |
| `event_feedback` | Implemented | Rating, comments, and locally classified sentiment. |
| `event_ticket_types` | Database foundation | More detailed ticket-type model. |
| `event_inventory` | Database foundation | Inventory items. |
| `event_inventory_assignments` | Database foundation | Event-to-inventory allocation. |
| `event_documents` | Database foundation | Event document metadata and paths. |
| `event_calendar_items` | Database foundation | Event calendar entries. |
| `event_sponsors` | Database foundation | Sponsor packages and payment state. |
| `event_speakers` | Database foundation | Speakers associated with events. |
| `event_sessions` | Database foundation | Sessions associated with events and optional speakers. |
| `event_risks` | Database foundation | Event risk register. |

### 1.2.2 Relationships

- `Event` has one-to-many relationships with RSVPs, tickets, tasks, vendors,
  expenses, seating tables, public pages, reviews, notifications, reminders,
  occurrences, messages, surveys, feedback, ticket types, documents, calendar
  items, sponsors, speakers, sessions, and risks.
- `event_expenses` optionally references `event_vendors`; vendor deletion
  sets `Vendor_ID` to `NULL`.
- `event_sessions` optionally references `event_speakers`; speaker deletion
  sets `Speaker_ID` to `NULL`.
- `event_feedback` optionally references `event_surveys`; survey deletion sets
  `Survey_ID` to `NULL`.
- `event_inventory_assignments` bridges events and inventory items, creating a
  many-to-many business relationship.
- `event_role_permissions` bridges roles and permissions, creating a
  many-to-many relationship.
- `event_seating_assignments` bridges RSVPs and seating tables. Its unique
  `RSVP_ID` key limits one seating assignment per RSVP.
- `event_public_pages` has a unique `EventID`, so the schema permits at most
  one public-page row per event (one-to-zero-or-one).
- No verified one-to-one relationship with `users` is defined by foreign key;
  several legacy/foundation tables store usernames as text.

## 1.3 Construction of Database Using ER Model

The ER model should place `Event` at the center, with child entities for
attendance, operations, finance, communication, content, and risk. The
`users` entity supplies authenticated identity, while the roles/permissions
submodel is separate from the current `users.role` text field.

**Recommended ER diagram specification (faithful to current SQL):**

```text
users (id PK)
  └── textual username references in event_notifications, event_reminders,
      event_audit_log, event_templates, and event_messages.created_by

Event (EventID PK, legacy DDL not present)
  ├── event_rsvps (EventID FK, Ticket_ID FK nullable)
  ├── event_tickets (EventID FK)
  ├── event_tasks (EventID FK)
  ├── event_vendors (EventID FK)
  │     └── event_expenses (Vendor_ID FK nullable)
  ├── event_seating_tables (EventID FK)
  │     └── event_seating_assignments (Table_ID FK, RSVP_ID FK)
  ├── event_public_pages (EventID FK, unique)
  ├── event_feedback (EventID FK, Survey_ID FK nullable)
  │     └── event_surveys (EventID FK)
  ├── event_speakers (EventID FK)
  │     └── event_sessions (EventID FK, Speaker_ID FK nullable)
  ├── event_inventory_assignments (EventID FK, Inventory_ID FK)
  └── event_messages/documents/calendar/sponsors/risks/
```

The report should draw dashed or differently colored edges for textual
username associations because they are not foreign-key constraints in the
checked-in SQL.

---

# CHAPTER 2 – RELATIONAL MODEL

## 2.1 Design of Relational Schema

The database name is `pateek`, using `utf8mb4` and InnoDB. The complete
attribute inventory below is extracted from `schema.sql`, `upgrade.sql`, and
`modules.sql`. `PK` and `FK` identify keys; `NULL` identifies nullable
attributes.

### Base and legacy feature relations

```text
users(
  id PK, username UNIQUE, password, email UNIQUE, name, phone, role, created_at
)

Event(
  EventID PK [base definition not checked in],
  Name, Date_OF_Booking, Time, Venue, Contact_info, Status, Budget,
  Capacity NULL, Recurrence_Rule, Recurrence_End_Date NULL, Image_Path NULL
)

Attendee(AttendeeID, Name, Email, Phone_No, RSVP) [DDL not checked in]
Venue(...) [DDL not checked in]
Organiser(...) [DDL not checked in]
```

### Upgrade relations

```text
event_rsvps(
 RSVP_ID PK, EventID FK, Guest_Name, Guest_Email, Guest_Phone NULL,
 RSVP_Token UNIQUE, RSVP_Status, Checked_In_At NULL, Created_At,
 Ticket_ID FK NULL, Payment_Status
)
event_tasks(Task_ID PK, EventID FK, Title, Details NULL, Assigned_To NULL,
 Due_Date NULL, Priority, Task_Status, Created_By, Created_At)
event_vendors(Vendor_ID PK, EventID FK, Vendor_Name, Service_Type,
 Contact_Name NULL, Contact_Email NULL, Contact_Phone NULL, Contract_Status,
 Notes NULL, Created_At)
event_expenses(Expense_ID PK, EventID FK, Vendor_ID FK NULL, Description,
 Estimated_Amount, Actual_Amount NULL, Payment_Status, Due_Date NULL,
 Receipt_Path NULL, Created_At)
event_seating_tables(Table_ID PK, EventID FK, Table_Name, Capacity)
event_seating_assignments(Assignment_ID PK, Table_ID FK, RSVP_ID FK UNIQUE,
 Seat_Number NULL)
event_templates(Template_ID PK, Template_Name, Description NULL,
 Default_Status NULL, Default_Budget NULL, Created_By, Created_At)
event_public_pages(Public_Page_ID PK, EventID FK UNIQUE, Public_Token UNIQUE,
 Description NULL, Is_Published, Created_At)
event_reviews(Review_ID PK, EventID FK, Guest_Email NULL, Rating, Comments NULL,
 Created_At)
event_notifications(Notification_ID PK, Username, EventID FK NULL,
 Notification_Type, Message, Is_Read, Created_At)
event_audit_log(Audit_ID PK, Username, Action_Name, Entity_Name,
 Entity_ID NULL, Details NULL, Created_At)
event_tickets(Ticket_ID PK, EventID FK, Ticket_Name, Price, Capacity NULL,
 Created_At)
event_reminders(Reminder_ID PK, Username, EventID FK, Remind_At, Is_Sent,
 Created_At, UNIQUE(Username, EventID))
event_occurrences(Occurrence_ID PK, EventID FK, Occurrence_Date,
 Occurrence_Time, Status, Created_At, UNIQUE(EventID, Occurrence_Date,
 Occurrence_Time))
```

### Module foundation relations

```text
event_roles(Role_ID PK, Role_Name UNIQUE, Description NULL)
event_permissions(Permission_ID PK, Permission_Key UNIQUE, Description NULL)
event_role_permissions(Role_ID PK/FK, Permission_ID PK/FK)
event_messages(Message_ID PK, EventID FK, Audience, Subject, Body, Channel,
 Status, Scheduled_At NULL, Sent_At NULL, Created_By, Created_At)
event_surveys(Survey_ID PK, EventID FK, Survey_Name, Is_Active, Created_At)
event_feedback(Feedback_ID PK, EventID FK, Survey_ID FK NULL, Guest_Email NULL,
 Rating NULL, Comments NULL, Sentiment, Created_At)
event_ticket_types(Ticket_Type_ID PK, EventID FK, Ticket_Name,
 Description NULL, Price, Quantity NULL, Sold_Quantity, Sale_Start NULL,
 Sale_End NULL, Is_Active)
event_inventory(Inventory_ID PK, Item_Name, Category NULL, Quantity,
 Available_Quantity, Condition_Status, Notes NULL)
event_inventory_assignments(Assignment_ID PK, EventID FK, Inventory_ID FK,
 Quantity, Return_Date NULL, Returned_At NULL)
event_documents(Document_ID PK, EventID FK NULL, Document_Name,
 Document_Type NULL, File_Path, Uploaded_By, Created_At)
event_calendar_items(Calendar_Item_ID PK, EventID FK NULL, Title,
 Description NULL, Starts_At, Ends_At NULL, Assigned_To NULL, Created_At)
event_sponsors(Sponsor_ID PK, EventID FK, Sponsor_Name, Package_Name NULL,
 Contract_Value NULL, Payment_Status, Logo_Path NULL, Notes NULL)
event_speakers(Speaker_ID PK, EventID FK, Speaker_Name, Bio NULL,
 Organization NULL, Photo_Path NULL)
event_sessions(Session_ID PK, EventID FK, Speaker_ID FK NULL, Session_Title,
 Description NULL, Starts_At NULL, Ends_At NULL, Capacity NULL)
event_risks(Risk_ID PK, EventID FK, Risk_Name, Description NULL, Severity,
 Status, Mitigation NULL, Created_At)
```

## 2.2 Database Structure

- `schema.sql` creates database `pateek` and `users`.
- `upgrade.sql` alters the legacy `Event` relation and creates operational
  tables.
- `modules.sql` creates idempotent module-foundation tables.
- Cascading deletes are used for child data owned by an event.
- `SET NULL` is used for optional vendor, speaker, survey, and ticket links.
- Enumerated columns constrain status, role-like states, channel, severity,
  recurrence, and payment values.
- Unique constraints protect usernames, emails, RSVP tokens, public tokens,
  one public page per event, reminders per user/event, seating assignments per
  RSVP, and occurrence duplicates.

The checked-in SQL does not define the base `Event`, `Attendee`, `Venue`, or
`Organiser` tables. A running database created from an earlier version is
therefore a prerequisite for all pages that reference those relations.

---

# CHAPTER 3 – SQL IMPLEMENTATION

## 3.1 Table Creation Queries

The complete executable DDL is in:

- [`database/schema.sql`](database/schema.sql): database and `users`.
- [`database/upgrade.sql`](database/upgrade.sql): `ALTER TABLE Event`,
  `event_rsvps`, `event_tasks`, `event_vendors`, `event_expenses`,
  `event_seating_tables`, `event_seating_assignments`, `event_templates`,
  `event_public_pages`, `event_reviews`, `event_notifications`,
  `event_audit_log`, `event_tickets`, `event_reminders`, and
  `event_occurrences`.
- [`database/modules.sql`](database/modules.sql): roles, permissions,
  messages, surveys, feedback, ticket types, inventory, documents, calendar,
  sponsors, speakers, sessions, and risks.

Because these files are the authoritative complete SQL source, the academic
report should reproduce their exact DDL in an appendix rather than rewrite it
from memory. The following are the exact base statements:

```sql
CREATE DATABASE IF NOT EXISTS pateek
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pateek;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    role VARCHAR(30) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

`upgrade.sql` uses `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` for
`Event.Capacity`, `Event.Recurrence_Rule`, `Event.Recurrence_End_Date`, and
`Event.Image_Path`, and uses `CREATE TABLE IF NOT EXISTS` for its new
relations. It later adds `event_rsvps.Ticket_ID`, `Payment_Status`, an index,
and its foreign key. `modules.sql` is idempotent and uses explicit foreign
keys for all declared relationships.

## 3.2 Complex Queries

The following are actual application queries, grouped by behavior. They are
not proposed examples.

### Authentication and profile

```sql
SELECT username, password, role
FROM users
WHERE username = ? AND LOWER(role) = ?
LIMIT 1;

SELECT id FROM users
WHERE username = ? OR email = ?
LIMIT 1;

INSERT INTO users (username, password, email, name, phone, role)
VALUES (?, ?, ?, ?, ?, ?);

SELECT * FROM Users WHERE username = ?;

UPDATE Users SET name = ?, email = ?, role = ?, phone = ?
WHERE username = ?;
```

The first query verifies the selected role; passwords are checked with
`password_verify`, not compared as plaintext. Registration checks uniqueness
before insertion and stores `password_hash` output.

### Event creation and maintenance

```sql
INSERT INTO Event
 (`EventID`, `Name`, `Date_OF_Booking`, `Time`, `Venue`, `Contact_info`,
  `Status`, `Budget`, `Capacity`, `Recurrence_Rule`,
  `Recurrence_End_Date`, `Image_Path`)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?, NULLIF(?, ''), ?);
```

Event edit, delete, attendee, venue, organizer, notification, calendar,
recurring, ticket, and export pages contain additional parameterized CRUD
statements. Their exact current source is the corresponding page under
[`pages/`](pages/); no additional SQL view or stored routine is present.

### Filtering, ordering, grouping, and aggregates

```sql
SELECT EventID, Name, Date_OF_Booking, Time, Venue, Contact_info, Image_Path
FROM Event
WHERE Date_OF_Booking >= CURDATE()
  /* optional prepared predicates for name/venue/contact, location,
     budget, from-date, and to-date */
ORDER BY Date_OF_Booking, Time;

SELECT RSVP_Status, COUNT(*) AS total
FROM event_rsvps
GROUP BY RSVP_Status;

SELECT DATE_FORMAT(Date_OF_Booking, '%b') AS month_label, COUNT(*) AS total
FROM Event
WHERE Date_OF_Booking >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY YEAR(Date_OF_Booking), MONTH(Date_OF_Booking)
ORDER BY YEAR(Date_OF_Booking), MONTH(Date_OF_Booking);

SELECT COALESCE(SUM(t.Price), 0) AS revenue
FROM event_tickets t
INNER JOIN event_rsvps r ON r.Ticket_ID = t.Ticket_ID
WHERE r.Payment_Status = 'Paid';
```

### Joins and nested queries

```sql
SELECT e.Venue, COUNT(*) AS total
FROM event_rsvps r
INNER JOIN Event e ON e.EventID = r.EventID
WHERE r.Guest_Email = ? AND r.RSVP_Status = 'Confirmed'
GROUP BY e.Venue
ORDER BY total DESC
LIMIT 3;

SELECT e.EventID, e.Name, e.Date_OF_Booking, e.Time, e.Venue, e.Budget,
       e.Capacity,
       COUNT(r.RSVP_ID) AS registrations,
       SUM(CASE WHEN r.RSVP_Status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed
FROM Event e
LEFT JOIN event_rsvps r ON r.EventID = e.EventID
WHERE e.Date_OF_Booking >= CURDATE()
GROUP BY e.EventID, e.Name, e.Date_OF_Booking, e.Time, e.Venue, e.Budget,
         e.Capacity
ORDER BY confirmed DESC, e.Date_OF_Booking ASC
LIMIT 6;

SELECT AVG(attendance_count) AS average_attendance
FROM (
    SELECT EventID, COUNT(*) AS attendance_count
    FROM event_rsvps
    WHERE RSVP_Status = 'Confirmed'
    GROUP BY EventID
) attendance;

SELECT Ticket_ID, Ticket_Name, Price, Capacity,
       (SELECT COUNT(*) FROM event_rsvps r
        WHERE r.Ticket_ID = event_tickets.Ticket_ID) AS Sold
FROM event_tickets
WHERE EventID = ?
ORDER BY Price, Ticket_Name;
```

### RSVP capacity and waitlist queries

```sql
SELECT Capacity FROM Event WHERE EventID = ? FOR UPDATE;

SELECT COUNT(*) AS booked
FROM event_rsvps
WHERE EventID = ?
  AND RSVP_Status IN ('Pending', 'Confirmed');

INSERT INTO event_rsvps
 (EventID, Guest_Name, Guest_Email, Guest_Phone, RSVP_Token,
  RSVP_Status, Payment_Status, Ticket_ID)
VALUES (?, ?, ?, ?, ?, ?, ?, ?);
```

When no ticket is selected, the implemented alternative omits
`Payment_Status` and `Ticket_ID`. A declined RSVP can promote the oldest
waitlisted guest:

```sql
UPDATE event_rsvps
SET RSVP_Status = 'Confirmed'
WHERE EventID = ? AND RSVP_Status = 'Waitlisted'
ORDER BY Created_At ASC
LIMIT 1;
```

### Feedback and sentiment

```sql
SELECT EventID, Name, Date_OF_Booking, Venue
FROM Event
ORDER BY Date_OF_Booking DESC, Name ASC;

INSERT INTO event_feedback
 (EventID, Guest_Email, Rating, Comments, Sentiment)
VALUES (?, ?, ?, ?, ?);

SELECT Sentiment, COUNT(*) AS total
FROM event_feedback
GROUP BY Sentiment
ORDER BY total DESC;

SELECT f.Rating, f.Comments, f.Sentiment, f.Created_At,
       e.Name AS Event_Name
FROM event_feedback f
INNER JOIN Event e ON e.EventID = f.EventID
ORDER BY f.Created_At DESC
LIMIT 10;
```

The sentiment function is local keyword scoring plus rating: positive and
negative word lists are counted; ratings 4–5 add positive weight and ratings
1–2 add negative weight; ties produce `Neutral`, mixed scores produce
`Mixed`.

## 3.3 Joins and Views

The joins listed above are implemented in page/API queries. No `CREATE VIEW`
statement or view definition was found in the repository. Therefore the
report must state **views: not implemented/evidenced**, rather than presenting
dashboard queries as views.

## 3.4 Triggers

No database trigger is defined in SQL and no trigger-dependent behavior was
found. Notification, audit, waitlist promotion, and sentiment behavior are
performed by PHP code or remain foundation-only.

## 3.5 Stored Procedures

No stored procedure is defined or called.

## 3.6 Functions and Cursors

No stored database function or cursor is defined. PHP functions include
`eventspace_database`, `store_event_image`, `classify_feedback_sentiment`,
`eventspace_ai_completion`, session/CSRF helpers, and rendering helpers.

---

# CHAPTER 4 – PITFALLS, DEPENDENCIES AND NORMALIZATION

## 4.1 Pitfalls

1. **Missing base DDL**: `Event`, `Attendee`, `Venue`, and `Organiser` are
   required by the application but are not created by the checked-in SQL.
2. **Two database abstractions**: current pages use MySQLi (`db.php`) while
   `config/database.php` provides PDO. This creates maintenance and behavior
   consistency risk.
3. **Textual identity references**: several foundation tables store `Username`
   or `Created_By` as text rather than foreign keys to `users`.
4. **Dual role models**: `users.role` is text, while `event_roles` and
   `event_role_permissions` provide a separate role catalog not wired into
   current authorization.
5. **Foundation versus UI confusion**: table existence does not prove a
   complete module implementation.
6. **Legacy naming inconsistency**: `users` and `Users`, `EventID` and
   `event_id`, and mixed casing are used across source.
7. **No migration transaction**: DDL scripts are idempotent in intent but do
   not provide an application-level migration transaction.
8. **External AI dependency**: provider availability, key configuration,
   timeout, response shape, and privacy must be handled.
9. **External image/QR dependencies**: fallback Unsplash images and QR
   generation are remote resources.
10. **Manual event IDs**: the create-event page accepts `EventID`, unlike most
    upgrade tables that auto-increment IDs.

## 4.2 Functional Dependencies

The following dependencies are supported by declared keys:

- `users.id -> username, password, email, name, phone, role, created_at`.
- `users.username -> id, password, email, name, phone, role, created_at`
  because username is unique.
- `users.email -> id, username, password, name, phone, role, created_at`
  because email is unique.
- Each child primary key determines all attributes in its relation.
- `event_role_permissions(Role_ID, Permission_ID)` has a composite key and no
  non-key attributes.
- `event_public_pages.EventID -> Public_Token, Description, Is_Published,
  Created_At` because `EventID` is unique.
- `event_reminders(Username, EventID)` determines reminder attributes because
  that pair is unique.
- `event_occurrences(EventID, Occurrence_Date, Occurrence_Time)` determines
  occurrence attributes because that tuple is unique.

For `Event`, the complete dependency list cannot be proven from this
repository because the base table definition is absent.

## 4.3 Normalization (1NF to 5NF)

### First Normal Form

Most checked-in relations are designed for 1NF: columns are scalar, rows have
primary keys, and enumerated states are single values. Potential exceptions
are semantic rather than syntactic: `Contact_info` in the legacy `Event`
model may contain comma-separated contacts, and free-text fields such as
`Facilities` in the legacy venue model cannot be verified without its DDL.

### Second Normal Form

Relations with single-column primary keys satisfy 2NF structurally because
there can be no partial dependency on part of a key. The bridge table
`event_role_permissions` has only a composite primary key and no non-key
attributes, so it has no partial dependency. The seating and inventory bridge
tables likewise keep relationship facts separate.

### Third Normal Form

The declared foundation tables generally separate entities and relationship
facts, supporting 3NF under the documented keys. Potential transitive
dependencies remain in textual fields: `event_expenses` stores `Vendor_ID` and
also event expense attributes; this is acceptable because vendor details are
in `event_vendors`, but textual `Created_By`, `Username`, and `Assigned_To`
are not normalized user references. `users.role` may duplicate the role
catalog concept. These are identified issues, not proof that every row
violates 3NF.

### BCNF

The relations with explicitly declared unique determinants (`users`,
`event_public_pages`, `event_reminders`, `event_occurrences`, and role/
permission bridges) are compatible with BCNF under the available
dependencies. BCNF for legacy `Event`, `Attendee`, `Venue`, and `Organiser`
cannot be certified without their definitions and candidate keys. Textual
username fields also need a business decision before claiming BCNF for the
whole model.

### Fourth Normal Form

The role/permission and event/inventory relationships are represented by
separate bridge tables, which avoids storing independent multivalued sets in
one row. Event speakers and sessions are also separate. No independent
multivalued dependency can be conclusively assessed for absent legacy tables.

### Fifth Normal Form

The current decomposition separates several join dependencies, but a complete
5NF claim would require domain-level proof that every decomposed join is
lossless and that no additional join dependency is needed. The repository
does not provide that proof. The academic report should therefore state:
**5NF is not formally certified; the checked-in bridge tables are designed to
remove obvious many-to-many repeating groups.**

---

# CHAPTER 5 – CONCURRENCY CONTROL AND RECOVERY

## 5.1 Transaction Implementation

The verified transaction is in [`pages/publicEvent.php`](pages/publicEvent.php).
On RSVP submission it:

1. Calls `$conn->begin_transaction()`.
2. Locks the event capacity row with `SELECT Capacity ... FOR UPDATE`.
3. Counts pending and confirmed RSVPs.
4. Selects `Pending` or `Waitlisted`.
5. Inserts the RSVP and token.
6. Calls `$conn->commit()` on success.
7. Calls `$conn->rollback()` on insert failure.

This protects the capacity decision from the specific concurrent RSVP race
addressed by the row lock. It does not constitute a complete transaction
framework for every page.

## 5.2 Commit and Rollback

`commit()` and `rollback()` are implemented only in the public RSVP workflow
described above. Other CRUD pages execute individual statements and do not
show multi-statement transaction boundaries.

## 5.3 Savepoints and Recovery

`SAVEPOINT` and `ROLLBACK TO SAVEPOINT` are not implemented. No custom recovery
job, backup script, retry queue, or disaster-recovery configuration is present.
InnoDB supplies engine-level durability and rollback for the explicit
transaction, but operational backup/recovery procedures must be supplied
outside the application.

---

# CHAPTER 6 – PROJECT SOURCE CODE

## 6.1 Frontend Implementation

| File/module | Purpose and communication |
|---|---|
| `includes/header.php`, `footer.php`, `navbar.php` | Shared HTML shell, navigation, styles, scripts, flash output, and page metadata. |
| `assets/css/theme.css`, `components.css`, `legacy.css`, `auth.css` | Theme tokens, reusable components, compatibility styling, and authentication views. |
| `assets/js/app.js`, `theme.js` | General browser behavior and theme switching. |
| `assets/js/ai.js` | JSON `fetch` helper for `/api/ai/` endpoints. |
| `assets/js/charts.js` | Chart.js configuration used by dashboard/analytics. |
| `components/ai-event-extractor.js` | Sends event brief and fills event form output. |
| `components/ai-planner.js` | Sends planning prompt and renders summary, budget, timeline, checklist, vendors. |
| `components/natural-search.js` | Sends natural-language catalog search and applies returned filters. |
| `components/ai-chat.js` | Sends assistant messages and renders answers. |
| `components/ai-description.js` | AI description UI component. |

## 6.2 Backend Implementation

### Entry points and authentication

- `register.php`: validates registration, checks duplicate username/email,
  hashes password, inserts a user.
- `login.php`: validates credentials and selected role, verifies password,
  regenerates session identity.
- `logout.php`: destroys the authenticated session.
- `auth.php`: compatibility wrappers `requireLogin`, `currentRole`,
  `requireRole`, `csrfToken`, and `verifyCsrf`.
- `includes/auth_guard.php`: secure cookie settings, session identity, CSRF
  generation/verification, authentication and role redirects.
- `profile.php`, `editProfile.php`: profile read/update.

### Main pages

- `dashboard.php`: totals, notifications, RSVP status aggregates, upcoming
  events, attendees, booking counts, revenue, charts, and quick links.
- `pages/events.php`, `addEvent.php`, `editEvent.php`, `deleteEvent.php`:
  event listing and CRUD.
- `pages/attendee.php`, `addAttendee.php`, `editAttendee.php`,
  `deleteAttendee.php`: legacy attendee CRUD.
- `pages/venue.php`, `addVenue.php`, `editVenue.php`, `deleteVenue.php`:
  venue CRUD.
- `pages/organiser.php`, `addOrganiser.php`, `editOrganiser.php`,
  `deleteOrganiser.php`: organizer CRUD.
- `pages/catalog.php`, `publicEvent.php`: discovery, filtering, public event
  display, ticket selection, capacity-aware RSVP, token/QR output.
- `pages/rsvps.php`: status/payment updates, waitlist promotion, filtering,
  search, and CSV export.
- `pages/checkin.php`: check-in workflow.
- `pages/tickets.php`: ticket workflow.
- `pages/tasks.php`: task creation, status updates, deletion, and ordering.
- `pages/notifications.php`, `calendar.php`, `recurring.php`: notification,
  calendar, and recurrence-related views.
- `pages/analytics.php`, `ai-insights.php`: operational and AI-assisted
  summaries.
- `pages/feedback.php`: rating/comment submission and local sentiment summary.
- `pages/export*.php`: CSV/calendar export.
- `search.php`: global search across allow-listed legacy tables.

## 6.3 Database Connectivity

- `db.php` creates a MySQLi connection using `DB_HOST`, `DB_USER`,
  `DB_PASSWORD`, `DB_NAME`, and `DB_PORT`, sets `utf8mb4`, and returns HTTP
  503 on connection failure.
- `config/database.php` exposes `eventspace_database(): PDO` with exceptions,
  associative fetch mode, and native prepares.
- Current pages primarily include `db.php`; the PDO helper is available but is
  not the dominant path.
- `config/app.php` loads `.env`, defines root/base URL/environment/debug,
  upload limits, and AI settings.

## 6.4 Important Modules and Functions

| Function | File | Implementation |
|---|---|---|
| `eventspace_load_env` | `config/app.php` | Loads simple `NAME=value` environment entries without overwriting existing environment values. |
| `eventspace_database` | `config/database.php` | Lazily creates a PDO MySQL connection with exception mode and native prepares. |
| `start_eventspace_session` | `includes/auth_guard.php` | Starts a session with HttpOnly, SameSite=Lax, and conditional Secure cookie settings. |
| `csrf_token`, `verify_csrf_token` | `includes/auth_guard.php` | Generates a random token and validates submitted tokens with `hash_equals`. |
| `current_user`, `require_auth`, `require_role` | `includes/auth_guard.php` | Reads session identity and guards protected/role-specific pages. |
| `store_event_image` | `includes/helpers.php` | Enforces 5 MB limit, checks actual MIME, generates a random filename, and stores JPG/PNG/WebP. |
| `eventspace_ai_completion` | `includes/ai_client.php` | Calls the configured OpenAI-compatible endpoint with cURL, timeout, auth header, and response-shape extraction. |
| `classify_feedback_sentiment` | `includes/sentiment.php` | Deterministic rating plus keyword sentiment classifier. |
| `askAI` | `services/ai.ts` | Generic typed browser-side AI request helper with fallback. |

---

# CHAPTER 7 – RESULTS AND DISCUSSION

## 7.1 Application Overview

The verified application interfaces are:

| Screen/module | User actions | Backend/database operations | Evidence/status |
|---|---|---|---|
| Login | Select role, enter credentials | `SELECT users`, password verification, session setup | Implemented |
| Registration | Submit account details | Duplicate check and `INSERT users` | Implemented |
| Dashboard | View totals, charts, upcoming events, revenue | Counts, grouped RSVP query, event/attendee selects, ticket join | Implemented, depends on legacy tables |
| Events | List, create, edit, delete, upload image | Event CRUD and `Image_Path` update | Implemented, depends on `Event` base table |
| Attendees | Add/edit/delete/list | Legacy `Attendee` CRUD | Implemented in code; DDL absent |
| Venues | Add/edit/delete/list | Legacy `Venue` CRUD | Implemented in code; DDL absent |
| Organizers | Add/edit/delete/list | Legacy `Organiser` CRUD | Implemented in code; DDL absent |
| Catalog | Search by keyword/date/location/budget | Prepared `Event` filtering and ordering | Implemented |
| Public event/RSVP | View event, choose ticket, RSVP, receive token/QR | Ticket count, locked capacity, RSVP insert, commit/rollback | Implemented |
| RSVP management | Filter/search/update status/payment/export | RSVP update, waitlist promotion, CSV select | Implemented |
| Check-in | Validate attendee token/check-in | Uses RSVP/check-in data | Implemented page; runtime evidence not supplied |
| Tasks | Create, update status, delete, sort | `event_tasks` CRUD | Implemented |
| Tickets | Manage/display ticket data | `event_tickets` queries | Implemented/partial |
| Notifications | View/mark notifications | `event_notifications` queries | Implemented/partial |
| Calendar/recurring | View/export dates and occurrences | Calendar/occurrence queries | Implemented/partial |
| Feedback | Select event, submit rating/comment | Event check, user email lookup, `event_feedback` insert, grouped summary | Implemented |
| Analytics | View charts/metrics | Aggregate queries | Implemented |
| AI insights | View recommendations, sentiment, attendance | Event/RSVP/feedback aggregates and local ranking | Implemented |
| AI planner | Enter event brief | JSON endpoint; external AI or local fallback | Implemented |
| AI event extractor | Enter natural-language brief | JSON endpoint; external AI or clear no-key result | Implemented |
| Natural-language search | Enter free-text search | JSON parsing then safe catalog filters | Implemented |
| AI assistant | Ask authenticated workspace question | Upcoming event context + optional AI completion | Implemented |
| Foundation modules | Roles, permissions, vendors, expenses, etc. | DDL exists; complete UI/service behavior not evidenced | Database foundation |

## 7.2 Implementation Screenshots

No running-application screenshots are stored in the repository. Capture the
following for the report:

1. Login page with role selector.
2. Registration page and successful account creation message.
3. Dashboard with cards, upcoming events, chart, RSVP statistics, and revenue.
4. Event list with uploaded/fallback images.
5. Create-event form with recurrence, capacity, upload, and AI extractor.
6. Edit-event form showing persisted values.
7. Catalog with normal filters.
8. Catalog with natural-language search result.
9. Public event page before RSVP.
10. Successful RSVP page showing token and QR code.
11. Full-capacity event showing waitlist behavior.
12. RSVP management with status/payment update and export control.
13. Check-in page with successful check-in.
14. Task board with priority/status ordering.
15. Ticket management and ticket selection.
16. Calendar/recurring event view.
17. Feedback submission and organizer sentiment snapshot.
18. Analytics page.
19. AI planner response showing summary, budget, timeline, checklist, and vendors.
20. AI insights recommendations, sentiment, and average attendance.
21. Authenticated AI assistant response.
22. Profile/edit-profile page.

For each screenshot, record the database seed state, browser URL, user role,
date/time, and whether the AI key was enabled. Do not claim a screenshot exists
until it is captured from a running installation.

## 7.3 Module-wise Results

- **Authentication**: password hashing, role-sensitive login, sessions,
  session regeneration, CSRF protection, and permission redirects are present.
- **Event operations**: event creation validates required fields, status,
  budget, capacity, recurrence, and uploaded image type/size.
- **Attendance**: RSVP status supports pending, confirmed, declined, and
  waitlisted states; a declined RSVP can promote the oldest waitlisted guest.
- **Capacity safety**: public RSVP uses `FOR UPDATE`, a count, and a single
  transaction.
- **AI assistance**: the planner has a deterministic local fallback; search
  has a local filter fallback; extraction returns empty fields plus a setup
  message when no key is configured.
- **Feedback**: comments are not sent to the external provider; sentiment is
  locally classified from rating and transparent keywords.
- **Security**: prepared statements are used in newer flows, output is
  escaped, image filenames are random, and AI input lengths are bounded.
- **Limitations of evidence**: no live database output, browser recording,
  deployment log, or production performance measurement is included.

## 7.4 Output Analysis

Expected output includes HTML pages for server-rendered screens, redirect/flash
messages after form operations, CSV files for exports, JSON for AI endpoints,
and token/QR presentation after successful RSVP. Actual row counts, screenshots,
response timings, and database query outputs are unavailable from static source
alone and must be collected during runtime validation.

---

# CHAPTER 8 – CONCLUSION

EventSpace provides a coherent event-management foundation with server-rendered
PHP pages, a MySQL relational model, reusable authentication/layout helpers,
event and RSVP workflows, operational dashboards, exports, feedback analysis,
and optional AI assistance. The most significant verified database behavior is
the transaction-protected capacity check and RSVP insert.

The main technical outcomes are:

- centralized event operations;
- role-aware authenticated workflows;
- prepared database operations in current feature flows;
- CSRF and output-encoding protections;
- validated image uploads;
- JSON AI endpoints with bounded input and fallbacks;
- normalized bridge tables for key many-to-many relationships;
- extensible SQL foundation for future event modules.

Current limitations are the absent base DDL for legacy relations, mixed MySQLi/
PDO access, incomplete wiring of foundation tables, no database routines or
triggers, no savepoints, and no repository evidence of deployment or runtime
benchmarks.

Possible future enhancements (not currently implemented) include completing
the foundation-module UI, migrating all pages to one database abstraction,
foreign-keying textual user references, formal migrations/backups, payment
processing, email/SMS delivery, richer reporting, test automation, attendance
forecasting, recommendation models, multilingual assistance, and human-
approved AI operational actions.

---

# CHAPTER 9 – NPTEL HALLTICKET AND PROGRESS

No NPTEL hall ticket, certificate, course identifier, enrollment number,
progress report, or certification evidence is present in the repository.
Include this chapter only after supplying verified institutional documents.

---

# CHAPTER 10 – GITHUB REPOSITORY

Verified repository remote:

`https://github.com/prateek1596/eventManagement`

No separate deployment URL is documented. The repository README describes a
local Apache/PHP-compatible-server setup and an `APP_BASE_URL` of
`/eventManagement`. No NPTEL link is available.

## Software and hardware requirements

### Software

- PHP 8-style runtime with MySQLi, PDO MySQL, cURL, sessions, file uploads,
  and image metadata support (`getimagesize`).
- MySQL/MariaDB with InnoDB, `utf8mb4`, and support for the SQL syntax used by
  the migrations.
- Apache or another PHP-compatible web server.
- Modern browser with JavaScript enabled.
- Optional OpenAI-compatible API endpoint and API key.

The exact PHP/MySQL versions are not pinned in a manifest and must be recorded
from the deployment environment used for screenshots.

### Hardware

No hardware specification is stated in the repository. For an academic
demonstration, record the actual machine/VM used (CPU, RAM, storage, OS) rather
than inventing a minimum configuration.

## References available in the repository

- [`README.md`](README.md): local setup, architecture summary, AI and photo behavior.
- [`PROJECT_DOCUMENTATION.md`](PROJECT_DOCUMENTATION.md): implementation
  overview, modules, workflows, security notes, and roadmap.
- [`database/schema.sql`](database/schema.sql): base database and users table.
- [`database/upgrade.sql`](database/upgrade.sql): operational schema upgrades.
- [`database/modules.sql`](database/modules.sql): future-module foundation.
- [`EventSpace_IEEE_Paper.docx`](EventSpace_IEEE_Paper.docx) and
  [`EventSpace_IEEE_Paper.pdf`](EventSpace_IEEE_Paper.pdf): reference academic
  paper supplied with the repository.
- [`ai-request-pipeline.svg`](ai-request-pipeline.svg): available AI request
  pipeline diagram asset.

