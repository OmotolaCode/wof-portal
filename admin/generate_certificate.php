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
    header('Location: graduates.php');
    exit();
}

$db->query('SELECT e.*, u.first_name, u.last_name, u.email,
           c.name as cohort_name, c.course_type, c.start_date, c.end_date, c.duration_months,
           a.surname, a.other_names, a.gender
           FROM enrollments e
           JOIN users u ON e.user_id = u.id
           JOIN cohorts c ON e.cohort_id = c.id
           LEFT JOIN applications a ON e.application_id = a.id
           WHERE e.id = :id AND e.status = "completed"');
$db->bind(':id', $enrollment_id);
$enrollment = $db->single();

if(!$enrollment) {
    header('Location: graduates.php?error=invalid_enrollment');
    exit();
}

$db->query('SELECT * FROM examinations WHERE enrollment_id = :id ORDER BY exam_date DESC LIMIT 1');
$db->bind(':id', $enrollment_id);
$exam = $db->single();

if(!$exam || $exam['percentage'] < 70) {
    header('Location: graduates.php?error=insufficient_grade');
    exit();
}

$db->query('SELECT * FROM certificates WHERE enrollment_id = :id');
$db->bind(':id', $enrollment_id);
$existing_cert = $db->single();

if($existing_cert) {
    header('Location: graduates.php?msg=already_generated');
    exit();
}

$cert_number = 'WOF/CERT/' . date('Y') . '/' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

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
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: "Georgia", serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
        }
        .certificate-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: url("data:image/svg+xml,%3Csvg width=\'100%\' height=\'100%\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cdefs%3E%3Cpattern id=\'grid\' x=\'0\' y=\'0\' width=\'40\' height=\'40\' patternUnits=\'userSpaceOnUse\'%3E%3Crect fill=\'none\' x=\'0\' y=\'0\' width=\'40\' height=\'40\'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width=\'100%25\' height=\'100%25\' fill=\'%23ffffff\'/%3E%3C/svg%3E");
        }
        .border-ornament {
            position: absolute;
            border: 25px solid transparent;
            border-image: linear-gradient(135deg, #667eea, #764ba2, #f093fb, #f5576c) 1;
            width: calc(100% - 80px);
            height: calc(100% - 80px);
            top: 40px;
            left: 40px;
        }
        .inner-border {
            position: absolute;
            border: 4px solid #fbbf24;
            width: calc(100% - 100px);
            height: calc(100% - 100px);
            top: 50px;
            left: 50px;
        }
        .corner-flourish {
            position: absolute;
            width: 80px;
            height: 80px;
            background: radial-gradient(circle, #fbbf24, transparent);
            border-radius: 50%;
            opacity: 0.3;
        }
        .corner-tl { top: 40px; left: 40px; }
        .corner-tr { top: 40px; right: 40px; }
        .corner-bl { bottom: 40px; left: 40px; }
        .corner-br { bottom: 40px; right: 40px; }
        .content {
            position: relative;
            z-index: 10;
            padding: 100px 120px;
            text-align: center;
        }
        .header {
            margin-bottom: 30px;
        }
        .logo-section {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-center;
            font-size: 48px;
            font-weight: bold;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .organization {
            font-size: 38px;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 3px;
            margin-bottom: 8px;
            text-transform: uppercase;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .tagline {
            font-size: 14px;
            color: #64748b;
            font-style: italic;
            letter-spacing: 1px;
        }
        .divider {
            width: 200px;
            height: 3px;
            background: linear-gradient(to right, transparent, #fbbf24, transparent);
            margin: 25px auto;
        }
        .certificate-title {
            font-size: 48px;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 8px;
            margin: 30px 0;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .subtitle {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 40px;
            letter-spacing: 2px;
        }
        .recipient-intro {
            font-size: 18px;
            color: #475569;
            margin-bottom: 20px;
            font-style: italic;
        }
        .recipient-name {
            font-size: 52px;
            font-weight: bold;
            color: #1e293b;
            margin: 30px 0;
            font-family: "Brush Script MT", cursive;
            border-bottom: 3px solid #fbbf24;
            display: inline-block;
            padding: 0 40px 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .achievement-text {
            font-size: 18px;
            line-height: 1.8;
            color: #475569;
            margin: 30px auto;
            max-width: 700px;
        }
        .course-name {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            margin: 25px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .performance-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 20px;
            font-weight: bold;
            margin: 25px 0;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }
        .footer-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
        }
        .signature-block {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 2px solid #1e293b;
            margin-top: 60px;
            padding-top: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }
        .signature-title {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }
        .certificate-details {
            position: absolute;
            bottom: 60px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
        .cert-number {
            font-weight: bold;
            color: #64748b;
            letter-spacing: 1px;
        }
        .seal {
            position: absolute;
            bottom: 120px;
            left: 100px;
            width: 120px;
            height: 120px;
            border: 8px solid #fbbf24;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
            line-height: 1.3;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transform: rotate(-15deg);
        }
        .excellence-badge {
            position: absolute;
            top: 80px;
            right: 100px;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.4);
        }
        .excellence-star {
            font-size: 32px;
            margin-bottom: 5px;
        }
        .excellence-text {
            font-size: 11px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="border-ornament"></div>
        <div class="inner-border"></div>

        <div class="corner-flourish corner-tl"></div>
        <div class="corner-flourish corner-tr"></div>
        <div class="corner-flourish corner-bl"></div>
        <div class="corner-flourish corner-br"></div>

        <div class="content">
            <div class="header">
                <div class="logo-section">WOF</div>
                <div class="organization">Whoba Ogo Foundation</div>
                <div class="tagline">Empowering Excellence Through Education</div>
            </div>

            <div class="divider"></div>

            <div class="certificate-title">CERTIFICATE</div>
            <div class="subtitle">OF ACHIEVEMENT</div>

            <div class="recipient-intro">This is to certify that</div>

            <div class="recipient-name">' . htmlspecialchars($student_name) . '</div>

            <div class="achievement-text">
                has successfully completed the comprehensive training program in
            </div>

            <div class="course-name">' . htmlspecialchars($course_name) . '</div>

            <div class="achievement-text">
                demonstrating exceptional dedication, skill mastery, and professional excellence throughout the <strong>' . $enrollment['duration_months'] . '-month intensive program</strong>.
            </div>

            <div class="performance-badge">
                ⭐ OUTSTANDING PERFORMANCE: ' . number_format($exam['percentage'], 1) . '% ⭐
            </div>

            <div class="footer-section">
                <div class="signature-block">
                    <div class="signature-line">Program Director</div>
                    <div class="signature-title">Whoba Ogo Foundation</div>
                </div>

                <div class="signature-block">
                    <div class="signature-line">Chief Executive Officer</div>
                    <div class="signature-title">Whoba Ogo Foundation</div>
                </div>
            </div>

            <div class="certificate-details">
                <div class="cert-number">Certificate No: ' . htmlspecialchars($cert_number) . '</div>
                <div>Issued on: ' . date('jS F, Y') . '</div>
                <div>Cohort: ' . htmlspecialchars($enrollment['cohort_name']) . ' | Duration: ' . date('M Y', strtotime($enrollment['start_date'])) . ' - ' . date('M Y', strtotime($enrollment['end_date'])) . '</div>
            </div>
        </div>

        <div class="seal">
            <div>
                OFFICIAL<br>
                SEAL<br>
                2025
            </div>
        </div>

        <div class="excellence-badge">
            <div class="excellence-star">★</div>
            <div class="excellence-text">Excellence</div>
        </div>
    </div>
</body>
</html>
';

$upload_dir = '../uploads/certificates/';
if(!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = 'certificate_' . $enrollment['user_id'] . '_' . time() . '.pdf';
$filepath = $upload_dir . $filename;
file_put_contents($filepath, $dompdf->output());

$db->query('INSERT INTO certificates (user_id, enrollment_id, examination_id, certificate_number, certificate_filename, course_name, is_released, generated_by)
           VALUES (:user_id, :enrollment_id, :exam_id, :cert_number, :filename, :course_name, 0, :generated_by)');
$db->bind(':user_id', $enrollment['user_id']);
$db->bind(':enrollment_id', $enrollment_id);
$db->bind(':exam_id', $exam['id']);
$db->bind(':cert_number', $cert_number);
$db->bind(':filename', $filename);
$db->bind(':course_name', $course_name);
$db->bind(':generated_by', $_SESSION['user_id']);
$db->execute();

$db->query('UPDATE users SET user_type = "graduate", user_status = "graduated" WHERE id = :id');
$db->bind(':id', $enrollment['user_id']);
$db->execute();

$dompdf->stream($filename, ['Attachment' => false]);
exit();
?>
