# GiveWP Campaign Elementor Template

This template provides a way to create campaign pages in Elementor that mimic the layout of the default Gutenberg campaign page template, but using shortcodes instead of blocks.

## 🎉 Automatic Setup (NEW!)

**When Elementor is active, campaign pages are now automatically populated with the campaign template!** When you create a new campaign page, it will automatically be set up with:
- Two-column layout with campaign image and goal/stats
- Pre-configured shortcodes for all campaign elements
- Ready-to-edit template structure
- Proper Elementor meta data for seamless editing

Simply create a campaign page and it's ready to customize in Elementor!

### Disabling Automatic Setup

If you prefer to manually set up your campaign pages, you can disable the automatic template setup by adding this to your theme's `functions.php`:

```php
// Disable automatic Elementor campaign template setup
add_filter('givewp_auto_setup_elementor_campaign_template', '__return_false');
```

## Overview

The template recreates the following layout structure:
- **Two-column layout** (60%/40% split)
  - Left: Campaign featured image (16:9 aspect ratio, rounded corners)
  - Right: Campaign goal, stats, and donate button
- **Description section** below the columns
- **Campaign donations list**
- **Campaign donors list**

## Available Shortcodes

The template uses these GiveWP campaign shortcodes:

### 1. `[givewp_campaign]` - Single Campaign Display
```
[givewp_campaign campaign_id="123" show_image="true" show_description="false" show_goal="true"]
```

**Attributes:**
- `campaign_id` (required): Campaign ID
- `show_image`: Show campaign image (true/false)
- `show_description`: Show campaign description (true/false)
- `show_goal`: Show campaign goal progress (true/false)

### 2. `[givewp_campaign_form]` - Campaign Donation Form
```
[givewp_campaign_form campaign_id="123" display_style="button" continue_button_title="Donate Now"]
```

**Attributes:**
- `campaign_id` (required): Campaign ID
- `display_style`: How to display form (onpage/button/modal)
- `continue_button_title`: Text for donate button
- `show_title`: Show form title (true/false)
- `show_goal`: Show goal in form (true/false)
- `show_content`: Show campaign content (true/false)

### 3. `[givewp_campaign_donations]` - Campaign Donations List
```
[givewp_campaign_donations campaign_id="123" donations_per_page="5"]
```

**Attributes:**
- `campaign_id` (required): Campaign ID
- `show_anonymous`: Show anonymous donations (true/false)
- `show_icon`: Show donation icons (true/false)
- `show_button`: Show donate button (true/false)
- `donations_per_page`: Number of donations to show
- `sort_by`: Sort order (recent-donations, etc.)

### 4. `[givewp_campaign_donors]` - Campaign Donors List
```
[givewp_campaign_donors campaign_id="123" donors_per_page="5"]
```

**Attributes:**
- `campaign_id` (required): Campaign ID
- `show_anonymous`: Show anonymous donors (true/false)
- `show_avatar`: Show donor avatars (true/false)
- `show_button`: Show donate button (true/false)
- `donors_per_page`: Number of donors to show
- `sort_by`: Sort order (top-donors, etc.)

## Implementation Methods

### Method 1: Using the HTML Template Function

1. Copy the `elementor-campaign-template.php` file to your active theme's folder
2. In your page template or functions.php, call:

```php
// Display the campaign template
$campaignId = 123; // Replace with actual campaign ID
$description = 'Your campaign description here';
echo givewp_get_elementor_campaign_html_template($campaignId, $description);
```

### Method 2: Manual Elementor Implementation

Follow these steps to manually create the layout in Elementor:

#### Step 1: Create the Two-Column Section
1. Add a new **Section** in Elementor
2. Choose **2 Columns** layout
3. Set column widths to **60%** and **40%**
4. In Advanced > Layout settings, set padding top and bottom to **0**

#### Step 2: Left Column - Campaign Image
1. Add a **Shortcode** widget to the left column
2. Enter shortcode:
   ```
   [givewp_campaign campaign_id="YOUR_CAMPAIGN_ID" show_image="true" show_description="false" show_goal="false"]
   ```
3. In the **Advanced** tab, add custom CSS:
   ```css
   .givewp-campaign img {
       width: 100%;
       height: auto;
       aspect-ratio: 16/9;
       object-fit: cover;
       border-radius: 8px;
   }
   ```

#### Step 3: Right Column - Goal and Stats
1. Add a **Shortcode** widget for the campaign goal:
   ```
   [givewp_campaign campaign_id="YOUR_CAMPAIGN_ID" show_image="false" show_description="false" show_goal="true"]
   ```

2. Add an **HTML** widget for campaign stats:
   ```html
   <div class="campaign-stats-container">
       <div class="campaign-stat">
           <span class="stat-label">Total Donations</span>
           <span class="stat-value">$50,000</span>
       </div>
       <div class="campaign-stat">
           <span class="stat-label">Average Donation</span>
           <span class="stat-value">$125</span>
       </div>
   </div>
   ```

3. Add CSS for stats styling:
   ```css
   .campaign-stats-container {
       margin: 20px 0;
   }
   .campaign-stat {
       display: flex;
       justify-content: space-between;
       align-items: center;
       margin-bottom: 10px;
       padding: 5px 0;
   }
   .stat-label {
       font-weight: normal;
       color: #666;
   }
   .stat-value {
       font-weight: bold;
       color: #333;
   }
   ```

4. Add a **Shortcode** widget for the donate button:
   ```
   [givewp_campaign_form campaign_id="YOUR_CAMPAIGN_ID" display_style="button" continue_button_title="Donate Now" show_title="false" show_goal="false" show_content="false"]
   ```

#### Step 4: Description Section
1. Add a new **Section** below the columns
2. Add a **Text Editor** widget
3. Enter your campaign description

#### Step 5: Donations List
1. Add a new **Section**
2. Add a **Shortcode** widget:
   ```
   [givewp_campaign_donations campaign_id="YOUR_CAMPAIGN_ID" show_anonymous="true" show_icon="true" show_button="true" donations_per_page="5"]
   ```

#### Step 6: Donors List
1. Add a final **Section**
2. Add a **Shortcode** widget:
   ```
   [givewp_campaign_donors campaign_id="YOUR_CAMPAIGN_ID" show_anonymous="true" show_avatar="true" show_button="true" donors_per_page="5"]
   ```

### Method 3: Import JSON Template (Advanced)

If you have Elementor Pro, you can create a template using the `givewp_get_elementor_campaign_template()` function data structure and import it as a JSON template.

## Responsive Design

The template includes responsive CSS that:
- Stacks columns vertically on mobile devices (< 768px)
- Maintains proper spacing and readability
- Preserves the 16:9 aspect ratio for campaign images

## Customization Options

### Styling
You can customize the appearance by:
1. Modifying the CSS classes in the template
2. Using Elementor's built-in styling options
3. Adding custom CSS to your theme

### Layout
- Adjust column widths by changing the section structure
- Modify spacing using Elementor's spacing controls
- Reorder sections as needed

### Content
- Change the number of donations/donors displayed
- Modify button text and styling
- Add additional sections or content

## Campaign Stats Dynamic Loading

The template includes placeholder JavaScript for loading dynamic campaign statistics. To implement this:

1. Create an AJAX endpoint to fetch campaign stats
2. Replace the placeholder JavaScript with actual API calls
3. Use `wp_localize_script()` to pass data from PHP to JavaScript

Example implementation:
```php
// In your functions.php or plugin file
add_action('wp_ajax_get_campaign_stats', 'handle_campaign_stats_ajax');
add_action('wp_ajax_nopriv_get_campaign_stats', 'handle_campaign_stats_ajax');

function handle_campaign_stats_ajax() {
    $campaign_id = intval($_POST['campaign_id']);
    // Fetch and return campaign statistics
    wp_die();
}
```

## Troubleshooting

### Shortcodes Not Working
- Ensure the campaign ID is valid and the campaign is published
- Check that GiveWP is active and updated
- Verify shortcode syntax is correct

### Layout Issues
- Check Elementor column settings
- Verify CSS is loading properly
- Test on different screen sizes

### Missing Data
- Confirm campaign has donations/donors to display
- Check campaign goal settings are enabled
- Verify campaign image is set

## Support

For support with this template:
1. Check the [GiveWP Documentation](https://givewp.com/documentation/)
2. Review the [Elementor Documentation](https://elementor.com/help/)
3. Contact GiveWP support for campaign-specific issues
