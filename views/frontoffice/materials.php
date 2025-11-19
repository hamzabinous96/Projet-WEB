<?php
$course_id = $_GET['course_id'] ?? 1;
$coach_id = $_GET['coach_id'] ?? null;

// Sample materials data
$materials = [
    [
        'title' => 'Advanced Calculus PDF', 
        'description' => 'Complete calculus course materials and exercises',
        'material_type' => 'pdf',
        'content' => 'https://example.com/calculus.pdf',
        'coach_name' => 'Math Coach John',
        'created_at' => '2024-01-15 10:30:00'
    ],
    [
        'title' => 'Algebra Basics Video', 
        'description' => 'Introduction to algebraic equations and problem solving',
        'material_type' => 'youtube',
        'content' => 'dQw4w9WgXcQ',
        'coach_name' => 'Math Coach John',
        'created_at' => '2024-01-10 14:20:00'
    ],
    [
        'title' => 'HTML Crash Course', 
        'description' => 'Complete HTML tutorial for beginners',
        'material_type' => 'pdf',
        'content' => 'https://example.com/html-course.pdf',
        'coach_name' => 'HTML Coach Sarah',
        'created_at' => '2024-01-12 09:15:00'
    ]
];
?>

<section class="materials">
    <div class="container">
        <h2>Course Materials</h2>
        <p class="subtitle">Learning resources and study materials</p>
        
        <div class="materials-grid">
            <?php foreach($materials as $material): ?>
                <div class="material-card">
                    <h3>
                        <?php 
                        $icons = [
                            'pdf' => '📄',
                            'youtube' => '🎥', 
                            'video_link' => '🔗'
                        ];
                        echo $icons[$material['material_type']] . ' ' . htmlspecialchars($material['title']);
                        ?>
                    </h3>
                    <p><?php echo htmlspecialchars($material['description']); ?></p>
                    <div class="material-meta">
                        <span>By: <?php echo htmlspecialchars($material['coach_name']); ?></span>
                        <span>Posted: <?php echo date('M j, Y', strtotime($material['created_at'])); ?></span>
                    </div>
                    <?php if($material['material_type'] == 'pdf'): ?>
                        <a href="<?php echo htmlspecialchars($material['content']); ?>" class="material-button" target="_blank">Download PDF</a>
                    <?php elseif($material['material_type'] == 'youtube'): ?>
                        <a href="https://youtube.com/watch?v=<?php echo htmlspecialchars($material['content']); ?>" class="material-button" target="_blank">Watch Video</a>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($material['content']); ?>" class="material-button" target="_blank">View Content</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>