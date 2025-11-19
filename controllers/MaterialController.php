<?php
class MaterialController {
    private $materialModel;

    public function __construct($db) {
        $this->materialModel = new Material($db);
    }

    public function addMaterial($data) {
        $this->materialModel->coach_id = $data['coach_id'];
        $this->materialModel->course_id = $data['course_id'];
        $this->materialModel->title = $data['title'];
        $this->materialModel->material_type = $data['material_type'];
        $this->materialModel->content = $data['content'];
        $this->materialModel->description = $data['description'];
        
        return $this->materialModel->create();
    }

    public function getCourseMaterials($course_id) {
        return $this->materialModel->readByCourse($course_id);
    }

    public function getCoachMaterials($coach_id) {
        return $this->materialModel->readByCoach($coach_id);
    }
}
?>