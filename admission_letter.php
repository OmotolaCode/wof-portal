<?php
require_once 'classes/Database.php';

$auth->requireAuth();

if (!isset($_GET['application_id'])) {
    header('Location: dashboard.php');
    exit();
}

$db = new Database();
$application_id = $_GET['application_id'];

$db->query('SELECT a.*, c.name as cohort_name, c.course_type, c.start_date, c.duration_months, u.id as user_id
           FROM applications a
           JOIN cohorts c ON a.cohort_id = c.id
           JOIN users u ON a.user_id = u.id
           WHERE a.id = :application_id AND a.user_id = :user_id AND a.status = "approved"');
$db->bind(':application_id', $application_id);
$db->bind(':user_id', $_SESSION['user_id']);
$application = $db->single();

if (!$application) {
    header('Location: dashboard.php?error=invalid_application');
    exit();
}

$student_number = 'WOF/ICT/C4/WB/' . substr(md5($application['user_id']), 0, 6);

$course_mapping = [
    'desktop_publishing' => 'Desktop Publishing',
    'graphics_design_ui_ux' => 'Graphics Design - UI/UX',
    'web_design' => 'Web Design',
    'digital_marketing' => 'Digital Marketing/Content Creation',
    'photography_video_editing' => 'Photography/Video Editing',
    'frontend_development' => 'Front-End Software Development',
    'backend_development' => 'Back-End Software Development',
    'fullstack_development' => 'Fullstack Software Development',
    'mobile_app_development' => 'Mobile App Development',
    'data_analytics' => 'Data Analytics'
];

$course_name = isset($course_mapping[$application['course_choice']])
    ? $course_mapping[$application['course_choice']]
    : $application['course_choice'];

$session_mapping = [
    'morning_10_12' => 'Morning (10:00 AM - 12:00 PM)',
    'afternoon_230_430' => 'Afternoon (2:30 PM - 4:30 PM)',
    'weekends_8_1' => 'Weekends only (8:00 AM - 1:00 PM)'
];

$session_name = isset($session_mapping[$application['preferred_session']])
    ? $session_mapping[$application['preferred_session']]
    : $application['preferred_session'];

$issue_date = date('F j, Y');
$training_start_date = date('F j, Y', strtotime($application['start_date']));
$ref_number = 'WOF/ADM/' . date('Y') . '/' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Letter - <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['surname']); ?></title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            @page {
                margin: 2cm 1.5cm;
            }
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.8;
            color: #000;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 20px;
        }

        .logo-section {
            margin-bottom: 15px;
        }

        .logo {
            max-width: 100px;
            height: auto;
        }

        .org-name {
            font-size: 20pt;
            font-weight: bold;
            color: #1e40af;
            margin: 10px 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .tagline {
            font-size: 11pt;
            font-style: italic;
            color: #16a34a;
            margin: 5px 0;
        }

        .contact-info {
            font-size: 10pt;
            margin-top: 10px;
            color: #333;
        }

        .letter-title {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 30px 0 20px 0;
            text-transform: uppercase;
        }

        .ref-section {
            font-size: 10pt;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .address-section {
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .content {
            text-align: justify;
            margin-bottom: 20px;
        }

        .content p {
            margin: 15px 0;
        }

        .highlight {
            font-weight: bold;
        }

        .details-box {
            border: 2px solid #1e40af;
            padding: 15px;
            margin: 20px 0;
            background-color: #f0f9ff;
        }

        .details-box p {
            margin: 8px 0;
            line-height: 1.8;
        }

        .requirements {
            margin: 20px 0;
        }

        .requirements ol {
            margin-left: 20px;
            line-height: 2;
        }

        .requirements li {
            margin: 8px 0;
        }

        .signature-section {
            margin-top: 50px;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 2px solid #000;
            width: 250px;
            display: inline-block;
        }

        .signature-name {
            font-weight: bold;
            margin-top: 5px;
        }

        .signature-title {
            font-style: italic;
            color: #555;
        }

        .footer-note {
            margin-top: 40px;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 15px;
            font-style: italic;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1e40af;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .print-button:hover {
            background: #1e3a8a;
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #16a34a;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: inline-block;
        }

        .back-button:hover {
            background: #15803d;
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-button no-print">Back to Dashboard</a>
    <button onclick="window.print()" class="print-button no-print">Download/Print as PDF</button>

    <div class="header">
        <div class="logo-section">
            <img src="images/wof_logo_png.png" alt="WOF Logo" class="logo" onerror="this.style.display='none'">
        </div>
        <div class="org-name">Whoba Ogo Foundation</div>
        <div class="tagline">Tuition-Free ICT Training Program</div>
        <div class="contact-info">
            ICT Training Center, Warri, Delta State<br>
            Email: info@whobaogofoundation.org | Phone: +234-XXX-XXX-XXXX
        </div>
    </div>

    <div class="ref-section">
        <strong>Ref:</strong> <?php echo htmlspecialchars($ref_number); ?><br>
        <strong>Date:</strong> <?php echo $issue_date; ?>
    </div>

    <div class="address-section">
        <strong><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['surname']); ?></strong><br>
        <?php echo nl2br(htmlspecialchars($application['contact_address'])); ?>
    </div>

    <div class="letter-title">
        Letter of Admission
    </div>

    <div class="content">
        <p>Dear <span class="highlight"><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['surname']); ?></span>,</p>

        <p style="text-align: center; font-size: 14pt;"><span class="highlight">CONGRATULATIONS!</span></p>

        <p>On behalf of the Board of Directors, Management, and Staff of Whoba Ogo Foundation ICT Training Center,
        I am pleased to inform you that you have been offered <span class="highlight">ADMISSION</span> into our Tuition-Free ICT Training Program.</p>

        <p><span class="highlight" style="font-size: 13pt;">ADMISSION DETAILS:</span></p>

        <div class="details-box">
            <p><span class="highlight">Student Number:</span> <?php echo htmlspecialchars($student_number); ?></p>
            <p><span class="highlight">Course of Study:</span> <?php echo htmlspecialchars($course_name); ?></p>
            <p><span class="highlight">Cohort:</span> <?php echo htmlspecialchars($application['cohort_name']); ?></p>
            <p><span class="highlight">Duration:</span> <?php echo $application['duration_months']; ?> months</p>
            <p><span class="highlight">Training Commencement Date:</span> <?php echo $training_start_date; ?></p>
            <p><span class="highlight">Class Session:</span> <?php echo htmlspecialchars($session_name); ?></p>
        </div>

        <p><span class="highlight" style="font-size: 13pt;">REQUIREMENTS FOR RESUMPTION:</span></p>

        <div class="requirements">
            <p>To complete your enrollment, you are required to submit the following documents on or before your resumption date:</p>
            <ol>
                <li><strong>Completed Guarantor's Form</strong> (available for download from your student dashboard)</li>
                <li>Photocopy of your <strong>highest educational qualification</strong></li>
                <li><strong>Two (2) recent passport photographs</strong></li>
                <li>Photocopy of a <strong>valid means of identification</strong> (National ID, Driver's License, or International Passport)</li>
                <li>Photocopy of the <strong>Guarantor's valid means of identification</strong></li>
            </ol>
        </div>

        <p>This admission is subject to your full compliance with the Foundation's rules and regulations,
        as well as maintaining satisfactory academic progress throughout the training period.</p>

        <p>Please note that the training is entirely <span class="highlight">FREE OF CHARGE</span>, and includes access to modern
        computer facilities, learning materials, and professional instructors.</p>

        <p>We look forward to welcoming you to the Whoba Ogo Foundation family and supporting you on your journey
        to becoming a highly skilled ICT professional.</p>

        <p><span class="highlight">Once again, congratulations on your admission!</span></p>
    </div>

    <div class="signature-section">
        <p>Yours faithfully,</p>
        <div class="signature-line"></div>
        <div class="signature-name">Foundation Director</div>
        <div class="signature-title">Whoba Ogo Foundation ICT Training Center</div>
    </div>

    <div class="footer-note">
        This is an official admission letter from Whoba Ogo Foundation ICT Training Center.<br>
        Please keep this letter safe as it may be required for verification purposes.<br>
        For inquiries, contact us at info@whobaogofoundation.org
    </div>
</body>
</html>
