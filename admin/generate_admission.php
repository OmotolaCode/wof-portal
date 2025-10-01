<?php
require_once '../classes/Database.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$auth = new Auth();
$auth->requireAdmin();

$db = new Database();

$enrollment_id = isset($_GET['enrollment_id']) ? $_GET['enrollment_id'] : null;

if(!$enrollment_id) {
    header('Location: enrollments.php');
    exit();
}

$db->query('SELECT e.*, u.first_name, u.last_name, u.email, u.phone,
           c.name as cohort_name, c.course_type, c.start_date, c.end_date, c.duration_months,
           a.surname, a.other_names, a.gender, a.date_of_birth, a.contact_address, a.state_of_origin
           FROM enrollments e
           JOIN users u ON e.user_id = u.id
           JOIN cohorts c ON e.cohort_id = c.id
           LEFT JOIN applications a ON e.application_id = a.id
           WHERE e.id = :id');
$db->bind(':id', $enrollment_id);
$enrollment = $db->single();

if(!$enrollment) {
    header('Location: enrollments.php');
    exit();
}

if($enrollment['admission_letter_generated']) {
    header('Location: enrollments.php?msg=already_generated');
    exit();
}

$letter_number = 'WOF/ADM/' . date('Y') . '/' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

$student_name = $enrollment['surname'] . ' ' . $enrollment['first_name'];
if($enrollment['other_names']) {
    $student_name .= ' ' . $enrollment['other_names'];
}

$course_name = str_replace('_', ' ', ucwords($enrollment['course_type'], '_'));

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            line-height: 1.6;
            color: #333;
        }
        .letterhead {
            text-align: center;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .tagline {
            font-size: 14px;
            color: #059669;
            font-style: italic;
        }
        .letter-number {
            text-align: right;
            margin-bottom: 30px;
            font-size: 12px;
            color: #666;
        }
        .date {
            text-align: right;
            margin-bottom: 30px;
        }
        .recipient {
            margin-bottom: 30px;
        }
        .subject {
            font-weight: bold;
            text-decoration: underline;
            margin: 30px 0 20px 0;
            text-align: center;
        }
        .content {
            text-align: justify;
            margin-bottom: 20px;
        }
        .details-box {
            border: 2px solid #1e40af;
            padding: 20px;
            margin: 30px 0;
            background-color: #f0f9ff;
        }
        .details-title {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .detail-row {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            display: inline-block;
            width: 180px;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature {
            margin-top: 60px;
            border-top: 1px solid #000;
            width: 250px;
            text-align: center;
            padding-top: 5px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #1e40af;
            font-size: 11px;
            text-align: center;
            color: #666;
        }
        .congratulations {
            background-color: #dcfce7;
            border-left: 4px solid #059669;
            padding: 15px;
            margin: 20px 0;
            font-weight: bold;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <div class="logo">WHOBA OGO FOUNDATION</div>
        <div class="tagline">Empowering Lives Through Quality Education & Skills Training</div>
        <div style="font-size: 11px; margin-top: 10px;">
            Training Center, Osogbo, Osun State, Nigeria<br>
            Email: admissions@whobaogo.org | Phone: +234 XXX XXX XXXX
        </div>
    </div>

    <div class="letter-number">
        Ref: ' . htmlspecialchars($letter_number) . '
    </div>

    <div class="date">
        ' . date('jS F, Y') . '
    </div>

    <div class="recipient">
        <strong>' . htmlspecialchars($student_name) . '</strong><br>
        ' . htmlspecialchars($enrollment['contact_address'] ?? '') . '
    </div>

    <div class="subject">
        ADMISSION LETTER
    </div>

    <div class="congratulations">
        🎉 CONGRATULATIONS! 🎉
    </div>

    <div class="content">
        Dear ' . htmlspecialchars($enrollment['first_name']) . ',
    </div>

    <div class="content">
        We are pleased to inform you that you have been offered admission into the <strong>' . htmlspecialchars($cohort_name) . '</strong>
        for the <strong>' . htmlspecialchars($course_name) . '</strong> training program at Whoba Ogo Foundation.
    </div>

    <div class="content">
        After careful review of your application, we are confident that you will be an excellent addition to our learning community.
        This program has been designed to equip you with industry-relevant skills and practical knowledge that will position you for success in the digital economy.
    </div>

    <div class="details-box">
        <div class="details-title">ADMISSION DETAILS</div>
        <div class="detail-row">
            <span class="detail-label">Student Name:</span>
            <span>' . htmlspecialchars($student_name) . '</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Program:</span>
            <span>' . htmlspecialchars($course_name) . '</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Cohort:</span>
            <span>' . htmlspecialchars($enrollment['cohort_name']) . '</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Duration:</span>
            <span>' . $enrollment['duration_months'] . ' Months</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Start Date:</span>
            <span>' . date('jS F, Y', strtotime($enrollment['start_date'])) . '</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">End Date:</span>
            <span>' . date('jS F, Y', strtotime($enrollment['end_date'])) . '</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Admission Number:</span>
            <span>' . htmlspecialchars($letter_number) . '</span>
        </div>
    </div>

    <div class="content">
        <strong>What to Expect:</strong><br>
        • Comprehensive training from industry experts<br>
        • Hands-on practical sessions and real-world projects<br>
        • Access to modern training facilities and equipment<br>
        • Career guidance and job placement support<br>
        • Certificate of completion upon successful program completion
    </div>

    <div class="content">
        <strong>Next Steps:</strong><br>
        1. Print and keep this admission letter for your records<br>
        2. Report to the training center on the specified start date<br>
        3. Come prepared with a notebook, pen, and enthusiasm to learn<br>
        4. For any questions, contact us via the details provided above
    </div>

    <div class="content">
        We look forward to welcoming you to Whoba Ogo Foundation and supporting you on your journey to acquiring valuable skills that will transform your career prospects.
    </div>

    <div class="content">
        Once again, congratulations on your admission!
    </div>

    <div class="signature-section">
        Yours faithfully,
        <div class="signature">
            <strong>Director of Training</strong><br>
            Whoba Ogo Foundation
        </div>
    </div>

    <div class="footer">
        <strong>Whoba Ogo Foundation</strong> - A registered non-profit organization dedicated to empowering Nigerian youths through quality education and skills training.
        <br>
        This is an official admission letter. Please keep it safe for future reference.
    </div>
</body>
</html>
';

$upload_dir = '../uploads/admission_letters/';
if(!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'admission_' . $enrollment['user_id'] . '_' . time() . '.pdf';
$filepath = $upload_dir . $filename;
file_put_contents($filepath, $dompdf->output());

$db->query('UPDATE enrollments SET
           admission_letter_generated = 1,
           admission_letter_filename = :filename,
           admission_letter_generated_at = NOW()
           WHERE id = :id');
$db->bind(':filename', $filename);
$db->bind(':id', $enrollment_id);
$db->execute();

$db->query('INSERT INTO admission_letters (application_id, user_id, cohort_id, letter_number, letter_filename, generated_by)
           VALUES (:app_id, :user_id, :cohort_id, :letter_number, :filename, :generated_by)');
$db->bind(':app_id', $enrollment['application_id']);
$db->bind(':user_id', $enrollment['user_id']);
$db->bind(':cohort_id', $enrollment['cohort_id']);
$db->bind(':letter_number', $letter_number);
$db->bind(':filename', $filename);
$db->bind(':generated_by', $_SESSION['user_id']);
$db->execute();

$dompdf->stream($filename, ['Attachment' => false]);
exit();
?>
