# EventSpace

EventSpace is a PHP/MySQL event-management application. The project keeps its
existing root-level PHP entry points so current local-server URLs continue to
work, while shared code, routes, assets, and database files are grouped by
responsibility.

## Project structure

```text
.
|-- api/                 JSON/API endpoints
|-- assets/
|   |-- css/             Shared, auth, theme, and compatibility styles
|   |-- img/             Profile, upload, and placeholder images
|   `-- js/              Browser scripts
|-- components/          Reusable frontend components
|-- config/              Environment and database configuration
|-- database/            Database schema and upgrade scripts
|-- includes/            Shared PHP layout, auth, flash, and helper code
|-- pages/               Authenticated event-management pages
|-- services/             Service-layer source code
|-- *.php                Public application entry points
`-- .env.example         Local environment variable template
```

## Local setup

1. Copy `.env.example` to `.env` and set the database credentials.
2. Create the configured MySQL database.
3. Apply `database/schema.sql`, followed by `database/upgrade.sql` when upgrading
   an existing installation.
4. Serve the repository with Apache/PHP or another PHP-compatible local server.
5. Set `APP_BASE_URL` to the path where the repository is served.

The PHP pages use the existing URL layout, so moving assets and SQL files does
not require changing bookmarks or web-server entry-point configuration.

## AI and event photos

The event form includes an AI brief extractor. Add `AI_API_KEY` and
`AI_API_URL` in `.env` to enable it; without a key, the form remains usable and
shows a clear setup message.

Event photos can be uploaded from the create/edit event forms. Run the upgrade
script before using this feature so the `Event.Image_Path` column exists.
Images are validated as JPG, PNG, or WebP files up to 5 MB and stored in
`assets/img/uploads/`. The directory is intended for user uploads and already
contains a placeholder file so it remains in source control.
