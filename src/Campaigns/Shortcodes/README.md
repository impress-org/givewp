# GiveWP Campaign Shortcodes

Welcome to the **GiveWP Campaign Shortcodes** directory! Here you'll find a collection of shortcodes that allow you to display and interact with GiveWP campaigns anywhere shortcodes are supported in WordPress. These shortcodes are designed to be flexible, extensible, and easy to use for both site builders and developers.

---

## Table of Contents
1. [What are Shortcodes?](#what-are-shortcodes)
2. [Available Shortcodes](#available-shortcodes)
    - [Single Campaign](#1-givewp_campaign)
    - [Campaign Grid](#2-givewp_campaign_grid)
    - [Campaign Form](#3-givewp_campaign_form)
    - [Campaign Donors](#4-givewp_campaign_donors)
    - [Campaign Donations](#5-givewp_campaign_donations)
    - [Campaign Comments](#6-givewp_campaign_comments)
     - [Campaign Goal](#7-givewp_campaign_goal)
     - [Campaign Stats](#8-givewp_campaign_stats)
3. [How Shortcodes are Registered](#how-shortcodes-are-registered)
4. [Extending Shortcodes](#extending-shortcodes)
5. [Support & Contribution](#support--contribution)

---

## What are Shortcodes?
Shortcodes are a WordPress feature that allow you to embed dynamic content into posts, pages, or widgets using simple bracketed tags. GiveWP campaign shortcodes make it easy to display campaign information, forms, donors, and more, anywhere on your site.

---

## Available Shortcodes

### 1. `[givewp_campaign]` — Single Campaign
Displays a single campaign.

| Attribute         | Type    | Default | Description                        |
|------------------|---------|---------|------------------------------------|
| campaign_id      | string  | —       | The ID of the campaign to display. |
| show_image       | bool    | true    | Show the campaign image.           |
| show_description | bool    | true    | Show the campaign description.     |
| show_goal        | bool    | true    | Show the campaign goal progress.   |

**Example:**
```[givewp_campaign campaign_id="123" show_image="true" show_description="false" show_goal="true"]```

[Source: CampaignShortcode.php](./CampaignShortcode.php)

---

### 2. `[givewp_campaign_grid]` — Campaign Grid
Displays a grid of campaigns.

| Attribute         | Type    | Default | Description                        |
|------------------|---------|---------|------------------------------------|
| layout           | string  | full    | Layout style for the grid.         |
| show_image       | bool    | true    | Show campaign images.              |
| show_description | bool    | true    | Show campaign descriptions.        |
| show_goal        | bool    | true    | Show campaign goals.               |
| sort_by          | string  | date    | Sort campaigns by this field.      |
| order_by         | string  | desc    | Order direction.                   |
| per_page         | int     | 6       | Number of campaigns per page.      |
| show_pagination  | bool    | true    | Show pagination controls.          |
| filter_by        | string  | —       | Filter campaigns by a value.       |

**Example:**
```[givewp_campaign_grid layout="compact" per_page="12" show_pagination="false"]```

[Source: CampaignGridShortcode.php](./CampaignGridShortcode.php)

---

### 3. `[givewp_campaign_form]` — Campaign Form
Displays a donation form for a campaign.

| Attribute              | Type    | Default      | Description                          |
|------------------------|---------|--------------|--------------------------------------|
| campaign_id            | int     | —            | The campaign ID.                     |
| block_id               | string  | —            | Block identifier.                    |
| prev_id                | int     | —            | Previous form ID.                    |
| id                     | int     | —            | Form ID.                             |
| display_style          | string  | onpage       | How to display the form.             |
| continue_button_title  | string  | Donate Now   | Text for the continue button.        |
| show_title             | bool    | true         | Show the form title.                 |
| content_display        | string  | above        | Where to display content.            |
| show_goal              | bool    | true         | Show the campaign goal.              |
| show_content           | bool    | true         | Show campaign content.               |
| use_default_form       | bool    | true         | Use the default form layout.         |

**Example:**
```[givewp_campaign_form campaign_id="123" show_title="false" continue_button_title="Support Now"]```

[Source: CampaignFormShortcode.php](./CampaignFormShortcode.php)

---

### 4. `[givewp_campaign_donors]` — Campaign Donors
Displays a list of campaign donors.

| Attribute             | Type    | Default      | Description                          |
|-----------------------|---------|--------------|--------------------------------------|
| campaign_id           | int     | —            | The campaign ID.                     |
| block_id              | string  | —            | Block identifier.                    |
| show_anonymous        | bool    | true         | Show anonymous donors.               |
| show_company_name     | bool    | true         | Show company names.                  |
| show_avatar           | bool    | true         | Show donor avatars.                  |
| show_button           | bool    | true         | Show the donate button.              |
| donate_button_text    | string  | Join the list| Text for the donate button.          |
| sort_by               | string  | top-donors   | Sort donors by this field.           |
| donors_per_page       | int     | 5            | Number of donors per page.           |
| load_more_button_text | string  | Load more    | Text for the load more button.       |

**Example:**
```[givewp_campaign_donors campaign_id="123" show_avatar="false" donors_per_page="10"]```

[Source: CampaignDonorsShortcode.php](./CampaignDonorsShortcode.php)

---

### 5. `[givewp_campaign_donations]` — Campaign Donations
Displays a list of campaign donations.

| Attribute             | Type    | Default      | Description                          |
|-----------------------|---------|--------------|--------------------------------------|
| campaign_id           | int     | —            | The campaign ID.                     |
| show_anonymous        | bool    | true         | Show anonymous donations.            |
| show_icon             | bool    | true         | Show donation icons.                 |
| show_button           | bool    | true         | Show the donate button.              |
| donate_button_text    | string  | Donate       | Text for the donate button.          |
| sort_by               | string  | recent-donations | Sort donations by this field.   |
| donations_per_page    | int     | 5            | Number of donations per page.        |
| load_more_button_text | string  | Load more    | Text for the load more button.       |

**Example:**
```[givewp_campaign_donations campaign_id="123" show_icon="false" donations_per_page="20"]```

[Source: CampaignDonationsShortcode.php](./CampaignDonationsShortcode.php)

---

### 6. `[givewp_campaign_comments]` — Campaign Comments
Displays campaign comments.

| Attribute           | Type    | Default | Description                          |
|---------------------|---------|---------|--------------------------------------|
| block_id            | string  | —       | Block identifier.                    |
| campaign_id         | int     | —       | The campaign ID.                     |
| title               | string  | —       | Section title.                       |
| show_anonymous      | bool    | true    | Show anonymous comments.             |
| show_avatar         | bool    | true    | Show commenter avatars.              |
| show_date           | bool    | true    | Show comment dates.                  |
| show_name           | bool    | true    | Show commenter names.                |
| comment_length      | int     | 200     | Max comment length to display.       |
| read_more_text      | string  | —       | Text for the read more link.         |
| comments_per_page   | int     | 3       | Number of comments per page.         |

**Example:**
```[givewp_campaign_comments campaign_id="123" show_avatar="false" comments_per_page="5"]```

[Source: CampaignCommentsShortcode.php](./CampaignCommentsShortcode.php)

---

### 7. `[givewp_campaign_goal]` — Campaign Goal
Displays the campaign goal progress for a specific campaign.

| Attribute   | Type | Default | Description                |
|-------------|------|---------|----------------------------|
| campaign_id | int  | —       | The campaign ID to render. |

**Example:**
```[givewp_campaign_goal campaign_id="123"]```

[Source: CampaignGoalShortcode.php](./CampaignGoalShortcode.php)

---

### 8. `[givewp_campaign_stats]` — Campaign Stats
Displays a statistic for a specific campaign.

| Attribute   | Type   | Default       | Description                                                                 |
|-------------|--------|---------------|-----------------------------------------------------------------------------|
| campaign_id | int    | —             | The campaign ID.                                                            |
| statistic   | string | top-donation  | Which statistic to display. Allowed: `top-donation`, `average-donation`.    |

**Example:**
```[givewp_campaign_stats campaign_id="123" statistic="average-donation"]```

[Source: CampaignStatsShortcode.php](./CampaignStatsShortcode.php)

---

## How Shortcodes are Registered
Shortcodes are registered in the GiveWP plugin, typically during the plugin's initialization. To add or modify a shortcode, see the registration logic in the main plugin or service provider files.

---

## Extending Shortcodes
All shortcodes are implemented as PHP classes. You can extend or override their behavior by subclassing or using WordPress hooks/filters. For advanced customization, refer to the source code linked above.

---

## Support & Contribution
- For help, visit the [GiveWP Documentation](https://givewp.com/documentation/).
- To contribute, open a pull request or issue on the main repository.
- For questions, reach out via the [GiveWP support channels](https://givewp.com/contact/).

---

## Notes
- All boolean attributes accept `true` or `false` (case-insensitive).
- All shortcodes automatically enqueue the necessary scripts and styles for proper display.
- For more advanced usage, refer to the source code in this directory.
