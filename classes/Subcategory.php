<?php

class Subcategory
{
    // Свойства

    /**
    * @var int ID подкатегории 
    */
    public $id = null;

    /**
    * @var string Название 
    */
    public $name = null;

    /**
    * @var int ID категории 
    */
    public $category_id = null;

    /**
    *
    * @param assoc Значения свойств
    */

    /**
     * Конструктор
     */
    public function __construct($data = array()) {
        if (isset($data['id'])) $this->id = (int)$data['id'];
        if (isset($data['name'])) $this->name = (string)$data['name'];
        if (isset($data['category_id'])) $this->category_id = (int)$data['category_id'];
    }
    /**
    * Устанавливаем свойства объекта с использованием значений из формы редактирования
    *
    * @param assoc Значения из формы редактирования
    */

    public function storeFormValues ( $params ) {

      // Store all the parameters
      $this->__construct( $params );
    }


    /**
    * Возвращаем объект подкатегории, соответствующий заданному ID
    *
    * @param int
    * @return Subcategory|false Объект object или false, если запись не была найдена или в случае другой ошибки
    */

    public static function getById( $id ) 
    {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT * FROM subcategories WHERE id = :id";
        $st = $conn->prepare( $sql );
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch();
        $conn = null;
        if ($row) 
            return new Subcategory($row);
    }


    /**
    * Возвращаем все (или диапазон) объектов из базы данных
    *
    * @param int Optional Количество возвращаемых строк (по умолчаниюt = all)
    * @param string Optional Столбец, по которому сортируются категории(по умолчанию = "name ASC")
    * @return Array|false Двух элементный массив: results => массив с объектами подкатегории; totalRows => общее количество категорий
    */
    public static function getList($numRows = 1000000, $order = "name ASC", $categoryId = null) 
{ 
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    
    // Формируем SQL с условием WHERE если есть categoryId
    $whereClause = "";
    if ($categoryId) {
        $whereClause = "WHERE category_id = :categoryId";
    }
    
    $sql = "SELECT * FROM subcategories 
            $whereClause
            ORDER BY $order 
            LIMIT :numRows";

    $st = $conn->prepare($sql);
    $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
    
    if ($categoryId) {
        $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
    }
    
    $st->execute();
    $list = array();
    
    while ($row = $st->fetch()) {
        $subcategory = new Subcategory($row);
        $list[] = $subcategory;
    }

    // Получаем общее количество
    $sql = "SELECT COUNT(*) AS totalRows FROM subcategories";
    if ($categoryId) {
        $sql .= " WHERE category_id = :categoryId";
        $stCount = $conn->prepare($sql);
        $stCount->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
    } else {
        $stCount = $conn->prepare($sql);
    }
    
    $stCount->execute();
    $totalRows = $stCount->fetch();
    $conn = null;        
    
    return array("results" => $list, "totalRows" => $totalRows[0]);
}


    /**
    * Вставляем текущий объект в базу данных и устанавливаем его свойство ID.
    */

    public function insert() {
      if ( !is_null( $this->id ) ) trigger_error ( "Subategory::insert(): Attempt to insert a Subcategory object that already has its ID property set (to $this->id).", E_USER_ERROR );

      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "INSERT INTO subcategories ( name, category_id ) VALUES ( :name, :category_id )";
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":name", $this->name, PDO::PARAM_STR );
      $st->bindValue( ":category_id",  $this->category_id, PDO::PARAM_INT);
      $st->execute();
      $this->id = $conn->lastInsertId();
      $conn = null;
    }


    /**
    * Обновляем текущий объект в базе данных.
    */

    public function update() {
    if (is_null($this->id)) trigger_error("Subcategory::update(): Attempt to update a Subcategory object that does not have its ID property set.", E_USER_ERROR);

    // ОШИБКА: UPDATE categories вместо UPDATE subcategories
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "UPDATE subcategories SET name = :name, category_id = :category_id WHERE id = :id"; // ПРАВИЛЬНО
    $st = $conn->prepare($sql);
    $st->bindValue(":name", $this->name, PDO::PARAM_STR);
    $st->bindValue(":category_id", $this->category_id, PDO::PARAM_INT);
    $st->bindValue(":id", $this->id, PDO::PARAM_INT);
    $st->execute();
    $conn = null;
}


    /**
    * Удаляем текущий объект из базы данных.
    */

   public function delete() {
    if (is_null($this->id)) trigger_error("Subcategory::delete(): Attempt to delete a Subcategory object that does not have its ID property set.", E_USER_ERROR);

    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $st = $conn->prepare("DELETE FROM subcategories WHERE id = :id LIMIT 1");
    $st->bindValue(":id", $this->id, PDO::PARAM_INT);
    $st->execute();
    $conn = null;
}

}
	  
	

