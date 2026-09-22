# Google Analytics for Craft CMS

A simple Google Analytics 4 dashboard for Craft CMS.

Analytics brings useful website performance data directly into the Craft control panel, giving content editors and site administrators a quick overview without needing to visit Google Analytics.

## Features

- Users
- Sessions
- Page views
- Previous-period comparisons
- Interactive page-view trend chart
- Top pages with links to the corresponding website pages
- Traffic sources
- 7, 30 and 90-day reporting periods
- Per-widget reporting periods
- Multiple dashboard widgets
- Direct link to the full Google Analytics report
- Cached API responses
- Configuration status checking
- Graceful error handling

## Requirements

- Craft CMS 5
- PHP 8.2 or later
- A Google Analytics 4 property
- Google Analytics Data API access
- A Google service account with access to the GA4 property

## Installation

Install Analytics using Composer:

```bash
composer require designkarma/craft-analytics
```

Then install the plugin from:

**Settings → Plugins**

Alternatively, install it from the command line:

```bash
php craft plugin/install analytics
```

## Google Analytics Setup

Analytics currently connects to Google Analytics using a Google service account.

### 1. Create a Google Cloud project

Create a new Google Cloud project, or use an existing project.

### 2. Enable the Google Analytics Data API

Enable the **Google Analytics Data API** for the project.

### 3. Create a service account

Create a service account for the project and generate a JSON credentials file.

Store the credentials file somewhere secure outside the plugin directory.

The file should not be committed to source control.

### 4. Give the service account access to GA4

In Google Analytics, add the service account email address to the GA4 property with **Viewer** access.

### 5. Find your GA4 Property ID

Your Property ID is the numeric identifier for the Google Analytics 4 property.

## Configuration

Open:

**Settings → Plugins → Analytics**

The plugin requires:

- **GA4 Property ID**
- **Service account credentials path**

Environment variables are recommended.

For example:

```dotenv
GOOGLE_ANALYTICS_PROPERTY_ID="123456789"
GOOGLE_APPLICATION_CREDENTIALS="/path/to/google-analytics.json"
```

Then enter:

```text
$GOOGLE_ANALYTICS_PROPERTY_ID
```

for the GA4 Property ID, and:

```text
$GOOGLE_APPLICATION_CREDENTIALS
```

for the service account credentials path.

The Analytics settings screen will indicate whether the Property ID and credentials file have been detected successfully.

> For security, keep the service account JSON file outside the plugin directory and do not commit it to your repository.

## Dashboard Widget

Once Analytics has been configured:

1. Open the Craft Dashboard.
2. Select **New Widget**.
3. Choose **Google Analytics**.
4. Select a reporting period of 7, 30 or 90 days.
5. Save the widget.

The widget displays:

- Users
- Sessions
- Page views
- Percentage changes compared with the previous equivalent period
- Page-view trend
- Top pages
- Traffic sources

Top-page titles link directly to the corresponding page on the website.

A link at the bottom of the widget opens the full report in Google Analytics.

You can add multiple Google Analytics widgets to the Dashboard with different reporting periods.

## Caching

Analytics data is cached by Craft for one hour to reduce unnecessary requests to the Google Analytics Data API.

Clearing Craft's data caches will cause fresh Analytics data to be requested the next time the widget is loaded.

## Troubleshooting

If Analytics cannot retrieve data, check that:

- The GA4 Property ID is correct.
- The credentials path points to an existing JSON credentials file.
- The Google Analytics Data API is enabled for the Google Cloud project.
- The service account has Viewer access to the correct GA4 property.

When Craft's `devMode` is enabled, additional error information may be displayed in the widget to help diagnose connection problems.

## Releases

### 0.1.1

- Improved Analytics configuration screen
- Added configuration status checking
- Improved page-view chart with date labels and area fill
- Added interactive chart tooltips
- Added clickable Top Pages
- Added link to the full Google Analytics report
- Improved support for multiple widgets and dynamic widget refreshes

### 0.1.0

Initial development release.

- GA4 users, sessions and page views
- Previous-period comparisons
- Page-view trend chart
- Top pages
- Traffic sources
- 7, 30 and 90-day reporting periods
- Craft caching
- Service account authentication

## Support

Analytics is developed by DesignKarma.

For more information, visit the DesignKarma website.