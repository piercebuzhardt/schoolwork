<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'Organization Status Overview';
include('../php/header.php'); ?>
<main>
    <?php
    //admin only page
    authenticate_admin($db);
    ?>

    <!-- Search bar -->
    <form name='search' method='POST' action='../pages/org_statuses.php'>
        <table>
            <tr>
                <td><label for='reggiespon'>Sponsor Org Like:</label><input name='reggiespon'
                                                                            type='text' <?php if (isset($_POST['reggiespon'])) echo "value='$_POST[reggiespon]'"; ?>>
                    <input class='button' type='submit'></td>
            </tr>
        </table>
    </form>
    <table border='1px'>
        <?php
        // Clearable searches
        if (isset($_POST['searchspon']) && $_POST['reggiespon'] == '') unset($_POST['reggiespon']);

        // Select general SQL or limited SQL based on search (if applicable)
        $sql = "SELECT * FROM `sponsor_org_creation_deletion` INNER JOIN `sponsor_org` ON `sponsor_org_creation_deletion`.`sponsor_org_id` = `sponsor_org`.`sponsor_org_id`";
        if (isset($_POST['reggiespon']) && $_POST['reggiespon'] != '') {
            $sql .= " WHERE `org_name` RLIKE '$_POST[reggiespon]'";
        } else {
            $sql .= " WHERE 1";
        }
	$sql .= " ORDER BY `sponsor_org_creation_deletion`.`event_time` DESC";
        // Do the search
        $result = $db->query($sql);
        if (($nrows = mysqli_num_rows($result)) != 0) {

            echo "<tr><td>Sponsor Org</td><td>Action</td><td>Time</td></tr>";
            for ($i = 0; $i < $nrows; $i += 1) {
                $row = mysqli_fetch_array($result);
                echo "<tr><td><a href='../pages/view_org.php?view_org=" . $row['sponsor_org_id'] . "'>$row[org_name]</a></td>";
                $temp = $row[was_created];
                if ($temp == 1)
                    echo "<td>Added</td>";
                else
                    echo "<td>Removed</td>";
                echo "<td>$row[event_time]</td></tr>";
            }
        }
        ?>
    </table>
</main>
<?php include('../php/footer.php'); ?>
</html>
