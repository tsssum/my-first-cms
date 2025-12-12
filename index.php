<?php

//phpinfo(); die();

require("config.php");

try {
    initApplication();
} catch (Exception $e) { 
    $results['errorMessage'] = $e->getMessage();
    require(TEMPLATE_PATH . "/viewErrorPage.php");
}


function initApplication()
{
    $action = isset($_GET['action']) ? $_GET['action'] : "";

    switch ($action) {
        case 'archive':
          archive();
          break;
        case 'viewArticle':
          viewArticle();
          break;
        default:
          homepage();
    }
}

function archive() 
{
    $results = [];
    
    $categoryId = ( isset( $_GET['categoryId'] ) && $_GET['categoryId'] ) ? (int)$_GET['categoryId'] : null;
    
    $results['category'] = Category::getById( $categoryId );    
    
    $subcategoryId = ( isset( $_GET['subcategoryId'] ) && $_GET['subcategoryId'] ) ? (int)$_GET['subcategoryId'] : null;    
    $results['subcategory'] = Subcategory::getById( $subcategoryId );

    $data = Article::getList( 100000, $results['category'] ? $results['category']->id : null, $results['subcategory'] ? $results['subcategory']->id : null );
    
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    foreach ($results['articles'] as $article) {
        $article->getAuthors();
    }
    $data = Category::getList();
    $results['categories'] = array();
    
    foreach ( $data['results'] as $category ) {
        $results['categories'][$category->id] = $category;
    }

    $data = Subcategory::getList();
    $results['subcategories'] = array();
    
    foreach ( $data['results'] as $subcategory ) {
        $results['subcategories'][$subcategory->id] = $subcategory;
    }

    foreach ($results['articles'] as $article) {
        $article->increaseViews(); 
    }

    if ($subcategoryId && $results['subcategory']) {
        $results['pageHeading'] = $results['subcategory']->name;
    } else if ($categoryId && $results['category']) {
        $results['pageHeading'] = $results['category']->name;
    } else {
        $results['pageHeading'] = "Article Archive";
    }
    $results['pageTitle'] = $results['pageHeading'] . " | Widget News";
    
    require( TEMPLATE_PATH . "/archive.php" );
}

/**
 * Загрузка страницы с конкретной статьёй
 * 
 * @return null
 */
function viewArticle() 
{   
    if ( !isset($_GET["articleId"]) || !$_GET["articleId"] ) {
      homepage();
      return;
    }

    $results = array();
    $articleId = (int)$_GET["articleId"];
    $results['article'] = Article::getById($articleId);
    
    if (!$results['article']) {
        throw new Exception("Статья с id = $articleId не найдена");
    }   

    $results['article']->increaseViews();

    $results['article']->getAuthors();

    $results['category'] = Category::getById($results['article']->categoryId);
    $results['subcategory'] = Subcategory::getById($results['article']->subcategoryId);
    $results['pageTitle'] = $results['article']->title . " | Простая CMS";
    
    require(TEMPLATE_PATH . "/viewArticle.php");
}

/**
 * Вывод домашней ("главной") страницы сайта
 */
function homepage() 
{
    $results = array();
    $data = Article::getList(HOMEPAGE_NUM_ARTICLES);
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];

    foreach ($results['articles'] as $article) {
        $article->increaseViews(); 
    }

    $data = Category::getList();
    $results['categories'] = array();
    foreach ( $data['results'] as $category ) { 
        $results['categories'][$category->id] = $category;
    } 

    $data = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ( $data['results'] as $subcategory ) { 
        $results['subcategories'][$subcategory->id] = $subcategory;
    } 
    
    
    $results['pageTitle'] = "Простая CMS на PHP";
    require(TEMPLATE_PATH . "/homepage.php");
    
}