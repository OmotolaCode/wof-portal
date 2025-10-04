<?php
require_once 'classes/Database.php';

$auth->requireAuth();

$file_path = __DIR__ . '/documents/WOF ICT GUARANTOR\'S FORM (1).pdf';

if (file_exists($file_path)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="WOF_ICT_Guarantors_Form.pdf"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    readfile($file_path);
    exit();
} else {
    header('Location: dashboard.php?error=file_not_found');
    exit();
}
