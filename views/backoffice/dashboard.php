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
    <h1>WeConnect Dashboard</h1>
    <p>Welcome to the WeConnect administration panel</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Coaches</h3>
        <div class="stat-number"><?php echo $coaches_count; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Participants</h3>
        <div class="stat-number"><?php echo $participants_count; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Courses</h3>
        <div class="stat-number"><?php echo $courses_count; ?></div>
    </div>
    <div class="stat-card">
        <h3>Active Sessions</h3>
        <div class="stat-number"><?php echo rand(50, 200); ?></div>
    </div>
</div>

<div class="recent-activity">
    <h2>Recent Activity</h2>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Activity</th>
                    <th>User</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>New coach registration</td>
                    <td>John Doe</td>
                    <td>2 hours ago</td>
                </tr>
                <tr>
                    <td>Course material uploaded</td>
                    <td>Jane Smith</td>
                    <td>4 hours ago</td>
                </tr>
                <tr>
                    <td>New participant joined</td>
                    <td>Mike Johnson</td>
                    <td>6 hours ago</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>