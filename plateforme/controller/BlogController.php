<?php
//intermédiaire entre la base de données (modèle) et les pages HTML//
require_once 'model/BlogModel.php';

class BlogController {
    private $model;

    public function __construct() {
        $this->model = new BlogModel();
    }

    public function index() {
        $articles = $this->model->getArticles(6);
        $recent_articles = $this->model->getRecentArticles(3);
        
        $data = [
            'articles' => $articles,
            'recent_articles' => $recent_articles,
            'categories' => ['Solidarité', 'Environnement', 'Éducation', 'Inclusion', 'Santé', 'Culture']
        ];
        
        require_once 'view/blog/index.php';
    }

    public function show($id) {
        $article = $this->model->getArticleById($id);
        $comments = $this->model->getCommentsByArticle($id);
        
        if (!$article) {
            echo "Article non trouvé";
            return;
        }
        
        $data = [
            'article' => $article,
            'comments' => $comments
        ];
        
        require_once 'view/blog/show.php';
    }

    public function getCommentCount($article_id) {
        return $this->model->countComments($article_id);
    }
}
?>