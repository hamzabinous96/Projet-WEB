<?php
$userController = new UserController($db);
$coaches = $userController->getAllUsers();

// Filter only coaches and sort by popularity
$coachesList = [];
while($user = $coaches->fetch(PDO::FETCH_ASSOC)) {
    if($user['role'] == 'coach') {
        $coachesList[] = $user;
    }
}

// Sort by popularity (you can implement this based on your logic)
usort($coachesList, function($a, $b) {
    return ($b['popularity'] ?? 0) - ($a['popularity'] ?? 0);
});
?>

<section class="our-coaches">
    <div class="container">
        <h2>Our Expert Coaches</h2>
        <p class="subtitle">Meet our dedicated team of professional teachers</p>
        
        <!-- Most Popular Coaches Section -->
        <div class="popular-coaches">
            <h3>🌟 Most Selected Coaches</h3>
            <div class="coaches-grid">
                <?php 
                $topCoaches = array_slice($coachesList, 0, 3); // Top 3 coaches
                foreach($topCoaches as $coach): 
                ?>
                    <div class="coach-card featured">
                        <div class="coach-avatar">
                            <?php echo strtoupper(substr($coach['name'], 0, 2)); ?>
                        </div>
                        <div class="coach-info">
                            <h3><?php echo htmlspecialchars($coach['name']); ?></h3>
                            <div class="coach-badge">Top Coach</div>
                            <p class="coach-bio"><?php echo htmlspecialchars($coach['bio']); ?></p>
                            <div class="coach-stats">
                                <span class="stat">⭐ <?php echo $coach['popularity'] ?? 0; ?> selections</span>
                            </div>
                        </div>
                        <div class="coach-actions">
                            <a href="index.php?page=chat&coach_id=<?php echo $coach['id']; ?>" class="btn-message">
                                💬 Message
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- All Coaches Section -->
        <div class="all-coaches">
            <h3>All Our Coaches</h3>
            <div class="coaches-grid">
                <?php foreach($coachesList as $coach): ?>
                    <div class="coach-card">
                        <div class="coach-avatar">
                            <?php echo strtoupper(substr($coach['name'], 0, 2)); ?>
                        </div>
                        <div class="coach-info">
                            <h3><?php echo htmlspecialchars($coach['name']); ?></h3>
                            <?php if($coach['teacher_code']): ?>
                                <div class="teacher-code">ID: <?php echo $coach['teacher_code']; ?></div>
                            <?php endif; ?>
                            <p class="coach-bio"><?php echo htmlspecialchars($coach['bio']); ?></p>
                            <div class="coach-contact">
                                <span class="coach-email">📧 <?php echo htmlspecialchars($coach['email']); ?></span>
                                <?php if($coach['phone']): ?>
                                    <span class="coach-phone">📞 <?php echo htmlspecialchars($coach['phone']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="coach-actions">
                            <a href="index.php?page=chat&coach_id=<?php echo $coach['id']; ?>" class="btn-message">
                                💬 Message
                            </a>
                            <a href="index.php?page=coach_materials&coach_id=<?php echo $coach['id']; ?>" class="btn-materials">
                                📚 Materials
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>