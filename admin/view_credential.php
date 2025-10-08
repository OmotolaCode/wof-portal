<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireAdmin();

if(!isset($_GET['id'])) {
    header('Location: applications.php');
    exit();
}

$application_id = $_GET['id'];
$db = new Database();

// Get application with credential info
$db->query('SELECT credential_filename, credential_file_type, user_id FROM applications WHERE id = :id');
$db->bind(':id', $application_id);
$application = $db->single();

if(!$application || !$application['credential_filename']) {
    die('Credential not found');
}

$file_path = '../uploads/credentials/' . $application['credential_filename'];

if(!file_exists($file_path)) {
    die('File not found');
}

$file_ext = strtolower(pathinfo($application['credential_filename'], PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Credential - WOF Training Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }
        .viewer-container {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .viewer-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .viewer-content {
            flex: 1;
            overflow: auto;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .image-viewer {
            max-width: 100%;
            height: auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            background: white;
        }
        .pdf-viewer {
            width: 100%;
            height: calc(100vh - 80px);
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="viewer-container">
        <div class="viewer-header">
            <div>
                <h1 class="text-xl font-bold">
                    <i class="fas fa-file-alt mr-2"></i>Student Credential Document
                </h1>
                <p class="text-sm text-blue-100">Application ID: <?php echo htmlspecialchars($application_id); ?></p>
            </div>
            <div class="flex gap-3">
                <a href="<?php echo $file_path; ?>" download
                   class="bg-white text-primary px-4 py-2 rounded-lg hover:bg-gray-100 transition flex items-center">
                    <i class="fas fa-download mr-2"></i>Download
                </a>
                <button onclick="window.close()"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center">
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        </div>

        <div class="viewer-content">
            <?php if($file_ext === 'pdf'): ?>
                <iframe src="<?php echo $file_path; ?>" class="pdf-viewer"></iframe>
            <?php else: ?>
                <img src="<?php echo $file_path; ?>" alt="Credential Document" class="image-viewer">
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
