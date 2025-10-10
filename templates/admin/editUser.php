<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

    <h1><?php echo $results['pageTitle']?></h1>

    <form action="admin.php?action=<?php echo $results['formAction']?>" method="post">
        <input type="hidden" name="userId" value="<?php echo $results['user']->id ?>">

        <?php if (isset($results['errorMessage'])) { ?>
                <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
        <?php } ?>

        <ul>
            <li>
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Enter username" required autofocus maxlength="50" value="<?php echo htmlspecialchars($results['user']->username)?>" />
            </li>

            <li>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter password" maxlength="255" />
                <?php if ($results['user']->id) { ?>
                    <small style="color: #666;">Leave blank to keep current password</small>
                <?php } ?>
            </li>

            <li style="display: flex; align-items: center; gap: 10px;">
                <label for="active">Activity Status</label>
                <input type="checkbox" name="active" id="active" value="1" 
                       <?php echo $results['user']->active ? 'checked="checked"' : '' ?> 
                       style="margin: 0; width: auto; transform: scale(1.2);" />
            </li>
        </ul>

        <div class="buttons">
            <input type="submit" name="saveChanges" value="Save Changes" />
            <input type="submit" formnovalidate name="cancel" value="Cancel" />
        </div>
    </form>

    <?php if ($results['user']->id) { ?>
        <p><a href="admin.php?action=deleteUser&amp;userId=<?php echo $results['user']->id ?>" onclick="return confirm('Delete This User?')">
                Delete This User
            </a>
        </p>
    <?php } ?>

<?php include "templates/include/footer.php" ?>