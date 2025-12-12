<?php
/**
 * Класс для обработки статей
 */
class Article
{
    // Свойства
    /**
    * @var int ID статей из базы данны
    */
    public $id = null;

    /**
    * @var int Дата первой публикации статьи
    */
    public $publicationDate = null;

    /**
    * @var string Полное название статьи
    */
    public $title = null;

     /**
    * @var int ID категории статьи
    */
    public $categoryId = null;

    public $subcategoryId = null;

    /**
    * @var string Краткое описание статьи
    */
    public $summary = null;

    /**
    * @var string HTML содержание статьи
    */
    public $content = null;

    /**
    * @var array массив авторов
    */
    public $authors = array();
     /**
    * @var int активна ли статья. по умолчанию активна
    */
    public $active = 1;

     /**
    * @var int активна ли статья. по умолчанию активна
    */
    public $views = 0;
    
    /**
     * Создаст объект статьи
     * 
     * @param array $data массив значений (столбцов) строки таблицы статей
     */
    public function __construct($data=array())
    {
        
      if (isset($data['id'])) {
          $this->id = (int) $data['id'];
      }
      
      if (isset( $data['publicationDate'])) {
          $this->publicationDate = (string) $data['publicationDate'];     
      }

      //die(print_r($this->publicationDate));

      if (isset($data['title'])) {
          $this->title = $data['title'];        
      }
      
      if (isset($data['categoryId'])) {
          $this->categoryId = (int) $data['categoryId'];      
      }
      
      if (isset($data['subcategoryId'])) {
          $this->subcategoryId = (int) $data['subcategoryId'];      
      }
      
      if (isset($data['summary'])) {
          $this->summary = $data['summary'];         
      }
      
      if (isset($data['content'])) {
          $this->content = $data['content'];  
      }

      if (isset($data['active'])) {
          $this->active = $data['active'];  
      }

      if (isset($data['views'])) {
          $this->views = $data['views'];  
      }
    }


    /**
    * Устанавливаем свойства с помощью значений формы редактирования записи в заданном массиве
    *
    * @param assoc Значения записи формы
    */
    public function storeFormValues ( $params ) {

      // Сохраняем все параметры
      $this->__construct( $params );

      // Разбираем и сохраняем дату публикации
      if ( isset($params['publicationDate']) ) {
        $publicationDate = explode ( '-', $params['publicationDate'] );

        if ( count($publicationDate) == 3 ) {
          list ( $y, $m, $d ) = $publicationDate;
          $this->publicationDate = mktime ( 0, 0, 0, $m, $d, $y );
        }
      }
      if (isset($params['subcategoryId']) && !empty($params['subcategoryId'])) {
        $this->subcategoryId = (int)$params['subcategoryId'];
        } else {
        $this->subcategoryId = null; 
        }
        
        if (isset($params['authorIds']) && is_array($params['authorIds'])) {
        $this->authors = array_filter($params['authorIds'], function($id) {
            return !empty($id);
        });
    }
    if (isset($params['authorIds']) && is_array($params['authorIds'])) {
        $this->authors = array();
        foreach ($params['authorIds'] as $id) {
            if (!empty($id) && is_numeric($id) && $id > 0) {
                $this->authors[] = (int)$id;
            }
        }
    } else {
        $this->authors = array();
    } 
    }


    /**
    * Возвращаем объект статьи соответствующий заданному ID статьи
    *
    * @param int ID статьи
    * @return Article|false Объект статьи или false, если запись не найдена или возникли проблемы
    */
    public static function getById($id) {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) "
                . "AS publicationDate FROM articles WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch();
        $conn = null;
        
        if ($row) { 
            $article = new Article($row);
            $article->getAuthors(); 
            return $article;
        }
        return false;
    } 

    /**
    * Возвращает диапазон объекты Article из базы данных
    *
    * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
    * @param int $categoryId Вернуть статьи только из категории с указанным ID
    * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
    * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
    */
    public static function getList($numRows = 1000000, $categoryId = null, 
    $subcategoryId = null, $order = "publicationDate DESC") 
    { $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);

    $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) AS publicationDate 
            FROM articles 
            WHERE active = 1";
    
    $params = array();
    
    if ($categoryId) {
        $sql .= " AND categoryId = :categoryId";
        $params[':categoryId'] = $categoryId;
    }
    
    if ($subcategoryId) {
        $sql .= " AND subcategoryId = :subcategoryId";
        $params[':subcategoryId'] = $subcategoryId;
    }
    
    $sql .= " ORDER BY $order LIMIT :numRows";
    
    $st = $conn->prepare($sql);
    $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
    
    foreach ($params as $key => $value) {
        $st->bindValue($key, $value, PDO::PARAM_INT);
    }
    
    $st->execute();
    $list = array();
    
    while ($row = $st->fetch()) {
        $article = new Article($row);
        $list[] = $article;
    }
    
    $sqlCount = "SELECT COUNT(*) AS totalRows FROM articles WHERE active = 1";
    if ($categoryId) $sqlCount .= " AND categoryId = :categoryId";
    if ($subcategoryId) $sqlCount .= " AND subcategoryId = :subcategoryId";
    
    $stCount = $conn->prepare($sqlCount);
    foreach ($params as $key => $value) {
        $stCount->bindValue($key, $value, PDO::PARAM_INT);
    }
    
    $stCount->execute();
    $totalRows = $stCount->fetch();
    $conn = null;
    
    return array(
        "results" => $list, 
        "totalRows" => $totalRows[0]
    );
    }
    
    public static function getListAll($numRows=1000000, $categoryId=null, $subcategoryId=null, $order="publicationDate DESC") 
    {
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $fromPart = "FROM articles";
    
    $categoryClause = $categoryId ? "WHERE categoryId = :categoryId" : "";
    
    $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) 
            AS publicationDate
            $fromPart $categoryClause
            ORDER BY  $order  LIMIT :numRows";
    
    $st = $conn->prepare($sql);
    $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);

    if ($categoryId) 
        $st->bindValue( ":categoryId", $categoryId, PDO::PARAM_INT);

    if ($subcategoryId) 
        $st->bindValue( ":subcategoryId", $subcategoryId, PDO::PARAM_INT);
    
    $st->execute();
    $list = array();

    while ($row = $st->fetch()) {
        $article = new Article($row);
        $list[] = $article;
    }

    // Получаем общее количество статей
    $sql = "SELECT COUNT(*) AS totalRows $fromPart $categoryClause";
    $st = $conn->prepare($sql);
    if ($categoryId) 
        $st->bindValue( ":categoryId", $categoryId, PDO::PARAM_INT);
    $st->execute();                    
    $totalRows = $st->fetch();
    $conn = null;
    
    return array(
        "results" => $list, 
        "totalRows" => $totalRows[0]
    );
    }
    /**
    * Вставляем текущий объект Article в базу данных, устанавливаем его ID
    */
   public function insert() {
    if (!is_null($this->id)) trigger_error("Article::insert(): Attempt to insert an Article object that already has its ID property set.", E_USER_ERROR);

    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "INSERT INTO articles (publicationDate, categoryId, subcategoryId, title, summary, content, active, views) 
            VALUES (FROM_UNIXTIME(:publicationDate), :categoryId, :subcategoryId, :title, :summary, :content, :active, :views)";
    
    $st = $conn->prepare($sql);
    $st->bindValue(":publicationDate", $this->publicationDate, PDO::PARAM_INT);
    $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
    
    // Исправьте и здесь
    if (!empty($this->subcategoryId)) {
        $st->bindValue(":subcategoryId", $this->subcategoryId, PDO::PARAM_INT);
    } else {
        $st->bindValue(":subcategoryId", null, PDO::PARAM_NULL);
    }
    
    $st->bindValue(":title", $this->title, PDO::PARAM_STR);
    $st->bindValue(":summary", $this->summary, PDO::PARAM_STR);
    $st->bindValue(":content", $this->content, PDO::PARAM_STR);
    $st->bindValue(":active", $this->active, PDO::PARAM_INT);
    $st->bindValue(":views", $this->views, PDO::PARAM_INT);
    $st->execute();
    $this->id = $conn->lastInsertId();
    $conn = null;
      if (!empty($this->authors)) {
            $authorIds = array();
            foreach ($this->authors as $author) {
                $authorIds[] = is_object($author) ? $author->id : $author;
            }
            $this->setAuthors($authorIds);
        }
    }

    /**
    * Обновляем текущий объект статьи в базе данных
    */
   public function update() {
    if (is_null($this->id)) trigger_error("Article::update(): Attempt to update an Article object that does not have its ID property set.", E_USER_ERROR);

    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "UPDATE articles SET publicationDate = FROM_UNIXTIME(:publicationDate), 
            categoryId = :categoryId, subcategoryId = :subcategoryId, 
            title = :title, summary = :summary, content = :content, 
            active = :active, views = :views WHERE id = :id";
    
    $st = $conn->prepare($sql);
    $st->bindValue(":publicationDate", $this->publicationDate, PDO::PARAM_INT);
    $st->bindValue(":categoryId", $this->categoryId, PDO::PARAM_INT);
    
    // Исправьте эту строку: если subcategoryId пустой - ставим NULL
    if (!empty($this->subcategoryId)) {
        $st->bindValue(":subcategoryId", $this->subcategoryId, PDO::PARAM_INT);
    } else {
        $st->bindValue(":subcategoryId", null, PDO::PARAM_NULL);
    }
    
    $st->bindValue(":title", $this->title, PDO::PARAM_STR);
    $st->bindValue(":summary", $this->summary, PDO::PARAM_STR);
    $st->bindValue(":content", $this->content, PDO::PARAM_STR);
    $st->bindValue(":active", $this->active, PDO::PARAM_INT);
    $st->bindValue(":views", $this->views, PDO::PARAM_INT);
    $st->bindValue(":id", $this->id, PDO::PARAM_INT);
    $st->execute();
    $conn = null;
    if (!empty($this->authors)) {
            $authorIds = array();
            foreach ($this->authors as $author) {
                $authorIds[] = is_object($author) ? $author->id : $author;
            }
            $this->setAuthors($authorIds);
        }
    }


    /**
    * Удаляем текущий объект статьи из базы данных
    */
    public function delete() {

      // Есть ли у объекта статьи ID?
      if ( is_null( $this->id ) ) trigger_error ( "Article::delete(): Attempt to delete an Article object that does not have its ID property set.", E_USER_ERROR );

      // Удаляем статью
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $st = $conn->prepare ( "DELETE FROM articles WHERE id = :id LIMIT 1" );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }


    public static function getBySubcategoryId($subcategoryId, $numRows = 1000000) {
        return self::getList($numRows, null, $subcategoryId);
    }

    public function getAuthors() {
        if (is_null($this->id)) return array();
        
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT u.* FROM users u 
                INNER JOIN article_authors aa ON u.id = aa.user_id 
                WHERE aa.article_id = :articleId";
        $st = $conn->prepare($sql);
        $st->bindValue(":articleId", $this->id, PDO::PARAM_INT);
        $st->execute();
        
        $authors = array();
        while ($row = $st->fetch()) {
            $authors[] = new User($row);
        }
        $conn = null;
        
        $this->authors = $authors;
        return $authors;
    }

    public function setAuthors($authorIds) 
    { 
        if (is_null($this->id)) return false;
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    

        $sql = "DELETE FROM article_authors WHERE article_id = :articleId";
        $st = $conn->prepare($sql);
        $st->bindValue(":articleId", $this->id, PDO::PARAM_INT);
        $st->execute();
    
        $validAuthorIds = array();
        if (!empty($authorIds) && is_array($authorIds)) {
            foreach ($authorIds as $userId) {
                if (!empty($userId) && is_numeric($userId) && $userId > 0) {
                    $validAuthorIds[] = (int)$userId;
                }
            }
        }
    
        if (!empty($validAuthorIds)) {
            $sql = "INSERT INTO article_authors (article_id, user_id) VALUES (:articleId, :userId)";
            $st = $conn->prepare($sql);
        
            foreach ($validAuthorIds as $userId) {
                $st->bindValue(":articleId", $this->id, PDO::PARAM_INT);
                $st->bindValue(":userId", $userId, PDO::PARAM_INT);
                $st->execute();
            }
        }
    
        $conn = null;
        return true;  
    }
    
    public function increaseViews(){
        if (is_null($this->id)) {
        trigger_error("Article::increaseViews(): this bad", E_USER_ERROR);
        return false;
        }

        $this->views++;
        $this->views++;
    
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    
        $sql = "UPDATE articles SET views = :views WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
        $st->bindValue(":views", $this->views, PDO::PARAM_INT);
    
        $result = $st->execute();
        $conn = null;   
           
        return $result;                
    }
}
