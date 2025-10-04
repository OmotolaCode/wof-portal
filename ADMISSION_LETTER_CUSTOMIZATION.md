# Admission Letter Customization Guide

## Overview
This guide explains how to customize the admission letter template for the Whoba Ogo Foundation ICT Training System.

## File Location
The admission letter template is located at:
```
/project/admission_letter.php
```

## Customizable Elements

### 1. Organization Contact Information
**Location:** Lines 91-96 in the HTML header section

```php
<div class="contact-info">
    ICT Training Center, Warri, Delta State<br>
    Email: info@whobaogofoundation.org | Phone: +234-XXX-XXX-XXXX
</div>
```

**What to update:**
- Physical address
- Email address
- Phone number

### 2. Student Number Format
**Location:** Line 28

```php
$student_number = 'WOF/ICT/C4/WB/' . substr(md5($application['user_id']), 0, 6);
```

**Current format:** WOF/ICT/C4/WB/XXXXXX
- WOF = Whoba Ogo Foundation
- ICT = ICT Training
- C4 = Cohort/Category
- WB = Warri Branch (or other branch code)
- XXXXXX = Unique 6-digit identifier

**To customize:**
- Change the prefix to match your organization
- Adjust the unique ID length
- Add additional identifiers as needed

### 3. Reference Number Format
**Location:** Line 60

```php
$ref_number = 'WOF/ADM/' . date('Y') . '/' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
```

**Current format:** WOF/ADM/YYYY/NNNN
- WOF = Organization code
- ADM = Admission
- YYYY = Current year
- NNNN = Random 4-digit number

### 4. Logo
**Location:** Line 86

```html
<img src="images/wof_logo_png.png" alt="WOF Logo" class="logo" onerror="this.style.display='none'">
```

**To update:**
- Replace `images/wof_logo_png.png` with your logo file path
- Recommended size: 100px width, maintains aspect ratio

### 5. Organization Name and Tagline
**Location:** Lines 88-89

```html
<div class="org-name">Whoba Ogo Foundation</div>
<div class="tagline">Tuition-Free ICT Training Program</div>
```

### 6. Letter Content

#### Greeting Section
**Location:** Lines 112-116

You can customize the congratulatory message and introduction.

#### Admission Details Box
**Location:** Lines 120-127

The details box displays:
- Student Number
- Course of Study
- Cohort
- Duration
- Training Commencement Date
- Class Session

These are automatically populated from the database.

#### Requirements Section
**Location:** Lines 131-141

The list of required documents can be customized:

```html
<ol>
    <li><strong>Completed Guarantor's Form</strong></li>
    <li>Photocopy of your <strong>highest educational qualification</strong></li>
    <li><strong>Two (2) recent passport photographs</strong></li>
    <li>Photocopy of a <strong>valid means of identification</strong></li>
    <li>Photocopy of the <strong>Guarantor's valid means of identification</strong></li>
</ol>
```

Add or remove items as needed for your organization's requirements.

### 7. Signature Section
**Location:** Lines 158-161

```html
<div class="signature-line"></div>
<div class="signature-name">Foundation Director</div>
<div class="signature-title">Whoba Ogo Foundation ICT Training Center</div>
```

**To customize:**
- Change the signatory's title
- Update the organization name
- Add additional signature lines if needed

### 8. Footer Information
**Location:** Lines 164-168

```html
<div class="footer-note">
    This is an official admission letter from Whoba Ogo Foundation ICT Training Center.<br>
    Please keep this letter safe as it may be required for verification purposes.<br>
    For inquiries, contact us at info@whobaogofoundation.org
</div>
```

## Styling Customization

### Color Scheme
The current color scheme uses:
- Primary Blue: `#1e40af` (for headers and accents)
- Secondary Green: `#16a34a` (for tagline)
- Light Blue Background: `#f0f9ff` (for details box)

To change colors, search for these hex codes and replace them throughout the file.

### Font Settings
**Location:** Lines 19-22 in the CSS

```css
body {
    font-family: "Times New Roman", Times, serif;
    font-size: 12pt;
    line-height: 1.8;
}
```

**Professional alternatives:**
- Formal: "Georgia", "Garamond"
- Modern: "Arial", "Helvetica"
- Traditional: "Times New Roman", "Baskerville"

### Page Margins
**Location:** Line 9 in the CSS

```css
@page {
    margin: 2cm 1.5cm;
}
```

Adjust margins to fit your organization's letterhead or printing requirements.

## Course Name Mapping

To add new courses or modify course names:

**Location:** Lines 30-41

```php
$course_mapping = [
    'desktop_publishing' => 'Desktop Publishing',
    'graphics_design_ui_ux' => 'Graphics Design - UI/UX',
    // Add new courses here
];
```

## Session Time Mapping

To modify session times:

**Location:** Lines 47-51

```php
$session_mapping = [
    'morning_10_12' => 'Morning (10:00 AM - 12:00 PM)',
    'afternoon_230_430' => 'Afternoon (2:30 PM - 4:30 PM)',
    'weekends_8_1' => 'Weekends only (8:00 AM - 1:00 PM)'
];
```

## Testing Your Changes

After making customizations:

1. **Test with a sample application:**
   - Use a test student account with an approved application
   - Click "View & Download Admission Letter"
   - Verify all customizations appear correctly

2. **Test printing:**
   - Click the "Download/Print as PDF" button
   - Check the PDF output for formatting issues
   - Ensure all text is visible and properly aligned

3. **Test on different browsers:**
   - Chrome
   - Firefox
   - Safari
   - Edge

4. **Mobile compatibility:**
   - Test on mobile devices if students will view on phones/tablets

## Backup Recommendation

Before making major changes:
1. Create a backup copy of the original file
2. Save it as `admission_letter_backup.php`
3. Test changes thoroughly before going live

## Support

For technical assistance with customization, contact your system administrator or developer.

---

**Last Updated:** October 2025
**Version:** 1.0
