<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

<h1><?php echo htmlspecialchars($results['pageTitle']) ?></h1>

<form action="admin.php?action=<?php echo $results['formAction'] ?>" method="post">
    <input type="hidden" name="subcategoryId" value="<?php echo $results['subcategory']->id ?>">

    <?php if (isset($results['errors'])) { ?>
        <div class="errors"><?php echo $results['errors'] ?></div>
    <?php } ?>

    <ul>
        <li>
            <label for="name">Название подкатегории</label>
            <input type="text" name="name" id="name" placeholder="Название подкатегории" required autofocus maxlength="255" value="<?php echo htmlspecialchars($results['subcategory']->name) ?>">
        </li>

        <li>
            <label for="category_id">Категория</label>
            <select name="category_id" id="category_id" required>
                <option value="">-- Выберите категорию --</option>
                <?php 
                // Проверяем, существует ли массив категорий
                if (isset($results['categories']) && is_array($results['categories'])) {
                    foreach ($results['categories'] as $category) { 
                        if ($category && isset($category->id, $category->name)) {
                ?>
                    <option value="<?php echo $category->id ?>" 
                        <?php echo (isset($results['subcategory']->category_id) && 
                                  $category->id == $results['subcategory']->category_id) ? "selected" : "" ?>>
                        <?php echo htmlspecialchars($category->name) ?>
                    </option>
                <?php 
                        }
                    }
                } else {
                    echo '<option value="">Нет доступных категорий</option>';
                }
                ?>
            </select>
        </li>
    </ul>

    <div class="buttons">
        <input type="submit" name="saveChanges" value="Сохранить изменения">
        <input type="submit" formnovalidate name="cancel" value="Отмена">
    </div>
</form>

<?php if ($results['subcategory']->id) { ?>
    <p><a href="admin.php?action=deleteSubcategory&amp;subcategoryId=<?php echo $results['subcategory']->id ?>" onclick="return confirm('Удалить эту подкатегорию?')">
        Удалить подкатегорию
    </a></p>
<?php } ?>

<?php include "templates/include/footer.php" ?>