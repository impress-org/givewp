# GiveWP Elementor Integration

This directory contains the complete Elementor integration for GiveWP campaigns, providing seamless switching between Elementor and WordPress editors.

## 🎯 **What It Does**

### **Automatic Template Setup**
When users create a campaign page and Elementor is active:
1. Page is automatically populated with Elementor template data
2. Uses proper Elementor meta keys (`_elementor_data`, `_elementor_edit_mode`)
3. Creates a beautiful two-column layout with campaign elements
4. Ready to edit immediately in Elementor

### **Smart Editor Switching**
When users click "← Back to WordPress Editor" in Elementor:
1. Detects the switch from Elementor to WordPress editor
2. Automatically restores original Gutenberg block content
3. Ensures users see proper campaign layout blocks, not empty content
4. Cleans up Elementor-specific meta data

## 📁 **Files**

### **Actions/SetupElementorCampaignTemplate.php**
- **Purpose**: Automatically populates campaign pages with Elementor template
- **Trigger**: `givewp_campaign_page_created` hook
- **Features**:
  - Detects if Elementor is active
  - Creates proper Elementor JSON structure
  - Uses native Elementor widgets (Featured Image)
  - Includes GiveWP shortcodes for campaign elements
  - Sets all required Elementor meta keys

### **Actions/RestoreGutenbergContentOnElementorExit.php**
- **Purpose**: Restores Gutenberg content when switching back from Elementor
- **Trigger**: `save_post` hook (when detecting Elementor mode change)
- **Features**:
  - Detects campaign pages specifically
  - Monitors Elementor switch mode requests
  - Generates original Gutenberg blocks
  - Updates post content seamlessly
  - Cleans up Elementor meta appropriately

## 🔄 **User Flow**

### **Creating a Campaign Page**
1. User creates campaign in GiveWP
2. User clicks "Create Campaign Page"
3. **Automatic**: Page populated with Elementor template
4. User opens in Elementor → Ready-to-edit layout

### **Switching Between Editors**
1. **Elementor → WordPress**:
   - Click "← Back to WordPress Editor"
   - **Automatic**: Original Gutenberg blocks restored
   - User sees proper campaign block layout

2. **WordPress → Elementor**:
   - Click "Edit with Elementor"
   - **Automatic**: Previous Elementor design loaded (if exists)
   - Or can start fresh with new template

## ⚙️ **Technical Details**

### **Template Structure**
```
Section 1: Two-column layout (60/40)
├── Left: Featured Image widget (native Elementor)
└── Right: Campaign elements
    ├── [givewp_campaign] - Goal display
    ├── [givewp_campaign_stats] - Statistics (future)
    └── [givewp_campaign_form] - Donate button

Section 2: Description (Text Editor widget)

Section 3: [givewp_campaign_donations] - Donations list

Section 4: [givewp_campaign_donors] - Donors list
```

### **Meta Keys Used**
- `_elementor_data`: JSON template structure
- `_elementor_edit_mode`: Set to `'builder'`
- `_elementor_template_type`: Set to `'page'`
- `_elementor_version`: Current Elementor version
- `_givewp_elementor_auto_template`: Custom tracking flag
- `_givewp_elementor_template_version`: Template version

### **Detection Logic**
- **Campaign Page**: Checks for `CampaignPageMetaKeys::CAMPAIGN_ID` meta
- **Mode Switch**: Monitors `$_POST['_elementor_post_mode']` changes
- **Nonce Verification**: Validates Elementor's nonce for security

## 🎛️ **Control Options**

### **Disable Automatic Setup**
```php
// Add to functions.php
add_filter('givewp_auto_setup_elementor_campaign_template', '__return_false');
```

### **Hook Priority**
Both actions run at default priority (10) but are designed to:
- Not conflict with each other
- Work with Elementor's own hooks
- Avoid infinite loops during saves

## 🚀 **Benefits**

### **For Users**
- ✅ **Zero Setup**: Campaign pages work immediately
- ✅ **Seamless Switching**: No lost content when switching editors
- ✅ **Professional Layout**: Beautiful design out of the box
- ✅ **Full Flexibility**: Complete Elementor editing capabilities

### **For Developers**
- ✅ **Clean Integration**: Proper WordPress/Elementor patterns
- ✅ **Robust Detection**: Smart switching logic
- ✅ **Future-Proof**: Extensible architecture
- ✅ **Safe Operations**: Prevents data loss

### **For Site Owners**
- ✅ **Faster Deployment**: Campaigns launch immediately
- ✅ **Consistent Experience**: Users don't get confused
- ✅ **Lower Support**: Fewer "empty page" issues
- ✅ **Professional Results**: Better-looking campaign pages

## 🔧 **Maintenance**

The solution is designed to be:
- **Self-Contained**: No external dependencies
- **Version Safe**: Compatible with Elementor updates
- **Performance Optimized**: Only runs when needed
- **Error Resistant**: Graceful fallbacks for edge cases

This integration provides the best of both worlds: the power of Elementor with the convenience of automatic GiveWP campaign layouts.
