# Analytics for Craft CMS

A simple Google Analytics 4 dashboard widget for Craft CMS.

Analytics brings useful GA4 performance data directly into the Craft control panel, giving content editors and site administrators a quick overview without needing to visit Google Analytics.

## Features

- Active users
- Sessions
- Page views
- Previous-period comparisons
- Page-view trend graph
- Top pages
- Traffic sources
- 7, 30 and 90-day reporting periods
- Per-widget date range
- Cached API responses
- Graceful error handling

## Requirements

- Craft CMS 5
- PHP 8.2 or later
- A Google Analytics 4 property
- Google Analytics Data API access
- A Google service account with access to the GA4 property

## Configuration

The plugin requires two settings:

- GA4 Property ID
- Path to a Google service account credentials JSON file

Environment variables are recommended.

For example:

```dotenv
GOOGLE_ANALYTICS_PROPERTY_ID="123456789"
GOOGLE_APPLICATION_CREDENTIALS="/path/to/google-analytics.json"
```

Then configure the plugin under:

**Settings → Plugins → Analytics**

Enter:

```text
$GOOGLE_ANALYTICS_PROPERTY_ID
```

for the GA4 Property ID, and:

```text
$GOOGLE_APPLICATION_CREDENTIALS
```

for the credentials path.

The credentials JSON file should be stored outside the plugin directory and must not be committed to source control.

## Google Analytics Setup

1. Create or select a Google Cloud project.
2. Enable the Google Analytics Data API.
3. Create a service account.
4. Create a JSON key for the service account.
5. Add the service account email address to the required GA4 property with Viewer access.
6. Configure the Property ID and credentials path in Craft.

## Dashboard Widget

After configuring the plugin:

1. Open the Craft Dashboard.
2. Select **New Widget**.
3. Choose **Google Analytics**.
4. Select a reporting period of 7, 30 or 90 days.
5. Save the widget.

Analytics data is cached for one hour to reduce unnecessary requests to the Google Analytics Data API.

## Development Status

This plugin is currently under development.

### 0.1.0

Initial development release.

## Support

Developed by [DesignKarma](https://designkarma.co.uk).