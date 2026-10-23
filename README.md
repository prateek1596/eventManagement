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
