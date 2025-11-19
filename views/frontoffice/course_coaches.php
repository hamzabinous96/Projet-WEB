<?php
$course_id = $_GET['course_id'] ?? 1;

// Simple course data
$courses = [
    1 => ['name' => 'Mathematics', 'description' => 'Advanced mathematics course', 'category' => 'math'],
    2 => ['name' => 'HTML & CSS', 'description' => 'Web development fundamentals', 'category' => 'html'],
    3 => ['name' => 'Société Paix et Inclusion', 'description' => 'Social peace and inclusion studies', 'category' => 'societe_paix_inclusion']
];

$course = $courses[$course_id] ?? $courses[1];

// Sample coaches data
$coaches = [
    ['id' => 1, 'name' => 'Math Coach John', 'email' => 'mathjohn@weconnect.com', 'phone' => '111-222-3333', 'bio' => 'Experienced mathematics teacher with 10 years of experience'],
    ['id' => 2, 'name' => 'HTML Coach Sarah', 'email' => 'htmlsarah@weconnect.com', 'phone' => '444-555-6666', 'bio' => 'Web developer and coding instructor'],
    ['id' => 3, 'name' => 'Social Coach Mike', 'email' => 'socialmike@weconnect.com', 'phone' => '777-888-9999', 'bio' => 'Social studies expert and peace educator']
];
?>

<section class="course-coaches">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php?page=courses">← Back to Courses</a>
        </div>
        
        <div class="course-header">
            <h1>Coaches for <?php echo htmlspecialchars($course['name']); ?></h1>
            <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
        </div>
        
        <div class="coaches-grid">
            <?php foreach($coaches as $coach): ?>
                <div class="coach-card">
                    <div class="coach-avatar">
                        <?php echo strtoupper(substr($coach['name'], 0, 2)); ?>
                    </div>
                    <div class="coach-info">
                        <h3><?php echo htmlspecialchars($coach['name']); ?></h3>
                        <p class="coach-bio"><?php echo htmlspecialchars($coach['bio']); ?></p>
                        <div class="coach-contact">
                            <span class="coach-email">📧 <?php echo htmlspecialchars($coach['email']); ?></span>
                            <?php if($coach['phone']): ?>
                                <span class="coach-phone">📞 <?php echo htmlspecialchars($coach['phone']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="coach-actions">
                        <a href="index.php?page=chat&coach_id=<?php echo $coach['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn-message">
                            💬 Send Message
                        </a>
                        <a href="index.php?page=materials&coach_id=<?php echo $coach['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn-materials">
                            📚 View Materials
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>