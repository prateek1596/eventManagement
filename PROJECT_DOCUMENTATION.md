# EventSpace Project Documentation

## 1. Project overview

EventSpace is a web-based event-management system for planning, organizing,
promoting, and operating events from one workspace.

The application is designed for event organizers, administrators, staff,
attendees, and public visitors. It combines event CRUD operations with
attendee management, venues, RSVPs, tickets, notifications, analytics, event
photos, and AI-assisted workflows.

The project currently contains two layers:

1. **Implemented application features** that are available through the PHP
   pages and existing database tables.
2. **Module foundation features** represented by the idempotent
   [`database/modules.sql`](C:/Users/prate/OneDrive/Desktop/eventManagement/database/modules.sql).
   These tables prepare the system for the next implementation phases; a table
   existing in the schema does not mean its full user interface is complete.

## 2. Main objectives

- Make event creation and management easier.
- Keep event, attendee, venue, ticket, and RSVP information centralized.
- Reduce manual work through natural-language AI tools.
- Provide secure authentication and role-based access.
- Give organizers operational visibility through dashboards, analytics, and
  notifications.
- Support future business modules without redesigning the database.

## 3. Technology stack

| Technology | Why it is used |
|---|---|
| PHP 8-style application code | Server-side rendering, form processing, authentication, and database operations |
| MySQL/MariaDB | Relational storage for users, events, RSVPs, tickets, tasks, and module data |
| MySQLi | Used by the existing application pages for prepared statements and queries |
| PDO | Available in [`config/database.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/config/database.php) for reusable service-layer database access |
| HTML5 | Page structure and accessible forms |
| CSS | Responsive application styling and theme support |
| Bootstrap 5.3 | Grid, forms, buttons, responsive layout, and utility classes |
| Bootstrap Icons | Consistent interface icons |
| Vanilla JavaScript | AI interactions, charts, theme behavior, and browser-side enhancements |
| TypeScript | Service-layer AI source in [`services/ai.ts`](C:/Users/prate/OneDrive/Desktop/eventManagement/services/ai.ts) |
| Chart.js | Dashboard and analytics visualizations |
| cURL | Server-to-server communication with the configured AI provider |
| `.env` configuration | Keeps database and AI settings outside application source code |

## 4. High-level architecture

```text
Browser
  |
  | HTML forms, GET/POST requests, JavaScript fetch calls
  v
PHP entry pages
  |
  +--> includes/       shared layout, navigation, auth, flash messages, helpers
  +--> config/         environment and application constants
  +--> db.php          MySQLi connection used by current pages
  +--> api/ai/         JSON AI endpoints
  +--> assets/         CSS, JavaScript, images, and uploads
  |
  v
MySQL database

Browser JavaScript
  |
  +--> PHP AI endpoint
           |
           +--> Configured AI API
```

### Request lifecycle

1. A user opens a PHP page in the browser.
2. The page loads configuration and database access.
3. Protected pages start the EventSpace session and check authentication.
4. GET requests load data and render HTML.
5. POST requests validate CSRF tokens, validate form fields, and write data.
6. Shared header/footer files provide common styling, navigation, flash
   messages, scripts, and page metadata.
7. AI-enabled controls send JSON to an endpoint under [`api/ai/`](C:/Users/prate/OneDrive/Desktop/eventManagement/api/ai).
8. The endpoint validates input, calls the configured AI provider, validates
   the response shape, and returns JSON to the browser.

## 5. Folder structure

```text
eventManagement/
|
|-- api/
|   `-- ai/
|       |-- extract-event.php      NLP event form extraction
|       |-- planner.php            AI event planning
|       `-- search.php             Natural-language event search
|
|-- assets/
|   |-- css/
|   |   |-- auth.css               Login and registration styling
|   |   |-- components.css         Shared application components
|   |   |-- dark-theme.css         Legacy dark-theme compatibility layer
|   |   |-- legacy.css             Legacy page styling
|   |   `-- theme.css              Main design tokens and theme
|   |-- img/
|   |   |-- placeholders/          Placeholder assets
|   |   |-- profile/               Profile images
|   |   `-- uploads/               User-uploaded event photos
|   |-- js/
|   |   |-- ai.js                 Shared AI request helper
|   |   |-- app.js                General browser behavior
|   |   `-- charts.js             Chart.js configuration
|   `-- theme.js                  Theme behavior
|
|-- components/
|   |-- ai-chat.js                 Event assistant interface
|   |-- ai-description.js          AI description generation UI
|   |-- ai-event-extractor.js      Event brief extraction UI
|   |-- ai-planner.js              Event planner UI
|   `-- natural-search.js          Natural-language catalog search
|
|-- config/
|   |-- app.php                    Environment and application constants
|   `-- database.php               PDO database helper
|
|-- database/
|   |-- schema.sql                 Base database setup
|   |-- upgrade.sql                Existing feature migrations
|   `-- modules.sql                Future module foundation tables
|
|-- includes/
|   |-- auth_guard.php             Session and authorization helpers
|   |-- flash.php                  Flash messages and CSRF support
|   |-- footer.php                 Shared scripts and closing layout
|   |-- header.php                 Shared HTML head and page opening
|   |-- helpers.php                Escaping, URLs, uploads, and utilities
|   `-- navbar.php                 Main navigation
|
|-- pages/                         Authenticated event-management pages
|-- services/                      Service-layer source
|-- .env.example                   Environment variable template
|-- db.php                         Current MySQLi connection bootstrap
`-- README.md                      Quick setup guide
```

## 6. Implemented modules

### Authentication and user accounts

Implemented through [`login.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/login.php),
[`register.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/register.php),
[`logout.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/logout.php),
[`auth.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/auth.php), and
[`includes/auth_guard.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/includes/auth_guard.php).

Capabilities:

- Registration
- Login and logout
- Password hashing and verification
- Session-based identity
- Role checks for administrative pages
- Session regeneration after successful login
- CSRF token generation and verification

### Dashboard and analytics

The dashboard in [`dashboard.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/dashboard.php)
provides:

- Event totals
- Upcoming events
- Attendee totals
- Revenue summary
- Upcoming-event cards
- Dashboard charts
- Quick links to management pages

### Event management

The event module supports:

- Creating events
- Editing events
- Deleting events
- Event status
- Date and time
- Venue and contact information
- Budget
- Capacity
- Recurrence rules
- Recurrence end dates
- Event photos

Main pages:

- [`pages/addEvent.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/addEvent.php)
- [`pages/editEvent.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/editEvent.php)
- [`pages/events.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/events.php)
- [`pages/publicEvent.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/publicEvent.php)

### Attendees and RSVPs

The application supports attendee records, RSVP statuses, guest check-in
tokens, checked-in timestamps, and attendance-related workflows.

Relevant pages include:

- [`pages/attendee.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/attendee.php)
- [`pages/addAttendee.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/addAttendee.php)
- [`pages/editAttendee.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/editAttendee.php)
- [`pages/rsvps.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/rsvps.php)
- [`pages/checkin.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/checkin.php)

### Venues and organizers

Venue pages manage location, capacity, facilities, cost, and availability.
Organizer pages manage organizer records and event relationships.

### Tickets

The existing ticket area supports ticket-related event workflows and is backed
by ticket tables in [`database/upgrade.sql`](C:/Users/prate/OneDrive/Desktop/eventManagement/database/upgrade.sql).

### Tasks, recurring events, and notifications

Organizers can manage event tasks, recurring event data, and in-app reminders.
Notifications are generated for upcoming events and can be marked as read.

### Search

There are two search experiences:

1. Global workspace search in [`search.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/search.php).
2. Event catalog search in [`pages/catalog.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/catalog.php).

The catalog supports keyword, location, date, and budget filtering.

## 7. AI and NLP features

### AI event planner

The user describes an event, for example:

> Plan a one-day conference for 300 people with a budget of ₹5 lakh.

The planner returns:

- Summary
- Budget categories
- Timeline
- Checklist
- Vendor suggestions

Implementation:

- Endpoint: [`api/ai/planner.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/api/ai/planner.php)
- UI: [`components/ai-planner.js`](C:/Users/prate/OneDrive/Desktop/eventManagement/components/ai-planner.js)

### Event brief extraction

The event creation page accepts plain language and extracts structured fields:

- Name
- Date
- Time
- Venue
- Contact information
- Budget
- Capacity

Implementation:

- Endpoint: [`api/ai/extract-event.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/api/ai/extract-event.php)
- UI: [`components/ai-event-extractor.js`](C:/Users/prate/OneDrive/Desktop/eventManagement/components/ai-event-extractor.js)

### Natural-language event search

Users can write:

> Music events in Goa under ₹3,000 next month.

The AI converts this into structured filters:

```json
{
  "query": "music",
  "location": "Goa",
  "from": "2026-11-01",
  "to": "2026-11-30",
  "maxPrice": "3000"
}
```

The catalog then applies those filters with prepared SQL parameters.

Implementation:

- Endpoint: [`api/ai/search.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/api/ai/search.php)
- UI: [`components/natural-search.js`](C:/Users/prate/OneDrive/Desktop/eventManagement/components/natural-search.js)

### AI descriptions and assistant

The application also includes an AI description-generation interface and an
event assistant interface. These are browser components that can be extended
with database-aware responses.

### AI fallback behavior

The application remains usable without an AI key:

- Event planning has fallback content.
- Search has a local keyword/location/budget fallback.
- Event extraction shows a configuration message rather than inventing data.

## 8. Photo and file handling

### Event photos

Users can upload a photo while creating or editing an event. The server:

1. Checks the upload error status.
2. Limits the file to 5 MB.
3. Reads the actual image MIME type with `getimagesize`.
4. Allows only JPEG, PNG, and WebP.
5. Generates a random filename.
6. Stores the file in `assets/img/uploads/`.
7. Saves the relative path in `Event.Image_Path`.

The image is displayed on the event list, catalog, dashboard, and public event
page. Unsplash images remain fallback visuals when an event has no uploaded
photo.

### Documents and future uploads

The `event_documents` foundation table is ready for contracts, invoices,
permits, floor plans, and other event files. A full document-management UI is
planned for a later phase.

## 9. Database design

### Installation order

Run the SQL files in this order:

```text
1. database/schema.sql
2. database/upgrade.sql
3. database/modules.sql
```

### Base schema

[`database/schema.sql`](C:/Users/prate/OneDrive/Desktop/eventManagement/database/schema.sql)
creates the database and the initial `users` table.

The user table contains:

- `id`
- `username`
- `password`
- `email`
- `name`
- `phone`
- `role`
- `created_at`

### Existing feature schema

[`database/upgrade.sql`](C:/Users/prate/OneDrive/Desktop/eventManagement/database/upgrade.sql)
extends the existing database with:

- `Event` columns for capacity, recurrence, and image path
- `event_rsvps`
- `event_tasks`
- `event_vendors`
- `event_expenses`
- `event_seating_tables`
- `event_seating_assignments`
- `event_templates`
- `event_public_pages`
- `event_reviews`
- `event_notifications`
- `event_audit_log`
- `event_tickets`
- `event_reminders`
- `event_occurrences`

### Module foundation schema

[`database/modules.sql`](C:/Users/prate/OneDrive/Desktop/eventManagement/database/modules.sql)
adds the remaining planned module structures:

- `event_roles`
- `event_permissions`
- `event_role_permissions`
- `event_messages`
- `event_surveys`
- `event_feedback`
- `event_ticket_types`
- `event_inventory`
- `event_inventory_assignments`
- `event_documents`
- `event_calendar_items`
- `event_sponsors`
- `event_speakers`
- `event_sessions`
- `event_risks`

### Relationship model

```text
users
  |
  +--> event_audit_log
  +--> event_messages.created_by
  +--> event_templates.created_by

Event
  |
  +--> RSVPs
  +--> Tasks
  +--> Vendors
  +--> Expenses
  +--> Tickets
  +--> Notifications
  +--> Reviews / Feedback
  +--> Seating tables
  +--> Public page

## Roles and feedback

Login now requires the user to select the role stored on the account:

- **Organizer**: creates and manages events, tickets, venues, and reporting.
- **Staff**: supports operations such as check-in and event coordination.
- **Attendee**: discovers events and submits post-event feedback.

The authenticated feedback page is [`pages/feedback.php`](C:/Users/prate/OneDrive/Desktop/eventManagement/pages/feedback.php).
It stores ratings and comments in `event_feedback` and assigns a transparent
Positive, Neutral, Negative, or Mixed sentiment using the rating plus a
local keyword classifier. This keeps the feature usable without sending guest
comments to an external AI provider. The `database/modules.sql` migration must
be applied before using it.

## Suggested AI/ML roadmap

1. **Feedback topic extraction**: group comments into themes such as venue,
   catering, registration, speakers, and accessibility, then show theme trends
   beside sentiment.
2. **Attendance forecasting**: predict registrations and no-shows from event
   type, date, venue, ticket price, lead time, and historical RSVP behavior.
3. **Personalized event recommendations**: recommend events to attendees from
   their interests, RSVP history, location, and capacity availability.
4. **No-show risk scoring**: identify likely no-shows so staff can send
   reminders or release waitlisted seats without exposing personal scores.
5. **Schedule and venue optimization**: suggest time slots and venues using
   demand, capacity, travel distance, cost, and historical satisfaction.
6. **AI event copilot**: turn a natural-language brief into an event plan,
   timeline, task list, budget draft, and vendor checklist with human approval.
7. **Anomaly detection**: flag unusual ticket sales, duplicate RSVPs, refund
   spikes, or check-in patterns for staff review.

For production, keep human approval for operational decisions, redact
personally identifiable information before model calls, log model confidence,
and provide an explanation or override for every recommendation.
  +--> Sponsors
  +--> Speakers and sessions
  +--> Risks
  +--> Documents
  +--> Calendar items
  +--> Inventory assignments
```

Foreign keys use cascading deletes where child records belong exclusively to
an event. Optional relationships use `SET NULL` where preserving a child
record without its former parent is useful.

## 10. Security and reliability

Current protections include:

- Password hashing through `password_hash` and verification through
  `password_verify`.
- Prepared statements on the main event and authentication workflows.
- CSRF token verification for protected POST operations.
- HTML escaping through the shared `e()` helper.
- Session regeneration after login.
- Role checks for restricted pages.
- MIME and size checks for uploaded photos.
- Random upload filenames to avoid user-controlled paths.
- AI input length limits and response-shape validation.
- Error logging for database and AI service failures.

Before production deployment, the remaining legacy pages should be migrated
to one consistent database abstraction and prepared-statement style.

## 11. Environment configuration

Copy [`.env.example`](C:/Users/prate/OneDrive/Desktop/eventManagement/.env.example)
to `.env` and configure:

```env
APP_ENV=local
APP_DEBUG=true
APP_BASE_URL=/eventManagement

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=pateek
DB_USER=root
DB_PASSWORD=

AI_API_KEY=
AI_API_URL=https://api.openai.com/v1/chat/completions
AI_MODEL=gpt-4o-mini
```

Never commit `.env` or real API keys.

## 12. Typical user workflows

### Organizer creates an event

1. Organizer signs in.
2. Organizer opens Create Event.
3. Organizer enters details manually or uses the AI event extractor.
4. Organizer optionally uploads a photo.
5. Server validates the form, CSRF token, and image.
6. Event data is inserted into `Event`.
7. The event appears on the dashboard and event-management pages.

### Visitor discovers an event

1. Visitor opens the event catalog.
2. Visitor enters a natural-language search.
3. AI extracts search filters.
4. The catalog applies keyword, location, date, and budget filters.
5. Visitor opens the public event page and can RSVP.

### Organizer plans an event with AI

1. Organizer enters a plain-language event brief.
2. Browser sends JSON to the planner endpoint.
3. PHP calls the configured AI provider.
4. The response is validated.
5. The UI displays the summary, budget ideas, timeline, checklist, and vendors.

## 13. Module roadmap

### Available now

- Authentication
- Event management
- Attendee management
- Venues
- Organizers
- RSVPs
- Tickets
- Tasks
- Notifications
- Analytics
- Public event pages
- Event photos
- AI event planning
- AI event extraction
- Natural-language search

### Database foundation ready

- User roles and permissions
- Vendors and expenses
- Communication center
- Feedback and surveys
- Ticket types
- Event templates
- Seating
- Inventory
- Documents
- Calendar items
- Sponsors
- Speakers and sessions
- Risk management
- Audit logging

### Recommended implementation phases

1. Roles, permissions, and audit interface
2. Vendor and expense management
3. Communication and feedback workflows
4. Ticket types and event templates
5. Seating, inventory, and documents
6. Calendar, reports, and public-page improvements
7. Sponsors, speakers, sessions, and risk management
8. Advanced AI for budget analysis, sentiment, segmentation, risk prediction,
   multilingual content, and marketing

## 14. Presentation summary

### One-minute explanation

> EventSpace is a PHP and MySQL event-management platform. It allows
> organizers to create events, manage venues and attendees, handle RSVPs and
> tickets, track operations, view analytics, and publish public event pages.
> The system uses shared PHP components for authentication, navigation, flash
> messages, CSRF protection, and URL generation. It also includes AI-powered
> natural-language tools that turn event descriptions into structured form
> data, create planning checklists, and convert normal language into event
> search filters. Uploaded event photos are validated and stored securely.
> The database is organized around an Event record with related tables for
> attendees, RSVPs, tickets, tasks, vendors, expenses, notifications, and
> future modules such as seating, inventory, feedback, sponsors, speakers,
> documents, and risk management.

### Key value proposition

EventSpace combines event operations and AI assistance in one system. It
reduces manual data entry, gives organizers operational visibility, improves
attendee workflows, and provides a foundation that can grow into a complete
event-management platform.
