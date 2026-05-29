# WPFetcher Engine Plugin

Fetch and Vet Options Wrapper, Dashboard Widgets, Custom Post Type Registrars, or Rest API Boilerplates from Github and compile as new API.

## Installation
Log into your dashboard, click Pages, and open your directory page (or create a fresh one).

Ensure the page Slug matches precisely: marketplace-directory (this is what the router class watches for).

## The Architecture: How the Plugin Will Be Structured

Instead of writing one massive, messy PHP file, we can organize the plugin cleanly into modular components, using standard ClassicPress actions and filters.

    The Core Plugin File (wpfresher-engine.php): Handles initialization, security checks (defined('ABSPATH') || exit;), and loads the modules.

    The Database Activator (includes/class-wpf-activator.php): Runs once upon plugin activation to create your custom wp_wpf_pending_review database table.

    The Ingestion Engine (includes/class-wpf-scraper.php): Houses the cURL requests hitting the GitHub REST API and handles the automated algorithmic screening (issue counts, star-to-size ratios, date checks).

    The Admin Dashboard UI (admin/class-wpf-admin.php): Registers a clean, custom WordPress admin menu page (the "Vetting Queue") where you review the filtered rows and click "Approve" or "Reject".

    The Tokenization Engine (includes/class-wpf-tokenizer.php): Handles the physical file storage of the master boilerplate templates and contains the deterministic PHP logic (str_replace or regex mapping) that swaps out your target placeholders (like {{CUSTOM_PREFIX}}) when a user triggers a frontend download.

```
wpfetcher-engine/
│
├── wpfetcher-engine.php
│
├── admin/
│   └── class-wpf-admin.php
│
├── includes/
│   ├── class-wpf-activator.php
│   ├── class-wpf-deactivator.php
│   ├── class-wpf-scraper.php
│   ├── class-wpf-tokenizer.php
│   ├── class-wpf-exporter.php
│   └── class-wpf-receiver.php
│
├── public/
│   └── views-directory.php      
│
└── templates/
    └── sample-settings-wrapper.txt
    └── class-wpf-scraper.php
```

### Phase 1: Database Initialization & Setup (The Foundation)

Before we can pull or manipulate data, we need a place to store it. We will write the custom activation hook that creates your lean custom table to hold GitHub repository metadata.
id (Primary Key)

* github_repo_id (To prevent duplicate scraping)

* repo_name / author_name

* description

* repository_url

* stars / open_issues

* status (e.g., 'pending', 'approved', 'rejected')

* date_scraped

### The architectural loop is:

    Ingested metadata safely from an automated filter layer (Wpf_Scraper).

    Managed records using a secure dashboard vetting queue table (Wpf_Admin).

    Compiled a zero-bloat, ultra-performant, flat text catalog directory file (Wpf_Exporter).

    Received & Processed client parameter maps over a live web hook interface (Wpf_Receiver).

    Tokenized static blueprint source definitions into deployment-ready extensions instantly (Wpf_Tokenizer).
