<?php
require_once('../config.php');
require_once('../classes/Article.php');

// Устанавливаем заголовок для JSON
header('Content-Type: application/json; charset=utf-8');

// Проверяем, есть ли параметр articleId
if (isset($_REQUEST['articleId'])) {
    $articleId = (int)$_REQUEST['articleId'];
    
    try {
        // Получаем статью
        $article = Article::getById($articleId);
        
        if ($article) {
            // Возвращаем JSON с содержанием статьи
            echo json_encode(array(
                'content' => $article->content,
                'status' => 'success'
            ));
        } else {
            // Статья не найдена
            echo json_encode(array(
                'error' => 'Статья не найдена',
                'status' => 'error'
            ));
        }
    } catch (Exception $e) {
        // Ошибка при получении статьи
        echo json_encode(array(
            'error' => 'Ошибка сервера: ' . $e->getMessage(),
            'status' => 'error'
        ));
    }
} else {
    // Не указан articleId
    echo json_encode(array(
        'error' => 'Не указан ID статьи',
        'status' => 'error'
    ));
}
?>