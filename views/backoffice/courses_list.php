<?php
$courseController = new CourseController($db);
$courses = $courseController->getAllCourses();

if(isset($_GET['message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</div>';
}
?>

<div class="content-header">
    <h1>Courses Management</h1>
    <p>Manage all courses in the platform</p>
</div>

<a href="backoffice.php?page=add_course" class="btn btn-add">Add New Course</a>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Category</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $courses->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo ucfirst(str_replace('_', ' ', $row['category'])); ?></td>
                <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                <td>
                    <a href="backoffice.php?page=edit_course&id=<?php echo $row['id']; ?>" class="btn btn-edit">Edit</a>
                    <a href="backoffice.php?page=courses&delete_course=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>