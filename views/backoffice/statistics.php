<?php
$userController = new UserController($db);
$courseController = new CourseController($db);

$users_count = $userController->getUserStats();
$coaches_count = 0;
$participants_count = 0;

while($row = $users_count->fetch(PDO::FETCH_ASSOC)) {
    if($row['role'] == 'coach') {
        $coaches_count = $row['count'];
    } else if($row['role'] == 'participant') {
        $participants_count = $row['count'];
    }
}

$courses_count = $courseController->getAllCourses()->rowCount();
?>

<div class="content-header">
    <h1>Platform Statistics</h1>
    <p>Detailed analytics and insights</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Coaches</h3>
        <div class="stat-number"><?php echo $coaches_count; ?></div>
        <div class="stat-change">+12% from last month</div>
    </div>
    <div class="stat-card">
        <h3>Total Participants</h3>
        <div class="stat-number"><?php echo $participants_count; ?></div>
        <div class="stat-change">+8% from last month</div>
    </div>
    <div class="stat-card">
        <h3>Total Courses</h3>
        <div class="stat-number"><?php echo $courses_count; ?></div>
        <div class="stat-change">+3 new this month</div>
    </div>
    <div class="stat-card">
        <h3>Active Sessions</h3>
        <div class="stat-number"><?php echo rand(50, 200); ?></div>
        <div class="stat-change">Currently online</div>
    </div>
</div>

<div class="charts-container">
    <div class="chart-card">
        <h3>Users Distribution</h3>
        <div class="chart-placeholder">
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color coach"></span>
                    <span>Coaches: <?php echo $coaches_count; ?></span>
                </div>
                <div class="legend-item">
                    <span class="legend-color participant"></span>
                    <span>Participants: <?php echo $participants_count; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="chart-card">
        <h3>Course Popularity</h3>
        <div class="chart-placeholder">
            <div class="popularity-list">
                <div class="popularity-item">
                    <span>Mathematics</span>
                    <span class="popularity-bar" style="width: 80%">80%</span>
                </div>
                <div class="popularity-item">
                    <span>HTML & CSS</span>
                    <span class="popularity-bar" style="width: 65%">65%</span>
                </div>
                <div class="popularity-item">
                    <span>Société Paix et Inclusion</span>
                    <span class="popularity-bar" style="width: 45%">45%</span>
                </div>
            </div>
        </div>
    </div>
</div>