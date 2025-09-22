<?php 
require_once '../classes/Database.php';
include '../includes/header.php';

$auth->requireAdmin();

$db = new Database();
$message = '';
$error = '';

// Handle event creation/updates
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['create_event'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $event_type = $_POST['event_type'];
        $cohort_id = !empty($_POST['cohort_id']) ? $_POST['cohort_id'] : null;
        $start_date = $_POST['start_date'];
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
        $location = trim($_POST['location']);
        $max_participants = !empty($_POST['max_participants']) ? $_POST['max_participants'] : null;
        
        if(empty($title) || empty($start_date)) {
            $error = 'Please fill in all required fields';
        } else {
            $db->query('INSERT INTO events (title, description, event_type, cohort_id, start_date, end_date, start_time, end_time, location, max_participants, created_by) 
                       VALUES (:title, :description, :event_type, :cohort_id, :start_date, :end_date, :start_time, :end_time, :location, :max_participants, :created_by)');
            $db->bind(':title', $title);
            $db->bind(':description', $description);
            $db->bind(':event_type', $event_type);
            $db->bind(':cohort_id', $cohort_id);
            $db->bind(':start_date', $start_date);
            $db->bind(':end_date', $end_date);
            $db->bind(':start_time', $start_time);
            $db->bind(':end_time', $end_time);
            $db->bind(':location', $location);
            $db->bind(':max_participants', $max_participants);
            $db->bind(':created_by', $_SESSION['user_id']);
            
            if($db->execute()) {
                $message = 'Event created successfully!';
            } else {
                $error = 'Failed to create event';
            }
        }
    } elseif(isset($_POST['update_status'])) {
        $event_id = $_POST['event_id'];
        $new_status = $_POST['new_status'];
        
        $db->query('UPDATE events SET status = :status WHERE id = :id');
        $db->bind(':status', $new_status);
        $db->bind(':id', $event_id);
        
        if($db->execute()) {
            $message = 'Event status updated successfully!';
        }
    }
}

// Get all events with cohort info
$db->query('SELECT e.*, c.name as cohort_name 
           FROM events e 
           LEFT JOIN cohorts c ON e.cohort_id = c.id 
           ORDER BY e.start_date DESC');
$events = $db->resultSet();

// Get cohorts for dropdown
$db->query('SELECT id, name FROM cohorts WHERE status IN ("upcoming", "active") ORDER BY start_date ASC');
$cohorts = $db->resultSet();
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Events & Examinations</h1>
        <p class="text-gray-600 mt-2">Schedule and manage foundation events, examinations, and activities</p>
    </div>
    
    <?php if($message): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Create New Event Form -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Schedule New Event</h2>
        
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Event Title</label>
                <input type="text" id="title" name="title" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="event_type" class="block text-sm font-medium text-gray-700">Event Type</label>
                <select id="event_type" name="event_type" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="examination">Examination</option>
                    <option value="graduation">Graduation</option>
                    <option value="workshop">Workshop</option>
                    <option value="meeting">Meeting</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div>
                <label for="cohort_id" class="block text-sm font-medium text-gray-700">Related Cohort (Optional)</label>
                <select id="cohort_id" name="cohort_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
                    <option value="">Select Cohort</option>
                    <?php foreach($cohorts as $cohort): ?>
                        <option value="<?php echo $cohort['id']; ?>"><?php echo htmlspecialchars($cohort['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" id="location" name="location"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" id="start_date" name="start_date" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date (Optional)</label>
                <input type="date" id="end_date" name="end_date"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                <input type="time" id="start_time" name="start_time"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                <input type="time" id="end_time" name="end_time"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="max_participants" class="block text-sm font-medium text-gray-700">Max Participants</label>
                <input type="number" id="max_participants" name="max_participants" min="1"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
            
            <div class="md:col-span-2">
                <button type="submit" name="create_event" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-calendar-plus mr-2"></i>Schedule Event
                </button>
            </div>
        </form>
    </div>
    
    <!-- Events Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">All Events</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cohort</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($events as $event): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($event['title']); ?></div>
                                    <?php if($event['max_participants']): ?>
                                        <div class="text-sm text-gray-500">Max: <?php echo $event['max_participants']; ?> participants</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    <?php if($event['event_type'] === 'examination'): ?>bg-red-100 text-red-800<?php endif; ?>
                                    <?php if($event['event_type'] === 'graduation'): ?>bg-blue-100 text-blue-800<?php endif; ?>
                                    <?php if($event['event_type'] === 'workshop'): ?>bg-green-100 text-green-800<?php endif; ?>
                                    <?php if($event['event_type'] === 'meeting'): ?>bg-yellow-100 text-yellow-800<?php endif; ?>
                                    <?php if($event['event_type'] === 'other'): ?>bg-gray-100 text-gray-800<?php endif; ?>">
                                    <?php echo ucfirst($event['event_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $event['cohort_name'] ? htmlspecialchars($event['cohort_name']) : 'All Cohorts'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div><?php echo date('M j, Y', strtotime($event['start_date'])); ?></div>
                                <?php if($event['start_time']): ?>
                                    <div class="text-gray-500"><?php echo date('g:i A', strtotime($event['start_time'])); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($event['location'] ?: 'TBD'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    <?php if($event['status'] === 'scheduled'): ?>bg-blue-100 text-blue-800<?php endif; ?>
                                    <?php if($event['status'] === 'ongoing'): ?>bg-green-100 text-green-800<?php endif; ?>
                                    <?php if($event['status'] === 'completed'): ?>bg-gray-100 text-gray-800<?php endif; ?>
                                    <?php if($event['status'] === 'cancelled'): ?>bg-red-100 text-red-800<?php endif; ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <select name="new_status" onchange="this.form.submit()" class="text-xs border rounded px-2 py-1">
                                        <option value="">Change Status</option>
                                        <option value="scheduled" <?php echo $event['status'] === 'scheduled' ? 'disabled' : ''; ?>>Scheduled</option>
                                        <option value="ongoing" <?php echo $event['status'] === 'ongoing' ? 'disabled' : ''; ?>>Ongoing</option>
                                        <option value="completed" <?php echo $event['status'] === 'completed' ? 'disabled' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $event['status'] === 'cancelled' ? 'disabled' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>