<?php
class CourseController {
    private $courseModel;
    private $userModel;

    public function __construct($db) {
        $this->courseModel = new Course($db);
        $this->userModel = new User($db);
    }

    public function getAllCourses() {
        return $this->courseModel->read();
    }

    public function getCourse($id) {
        $this->courseModel->id = $id;
        if($this->courseModel->readOne()) {
            return $this->courseModel;
        }
        return false;
    }

    public function createCourse($data) {
        $this->courseModel->name = $data['name'];
        $this->courseModel->description = $data['description'];
        $this->courseModel->category = $data['category'];
        
        return $this->courseModel->create();
    }

    public function updateCourse($id, $data) {
        $this->courseModel->id = $id;
        $this->courseModel->name = $data['name'];
        $this->courseModel->description = $data['description'];
        $this->courseModel->category = $data['category'];
        
        return $this->courseModel->update();
    }

    public function deleteCourse($id) {
        $this->courseModel->id = $id;
        return $this->courseModel->delete();
    }

    public function getCourseCoaches($course_id) {
        return $this->courseModel->getCourseCoaches($course_id);
    }
}
?>