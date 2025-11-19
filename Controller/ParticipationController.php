<?php
// Ensure config and model are loaded early so classes exist when controller runs
// (helps avoid "Class 'Participation' not found" when include paths vary)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Participation.php';
// Troubleshooting: "Error: spawn php ENOENT"
// - This error comes from the Node process that tried to spawn `php` (e.g. VS Code task/extension).
// - Fix options:
//   1) Install PHP and add php.exe to your Windows PATH (for XAMPP: add C:\xampp\php).
//      Example (Windows): setx PATH "%PATH%;C:\xampp\php"
//   2) Configure your editor/launcher to use the full php.exe path (recommended):
//      - VS Code launch.json example:
//          "configurations": [
//            {
//              "name": "PHP Built-in Server",
//              "type": "php",
//              "request": "launch",
//              "runtimeExecutable": "C:\\\\xampp\\\\php\\\\php.exe",
//              "program": "${file}"
//            }
//          ]
//   3) Start the server manually from a terminal where php is available:
//         cd c:\xampp\htdocs\tasnim
//         C:\xampp\php\php.exe -S localhost:8000 -t .
//   4) Or use XAMPP Apache: start Apache and use http://localhost/tasnim/...
// - After fixing PATH or runtimeExecutable, re-run your dev server; ENOENT should disappear.

// locate Participation.php (search current and parent directories)
$modelPath = null;
$dir = __DIR__;
for ($i = 0; $i < 6; $i++) {
    $try = $dir . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Participation.php';
    if (file_exists($try)) {
        $modelPath = $try;
        break;
    }
    $dir = dirname($dir);
}
if (!$modelPath) {
    die("Model file not found. Looked for Model/Participation.php in controller and parent directories.");
}

require_once $modelPath;

// If model was declared in a namespace, create a global alias to ease older code (optional)
if (!class_exists('Participation')) {
    if (class_exists('Model\\Participation')) {
        class_alias('Model\\Participation', 'Participation');
    } elseif (class_exists('App\\Model\\Participation')) {
        class_alias('App\\Model\\Participation', 'Participation');
    }
}

class ParticipationController {
    private $participationModel;

    public function __construct() {
        // Try known possible class names (global or namespaced) and instantiate the first found.
        $possibleModelClasses = ['Participation', 'Model\\Participation', 'App\\Model\\Participation'];

        $modelClass = null;
        foreach ($possibleModelClasses as $cname) {
            if (class_exists($cname)) {
                $modelClass = $cname;
                break;
            }
        }

        if (!$modelClass) {
            die("Class 'Participation' not found after requiring model file. Ensure Participation.php defines the class.");
        }

        $this->participationModel = new $modelClass();
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titre'] ?? '';
            $auteur = $_POST['auteur'] ?? '';
            $categorie = $_POST['categorie'] ?? '';

            if (empty($titre) || empty($auteur) || empty($categorie)) {
                return ['error' => 'All fields are required'];
            }

            try {
                $result = $this->participationModel->create($titre, $auteur, $categorie);

                if ($result) {
                    return ['success' => 'Participation created successfully!'];
                } else {
                    return ['error' => 'Failed to create participation'];
                }
            } catch (Exception $e) {
                return ['error' => 'Database error: ' . $e->getMessage()];
            }
        }

        return ['error' => 'Invalid request method'];
    }

    public function index() {
        return $this->participationModel->getAll();
    }

    public function edit($id) {
        return $this->participationModel->getById($id);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titre'] ?? '';
            $auteur = $_POST['auteur'] ?? '';
            $categorie = $_POST['categorie'] ?? '';

            if (empty($titre) || empty($auteur) || empty($categorie)) {
                return ['error' => 'All fields are required'];
            }

            $result = $this->participationModel->update($id, $titre, $auteur, $categorie);

            if ($result) {
                return ['success' => 'Participation updated successfully!'];
            } else {
                return ['error' => 'Failed to update participation'];
            }
        }
    }

    public function delete($id) {
        $result = $this->participationModel->delete($id);

        if ($result) {
            return ['success' => 'Participation deleted successfully!'];
        } else {
            return ['error' => 'Failed to delete participation'];
        }
    }
}
?>
