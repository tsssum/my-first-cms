<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
<!--        <?php echo "<pre>";
            print_r($results);
            print_r($data);
        echo "<pre>"; ?> Данные о массиве $results и типе формы передаются корректно-->

        <h1><?php echo $results['pageTitle']?></h1>

        <form action="admin.php?action=<?php echo $results['formAction']?>" method="post">
            <input type="hidden" name="articleId" value="<?php echo $results['article']->id ?>">
        <?php 
            if (!empty($results['errors'])) { ?>
        <div class="errorMessage">
            <?php foreach ($results['errors'] as $error) { ?>
                <p><?php echo htmlspecialchars($error) ?></p>
            <?php } ?>
        </div>
        <?php } ?>

            <ul>

              <li>
                <label for="title">Article Title</label>
                <input type="text" name="title" id="title" placeholder="Name of the article" required autofocus maxlength="255" value="<?php echo htmlspecialchars( $results['article']->title )?>" />
              </li>

              <li>
                <label for="summary">Article Summary</label>
                <textarea name="summary" id="summary" placeholder="Brief description of the article" required maxlength="1000" style="height: 5em;"><?php echo htmlspecialchars( $results['article']->summary )?></textarea>
              </li>

              <li>
                <label for="content">Article Content</label>
                <textarea name="content" id="content" placeholder="The HTML content of the article" required maxlength="100000" style="height: 30em;"><?php echo htmlspecialchars( $results['article']->content )?></textarea>
              </li>

              <li>
                <label for="categoryId">Article Category</label>
                <select name="categoryId">
                  <option value="0"<?php echo !$results['article']->categoryId ? " selected" : ""?>>(none)</option>
                <?php foreach ( $results['categories'] as $category ) { ?>
                  <option value="<?php echo $category->id?>"<?php echo ( $category->id == $results['article']->categoryId ) ? " selected" : ""?>><?php echo htmlspecialchars( $category->name )?></option>
                <?php } ?>
                </select>
              </li>

              <li>
                <label for="subcategoryId">Article Subcategory</label>
                <select name="subcategoryId">
                  <option value="">-- Без подкатегории --</option>
    <?php 
    // Группируем подкатегории по категориям
    $groupedSubcategories = [];
    if (isset($results['subcategories']) && is_array($results['subcategories'])) {
        foreach ($results['subcategories'] as $subcat) {
            if (!isset($groupedSubcategories[$subcat->category_id])) {
                $groupedSubcategories[$subcat->category_id] = [];
            }
            $groupedSubcategories[$subcat->category_id][] = $subcat;
        }
    }
    
    // Выводим сгруппированные
    foreach ($groupedSubcategories as $categoryId => $subcats) {
        // Находим название категории
        $categoryName = '';
        foreach ($results['categories'] as $cat) {
            if ($cat->id == $categoryId) {
                $categoryName = $cat->name;
                break;
            }
        }
        
        echo '<optgroup label="' . htmlspecialchars($categoryName) . '">';
        foreach ($subcats as $subcat) {
            echo '<option value="' . $subcat->id . '"';
            echo (isset($results['article']->subcategoryId) && 
                  $subcat->id == $results['article']->subcategoryId) ? ' selected' : '';
            echo '>' . htmlspecialchars($subcat->name) . '</option>';
        }
        echo '</optgroup>';
    }
    ?>
                </select>
              </li>

              <li>
    <label for="authorIds">Авторы статьи</label>
    <select name="authorIds[]" id="authorIds" multiple="multiple" size="5" style="height: auto;">
        <option value="">-- Без авторов --</option>
        <?php 
        $allUsers = User::getList();
        $articleAuthorIds = array();
        if (isset($results['article']->authors) && is_array($results['article']->authors)) {
            foreach ($results['article']->authors as $author) {
                $articleAuthorIds[] = $author->id;
            }
        }
        
        foreach ($allUsers['results'] as $user) { 
            if ($user->active) { 
        ?>
            <option value="<?php echo $user->id ?>" 
                <?php echo in_array($user->id, $articleAuthorIds) ? 'selected="selected"' : '' ?>>
                <?php echo htmlspecialchars($user->username) ?>
            </option>
        <?php 
            }
        } 
        ?>
    </select>
    <small style="color: #666;">Для множественного выбора зажмите Ctrl (Cmd на Mac)</small>
</li>
              <li>
                <label for="publicationDate">Publication Date</label>
                <input type="date" name="publicationDate" id="publicationDate" placeholder="YYYY-MM-DD" required maxlength="10" value="<?php echo $results['article']->publicationDate ? date( "Y-m-d", $results['article']->publicationDate ) : "" ?>" />
              </li>

              <li style="display: flex; align-items: center; gap: 10px;">
                <label for="active">Activity Status</label>
                <input type="checkbox" name="active" id="active" value="1" <?php echo $results['article']->active ? 'checked="checked"' : '' ?> 
                style="margin: 0; width: auto; transform: scale(1.2);" />
              </li>

            </ul>

            <div class="buttons">
              <input type="submit" name="saveChanges" value="Save Changes" />
              <input type="submit" formnovalidate name="cancel" value="Cancel" />
            </div>

        </form>

    <?php if ($results['article']->id) { ?>
          <p><a href="admin.php?action=deleteArticle&amp;articleId=<?php echo $results['article']->id ?>" onclick="return confirm('Delete This Article?')">
                  Delete This Article
              </a>
          </p>
    <?php } ?>
	  
<?php include "templates/include/footer.php" ?>

              