# Workplace CMS

This project converts the Workplace Solutions marketing site into a lightweight PHP/MySQL content management system.

## Features
- Installs into any folder (default `/work`) via web-based installer.
- Stores pages, sections, and menus in MySQL for editing through an admin dashboard.
- Rich-text editor (TinyMCE) for editing global sections (header, footer, etc.) and individual page sections.
- Menu management with drag-friendly sorting inputs and support for nested items and external links.
- Preserves the original static HTML content by importing it into the database during installation.

## Installation
1. Copy the project into your web root (e.g. `htdocs/work`).
2. Create an empty MySQL database.
3. Visit `http://localhost/work/install.php` and provide database credentials, the site base path, and admin credentials.
4. After installation, log in at `http://localhost/work/admin/login.php` to manage content.

## Development Notes
- Database configuration is stored in `config.php` after running the installer. A template lives in `config.sample.php`.
- `.htaccess` rewrites clean URLs like `/contact` or legacy `.html` paths to the dynamic router.
- Original HTML files remain in place so the installer can import their contents.

## Admin Capabilities
- Create, edit, delete pages and manage per-page sections.
- Edit global site sections (preloader, top bar, footer, etc.).
- Update primary navigation items, including dropdowns and button-style links.
- Configure basic settings such as site name and logo path.

Run `php -l` across the project to lint PHP files during development.
