<?php
require_once 'classes/Database.php';
include 'includes/header.php';

$db = new Database();

$success_message = '';
$error_message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if($name && $email && $subject && $message) {
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $db->query('INSERT INTO contact_messages (user_id, name, email, phone, subject, message)
                   VALUES (:user_id, :name, :email, :phone, :subject, :message)');
        $db->bind(':user_id', $user_id);
        $db->bind(':name', $name);
        $db->bind(':email', $email);
        $db->bind(':phone', $phone);
        $db->bind(':subject', $subject);
        $db->bind(':message', $message);

        if($db->execute()) {
            $success_message = "Your message has been sent successfully! We'll get back to you soon.";
            $_POST = [];
        } else {
            $error_message = "Failed to send message. Please try again.";
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

$default_name = '';
$default_email = '';
$default_phone = '';

if(isset($_SESSION['user_id'])) {
    $db->query('SELECT first_name, last_name, email, phone FROM users WHERE id = :id');
    $db->bind(':id', $_SESSION['user_id']);
    $user = $db->single();

    if($user) {
        $default_name = $user['first_name'] . ' ' . $user['last_name'];
        $default_email = $user['email'];
        $default_phone = isset($user['phone']) ?  $user['phone'] : '';
    }
}
?>

<div class="bg-gradient-to-br from-primary via-blue-700 to-secondary text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-4">Contact Us</h1>
        <p class="text-xl text-blue-100">We're here to help! Reach out with any questions or concerns</p>
    </div>
</div>

<div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
    <?php if($success_message): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg mb-8">
            <i class="fas fa-check-circle mr-2 text-xl"></i>
            <span class="text-lg"><?php echo $success_message; ?></span>
        </div>
    <?php endif; ?>

    <?php if($error_message): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg mb-8">
            <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
            <span class="text-lg"><?php echo $error_message; ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Send us a Message</h2>

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">Your Name *</label>
                            <input type="text" name="name"
                                   value="<?php echo htmlspecialchars(isset($_POST['name']) ? $_POST['name'] : $default_name); ?>"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">Email Address *</label>
                            <input type="email" name="email"
                                   value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : $default_email); ?>"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">Phone Number</label>
                            <input type="tel" name="phone"
                                   value="<?php echo htmlspecialchars(isset($_POST['phone']) ? $_POST['phone'] : $default_phone); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-700">Subject *</label>
                            <select name="subject" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select a subject...</option>
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Application Status">Application Status</option>
                                <option value="Course Information">Course Information</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Payment Issue">Payment Issue</option>
                                <option value="Certificate Request">Certificate Request</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Message *</label>
                        <textarea name="message" rows="8" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Tell us how we can help you..."><?php echo htmlspecialchars(isset($_POST['message']) ? $_POST['message'] : ''); ?></textarea>
                    </div>

                    <button type="submit"
                            class="bg-primary text-white px-8 py-4 rounded-lg hover:bg-blue-700 transition font-bold text-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Send Message
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-map-marker-alt text-primary mr-2"></i>Visit Us
                </h3>
                <p class="text-gray-700 leading-relaxed">
                    Whoba Ogo Foundation<br>
                    Training Center<br>
                    Osogbo, Osun State<br>
                    Nigeria
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-phone text-primary mr-2"></i>Call Us
                </h3>
                <p class="text-gray-700 leading-relaxed">
                    Phone: +234 XXX XXX XXXX<br>
                    WhatsApp: +234 XXX XXX XXXX
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-envelope text-primary mr-2"></i>Email Us
                </h3>
                <p class="text-gray-700 leading-relaxed">
                    General: info@whobaogo.org<br>
                    Support: support@whobaogo.org<br>
                    Admissions: admissions@whobaogo.org
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-clock text-primary mr-2"></i>Office Hours
                </h3>
                <p class="text-gray-700 leading-relaxed">
                    Monday - Friday: 9:00 AM - 5:00 PM<br>
                    Saturday: 10:00 AM - 2:00 PM<br>
                    Sunday: Closed
                </p>
            </div>

            <div class="bg-gradient-to-br from-primary to-secondary rounded-lg shadow-lg p-6 text-white">
                <h3 class="text-xl font-bold mb-4">
                    <i class="fas fa-share-alt mr-2"></i>Connect With Us
                </h3>
                <div class="flex space-x-4">
                    <a href="#" class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition">
                        <i class="fab fa-facebook-f text-xl"></i>
                    </a>
                    <a href="#" class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                    <a href="#" class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition">
                        <i class="fab fa-linkedin-in text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
