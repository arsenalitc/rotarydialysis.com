# Rotary Dialysis Centers - Project KIND

Website for Rotary Club of Madras Industrial City's "Project KIND" (Kidney Initiative for Needy Dialysis patients).

## Site Information

- **URL:** https://rotarydialysis.com
- **WordPress Version:** 6.7.2
- **Theme:** Twenty Twenty-One
- **Hosting:** Hostinger

## Key Plugins

- Popup Maker v1.21.5
- Agile Store Locator v4.5.6 (dialysis center map)
- LiteSpeed Cache

## Pages

- Home (dialysis center locator map)
- About Us
- Contact Us
- Launch

## Directory Structure

```
rotarydialysis.com/
├── wp-content/
│   └── uploads/          # Media files (451 images)
├── database/
│   └── database.sql      # WordPress database export
├── exports/
│   └── package.json      # AI1WM export metadata
├── index.php             # WordPress entry point
├── robots.txt            # SEO configuration
├── .env (credentials - not committed)
├── .env.example          # Credentials template
└── .gitignore
```

## Setup

1. Copy `.env.example` to `.env` and fill in credentials
2. Import `database/database.sql` into MySQL/MariaDB
3. Configure WordPress wp-config.php with database credentials
4. For full restore, use the .wpress backup file in exports/ with All-in-One WP Migration plugin

## Mission

"Kindness in health" - Providing affordable dialysis care to patients in need across India.
