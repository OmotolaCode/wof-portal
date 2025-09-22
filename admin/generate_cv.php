<?php 
require_once '../classes/Database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireAdmin();

$db = new Database();

if(!isset($_GET['user_id'])) {
    header('Location: graduates.php');
    exit();
}

$user_id = $_GET['user_id'];

// Get user details with profile and certification
$db->query('SELECT u.*, gp.*, gc.certificate_number, gc.certification_level, gc.skills_verified, gc.certification_date,
           (SELECT c.name FROM enrollments e JOIN cohorts c ON e.cohort_id = c.id WHERE e.user_id = u.id AND e.status = "completed" LIMIT 1) as completed_cohort
           FROM users u 
           LEFT JOIN graduate_profiles gp ON u.id = gp.user_id 
           LEFT JOIN graduate_certifications gc ON u.id = gc.user_id
           WHERE u.id = :user_id AND u.status = "graduated"');
$db->bind(':user_id', $user_id);
$user = $db->single();

if(!$user) {
    header('Location: graduates.php');
    exit();
}

// Get examination results
$db->query('SELECT e.*, c.name as cohort_name FROM examinations e 
           JOIN cohorts c ON e.cohort_id = c.id 
           WHERE e.user_id = :user_id AND e.status = "passed" 
           ORDER BY e.exam_date DESC');
$db->bind(':user_id', $user_id);
$examinations = $db->resultSet();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV - <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Print Controls -->
    <div class="no-print bg-white shadow-sm border-b p-4 mb-6">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-900">International Standard CV</h1>
            <div class="space-x-3">
                <button onclick="window.print()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-print mr-2"></i>Print CV
                </button>
                <a href="graduates.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Graduates
                </a>
            </div>
        </div>
    </div>

    <!-- CV Content -->
    <div class="max-w-4xl mx-auto bg-white shadow-lg">
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary to-secondary text-white p-8">
            <div class="flex items-center space-x-6">
                <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-3xl font-bold">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold mb-2">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </h1>
                    <?php if($user['title']): ?>
                        <p class="text-xl text-blue-100 mb-2"><?php echo htmlspecialchars($user['title']); ?></p>
                    <?php endif; ?>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <span><i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($user['email']); ?></span>
                        <?php if($user['phone']): ?>
                            <span><i class="fas fa-phone mr-1"></i><?php echo htmlspecialchars($user['phone']); ?></span>
                        <?php endif; ?>
                        <?php if($user['nationality']): ?>
                            <span><i class="fas fa-flag mr-1"></i><?php echo htmlspecialchars($user['nationality']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8">
            <!-- Professional Summary -->
            <?php if($user['bio']): ?>
                <section class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">Professional Summary</h2>
                    <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                </section>
            <?php endif; ?>

            <!-- WOF Certification -->
            <?php if($user['certificate_number']): ?>
                <section class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">Whoba Ogo Foundation Certification</h2>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-green-800">Certified Graduate</h3>
                            <span class="text-sm text-green-600">Certificate #<?php echo htmlspecialchars($user['certificate_number']); ?></span>
                        </div>
                        <p class="text-green-700 mb-2">
                            <strong>Program:</strong> <?php echo htmlspecialchars($user['completed_cohort']); ?>
                        </p>
                        <p class="text-green-700 mb-2">
                            <strong>Certification Level:</strong> <?php echo ucfirst($user['certification_level']); ?>
                        </p>
                        <p class="text-green-700 mb-2">
                            <strong>Certification Date:</strong> <?php echo date('F j, Y', strtotime($user['certification_date'])); ?>
                        </p>
                        <?php if($user['skills_verified']): ?>
                            <p class="text-green-700">
                                <strong>Verified Skills:</strong> <?php echo htmlspecialchars($user['skills_verified']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Skills -->
            <?php if($user['skills']): ?>
                <section class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">Technical Skills</h2>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        $skills = explode(',', $user['skills']);
                        foreach($skills as $skill): 
                            $skill = trim($skill);
                            if($skill):
                        ?>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                <?php echo htmlspecialchars($skill); ?>
                            </span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Education & Training -->
            <section class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">Education & Training</h2>
                
                <!-- WOF Training -->
                <div class="mb-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['completed_cohort']); ?></h3>
                            <p class="text-primary font-medium">Whoba Ogo Foundation</p>
                        </div>
                        <span class="text-sm text-gray-600">2024-2025</span>
                    </div>
                    <p class="text-gray-700 text-sm">
                        Comprehensive training program focused on practical skills development and real-world application.
                        Graduated with <?php echo ucfirst($user['certification_level']); ?> level certification.
                    </p>
                </div>

                <!-- Additional Education -->
                <?php if($user['education_history']): ?>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h4 class="font-medium text-gray-900 mb-2">Additional Education</h4>
                        <div class="text-gray-700 text-sm">
                            <?php echo nl2br(htmlspecialchars($user['education_history'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Work Experience -->
            <?php if($user['work_history']): ?>
                <section class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">Work Experience</h2>
                    <div class="text-gray-700">
                        <?php echo nl2br(htmlspecialchars($user['work_history'])); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Examination Results -->
            <?php if(!empty($examinations)): ?>
                <section class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">Assessment Results</h2>
                    <div class="space-y-3">
                        <?php foreach($examinations as $exam): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900"><?php echo ucfirst($exam['exam_type']); ?> Examination</h4>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($exam['cohort_name']); ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-green-600">
                                        <?php echo $exam['score']; ?>/<?php echo $exam['total_marks']; ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('M Y', strtotime($exam['exam_date'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Additional Certifications -->
            <?php if($user['certifications']): ?>
                <section class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">Additional Certifications</h2>
                    <div class="text-gray-700">
                        <?php echo nl2br(htmlspecialchars($user['certifications'])); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Languages -->
            <?php if($user['languages']): ?>
                <section class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">Languages</h2>
                    <div class="text-gray-700">
                        <?php echo nl2br(htmlspecialchars($user['languages'])); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Achievements -->
            <?php if($user['achievements']): ?>
                <section class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">Achievements</h2>
                    <div class="text-gray-700">
                        <?php echo nl2br(htmlspecialchars($user['achievements'])); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- References -->
            <?php if($user['references']): ?>
                <section class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">References</h2>
                    <div class="text-gray-700">
                        <?php echo nl2br(htmlspecialchars($user['references'])); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Professional Links -->
            <section class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 border-b-2 border-primary pb-2 mb-4">Professional Links</h2>
                <div class="space-y-2">
                    <?php if($user['linkedin_url']): ?>
                        <div class="flex items-center">
                            <i class="fab fa-linkedin text-blue-600 mr-3"></i>
                            <a href="<?php echo htmlspecialchars($user['linkedin_url']); ?>" class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($user['linkedin_url']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($user['portfolio_url']): ?>
                        <div class="flex items-center">
                            <i class="fas fa-globe text-gray-600 mr-3"></i>
                            <a href="<?php echo htmlspecialchars($user['portfolio_url']); ?>" class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($user['portfolio_url']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 p-6 text-center border-t">
            <div class="flex items-center justify-center space-x-4 mb-2">
                <img src="../images/wof_logo.png" alt="WOF" class="h-8">
                <div class="text-sm text-gray-600">
                    <strong>Whoba Ogo Foundation</strong> - Certified Graduate
                </div>
            </div>
            <p class="text-xs text-gray-500">
                This CV is generated by Whoba Ogo Foundation's Graduate Management System. 
                For verification, contact: info@whobaogo.org
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Generated on <?php echo date('F j, Y'); ?>
            </p>
        </div>
    </div>
</body>
</html>