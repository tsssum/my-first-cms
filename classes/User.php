<?php


/**
 * Класс для обработки пользоваталей
 */
class User
{
    // Свойства
    /**
    * @var int ID из базы данны
    */
    public $id = null;

    /**
    * @var string логин
    */
    public $username = null;

     /**
    * @var string пароль
    */
    public $password = null;

     /**
    * @var int активен ли пользователь. по умолчанию активнен
    */
    public $active = 1;
    
    /**
     * Создаст объект
     * 
     * @param array $data массив значений (столбцов) строки таблицы пользоватей
     */
    public function __construct($data=array())
    {
        
      if (isset($data['id'])) {
          $this->id = (int) $data['id'];
      }
      
        if (isset($data['username'])) {
            $this->username = $data['username'];
        }
        
        if (isset($data['password'])) {
            $this->password = $data['password'];
        }

      if (isset($data['active'])) {
          $this->active = $data['active'];  
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

      if ( isset($params['active']) ) {
        $this->active = 1;
      } else {
        $this->active = 0;
      }  

        if (isset($params['password']) && !empty($params['password'])) {
            $this->password = password_hash($params['password'], PASSWORD_DEFAULT);
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
        $sql = "SELECT * FROM users WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch();
        $conn = null;
        
        if ($row) { 
            return new User($row);
        }
    }

     /**
     * Возвращает всех пользователей
     */
     public static function getList($numRows=1000000, $order="username ASC")
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "SELECT * FROM users ORDER BY $order LIMIT :numRows";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        $st->execute();
        
        $list = array();
        while ($row = $st->fetch()) {
            $user = new User($row);
            $list[] = $user;
        }

        $sql = "SELECT COUNT(*) AS totalRows FROM users";
        $st = $conn->prepare($sql);
        $st->execute();
        $totalRows = $st->fetch();
        $conn = null;
        
        return array(
            "results" => $list,
            "totalRows" => $totalRows[0]
        );
    }

    
    /**
    * Вставляем текущий объект в базу данных, устанавливаем его ID
    */
    public function insert()
    {
        if (!is_null($this->id)) {
            trigger_error("User::insert(): Attempt to insert a User object that already has its ID property set.", E_USER_ERROR);
        }

        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $sql = "INSERT INTO users (username, password, active) VALUES (:username, :password, :active)";
        $st = $conn->prepare($sql);
        $st->bindValue(":username", $this->username, PDO::PARAM_STR);
        $st->bindValue(":password", $this->password, PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, PDO::PARAM_INT);
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }
    /**
    * Обновляем текущий объект в базе данных
    */
    public function update() {

      // Есть ли у объекта статьи ID?
      if ( is_null( $this->id ) ) trigger_error ( "User::update(): "
              . "Attempt to update an User object "
              . "that does not have its ID property set.", E_USER_ERROR );

      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      if (empty($this->password)) {
        $sql = "UPDATE users SET username = :username, active = :active WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":username", $this->username, PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, PDO::PARAM_INT);
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
    } else {
        $sql = "UPDATE users SET username = :username, password = :password, active = :active WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":username", $this->username, PDO::PARAM_STR);
        $st->bindValue(":password", $this->password, PDO::PARAM_STR);
        $st->bindValue(":active", $this->active, PDO::PARAM_INT);
        $st->bindValue(":id", $this->id, PDO::PARAM_INT);
    }
      $st->execute();
      $conn = null;
    }


    /**
    * Удаляем текущий объект статьи из базы данных
    */
    public function delete() {

      // Есть ли у объекта статьи ID?
      if ( is_null( $this->id ) ) trigger_error ( "User::delete(): Attempt to delete an User object that does not have its ID property set.", E_USER_ERROR );

      // Удаляем статью
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $st = $conn->prepare ( "DELETE FROM users WHERE id = :id LIMIT 1" );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }

}
