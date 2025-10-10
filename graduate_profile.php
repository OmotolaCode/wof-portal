<?php
require_once 'classes/Database.php';
include 'includes/header.php';

$db = new Database();
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(!$user_id) {
    header('Location: showcase.php');
    exit;
}

// Get graduate profile with user details
$db->query('SELECT u.*, gp.*
           FROM users u
           LEFT JOIN graduate_profiles gp ON u.id = gp.user_id
           WHERE u.id = :user_id AND u.status = "graduated"');
$db->bind(':user_id', $user_id);
$graduate = $db->single();

if(!$graduate || !$graduate['is_visible']) {
    header('Location: showcase.php');
    exit;
}

// Get portfolio items
$db->query('SELECT * FROM portfolio_items
           WHERE user_id = :user_id AND is_visible = 1
           ORDER BY is_featured DESC, project_date DESC, sort_order ASC');
$db->bind(':user_id', $user_id);
$portfolio_items = $db->resultSet();

// Get completed cohorts
$db->query('SELECT c.name, c.course_type, e.completion_date
           FROM enrollments e
           JOIN cohorts c ON e.cohort_id = c.id
           WHERE e.user_id = :user_id AND e.status = "completed"
           ORDER BY e.completion_date DESC');
$db->bind(':user_id', $user_id);
$cohorts = $db->resultSet();

// Get certificates
$db->query('SELECT * FROM certificates
           WHERE user_id = :user_id AND is_released = 1
           ORDER BY issue_date DESC');
$db->bind(':user_id', $user_id);
$certificates = $db->resultSet();
?>

<div class="bg-gradient-to-br from-primary via-blue-700 to-secondary text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="showcase.php" class="text-white hover:text-blue-200 mb-4 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Showcase
        </a>

        <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-8 mt-4">
            <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-2xl flex-shrink-0">
                    <?php if($graduate['profile_image']): ?>
                        <img src="uploads/profiles/<?php echo htmlspecialchars($graduate['profile_image']); ?>"
                             alt="Profile" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-accent to-yellow-500 flex items-center justify-center text-white text-4xl font-bold">
                            <?php echo strtoupper(substr($graduate['first_name'], 0, 1) . substr($graduate['last_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-4xl font-bold mb-2">
                        <?php echo htmlspecialchars($graduate['first_name'] . ' ' . $graduate['last_name']); ?>
                    </h1>
                    <?php if($graduate['title']): ?>
                        <p class="text-2xl text-blue-100 mb-4"><?php echo htmlspecialchars($graduate['title']); ?></p>
                    <?php endif; ?>

                    <?php if($graduate['bio']): ?>
                        <p class="text-lg text-blue-50 leading-relaxed max-w-3xl">
                            <?php echo nl2br(htmlspecialchars($graduate['bio'])); ?>
                        </p>
                    <?php endif; ?>

                    <div class="flex flex-wrap gap-4 mt-6 justify-center md:justify-start">
                        <?php if($graduate['linkedin_url']): ?>
                            <a href="<?php echo htmlspecialchars($graduate['linkedin_url']); ?>" target="_blank"
                               class="bg-white text-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-all">
                                <i class="fab fa-linkedin mr-2"></i>LinkedIn
                            </a>
                        <?php endif; ?>

                        <?php if($graduate['portfolio_url']): ?>
                            <a href="<?php echo htmlspecialchars($graduate['portfolio_url']); ?>" target="_blank"
                               class="bg-white text-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-all">
                                <i class="fas fa-globe mr-2"></i>Website
                            </a>
                        <?php endif; ?>

                        <?php if($graduate['github_url']): ?>
                            <a href="<?php echo htmlspecialchars($graduate['github_url']); ?>" target="_blank"
                               class="bg-white text-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-all">
                                <i class="fab fa-github mr-2"></i>GitHub
                            </a>
                        <?php endif; ?>

                        <?php if($graduate['cv_filename']): ?>
                            <a href="uploads/cvs/<?php echo htmlspecialchars($graduate['cv_filename']); ?>" target="_blank"
                               class="bg-coral text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-600 transition-all">
                                <i class="fas fa-file-pdf mr-2"></i>Download CV
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <?php if(!empty($portfolio_items)): ?>
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Portfolio</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach($portfolio_items as $item): ?>
                                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-all">
                                    <?php if($item['image_url'] || $item['thumbnail']): ?>
                                        <div class="h-48 bg-gray-200 overflow-hidden">
                                            <img src="<?php echo htmlspecialchars($item['image_url'] ?: $item['thumbnail']); ?>"
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                 class="w-full h-full object-cover hover:scale-105 transition-transform">
                                        </div>
                                    <?php endif; ?>

                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </h3>

                                        <?php if($item['description']): ?>
                                            <p class="text-gray-600 text-sm mb-3">
                                                <?php echo htmlspecialchars(substr($item['description'], 0, 120)) . (strlen($item['description']) > 120 ? '...' : ''); ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if($item['technologies']): ?>
                                            <div class="flex flex-wrap gap-2 mb-3">
                                                <?php
                                                $techs = array_slice(explode(',', $item['technologies']), 0, 3);
                                                foreach($techs as $tech):
                                                    $tech = trim($tech);
                                                ?>
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                                                        <?php echo htmlspecialchars($tech); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($item['project_url']): ?>
                                            <a href="<?php echo htmlspecialchars($item['project_url']); ?>" target="_blank"
                                               class="text-primary hover:text-blue-700 text-sm font-medium">
                                                View Project <i class="fas fa-external-link-alt ml-1"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($graduate['achievements']): ?>
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Achievements</h2>
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($graduate['achievements'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="space-y-6">
                <?php if($graduate['skills']): ?>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Skills</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php
                            $skills = explode(',', $graduate['skills']);
                            foreach($skills as $skill):
                                $skill = trim($skill);
                                if($skill):
                            ?>
                                <span class="px-3 py-2 bg-blue-100 text-blue-800 text-sm rounded-lg font-medium">
                                    <?php echo htmlspecialchars($skill); ?>
                                </span>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(!empty($cohorts)): ?>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Training</h3>
                        <?php foreach($cohorts as $cohort): ?>
                            <div class="mb-4 pb-4 border-b border-gray-200 last:border-0">
                                <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($cohort['name']); ?></h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($cohort['course_type']); ?></p>
                                <p class="text-sm text-gray-500">Completed: <?php echo date('M Y', strtotime($cohort['completion_date'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if(!empty($certificates)): ?>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Certificates</h3>
                        <?php foreach($certificates as $cert): ?>
                            <div class="mb-3 p-3 bg-green-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-certificate text-green-600 mr-3"></i>
                                    <div>
                                        <p class="font-semibold text-sm text-gray-900"><?php echo htmlspecialchars($cert['course_name']); ?></p>
                                        <p class="text-xs text-gray-600"><?php echo htmlspecialchars($cert['certificate_number']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if($graduate['years_experience']): ?>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Experience</h3>
                        <p class="text-3xl font-bold text-primary"><?php echo $graduate['years_experience']; ?>+ years</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
