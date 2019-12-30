<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = "Edit Item";
include('../php/header.php'); ?>
<main>

<?php
$self = $_SERVER['PHP_SELF'];

if(isset($_POST['returnToCatalog']))
{
    redirect("./catalog.php");
}


if(isset($_POST['itemToEdit'])) {

    $itemToEdit = $_POST['itemToEdit'];

    $permissionToEdit = false;

    $sponsor_org_id = "";
    if($role == 'admin')
    {
        $permissionToEdit = true;
        if(isset($_POST['sponsor_org_id'])) $sponsor_org_id = $_POST['sponsor_org_id'];
    }

    if($role == 'sponsor')
    {
        $query = "SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $id;";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $sponsor_org_id = $row['sponsor_org_id'];
        }
        $permissionToEdit = true;
    }

    if(isset($_POST['submit']) && $permissionToEdit)
    {
        $query = "UPDATE catalog_items SET ";
        $isFirst = true;
        if(isset($_POST['itemCategory']) && $_POST['itemCategory'] != "")
        {
            $itemCategory = $_POST['itemCategory'];
            if(!$isFirst)
            {
                $query.=",";
            }
            else{$isFirst = false;}
            $query.= "category = '$itemCategory'";
        }
        if(isset($_POST['itemDescription']) && $_POST['itemDescription'] != "")
        {
            $itemDescription = $_POST['itemDescription'];
            if(!$isFirst)
            {
                $query.=",";
            }
            else{$isFirst = false;}
            $query.= "product_description = '$itemDescription'";
        }
        if(isset($_POST['itemPrice']) && $_POST['itemPrice'] != "")
        {
            $itemPrice = doubleval($_POST['itemPrice']);
            if(!$isFirst)
            {
                $query.=",";
            }
            else{$isFirst = false;}
            $query.= "unit_price = '$itemPrice'";
        }
        if(isset($_POST['itemImg']) && $_POST['itemImg'] != "")
        {
            $itemImg = $_POST['itemImg'];
            if(!$isFirst)
            {
                $query.=",";
            }
            else{$isFirst = false;}
            $query.= "gallery_pic = '$itemImg'";
        }

        $query.= " WHERE api_reference = '$itemToEdit' AND sponsor_org_id = '$sponsor_org_id';";

        if(!$isFirst)
        {
            $db->query($query);
        }

    }
}



?>

    <form action= <?php echo $self; ?> method="post">
    <table style="margin-left: auto; margin-right: auto; margin-top: auto; margin-bottom: auto; border-radius: 4px; padding: 8px;">
	<?php if($role == "admin") echo "<input type=hidden name=sponsor_org_id value=$sponsor_org_id>"; ?>
        <tr>
            <td colspan="2" style="text-align: center; padding-bottom: 1em;">
                Edit Item
            </td>
        </tr>
        <tr>
            <td>
                Category:
            </td>
            <td>
            <input style="float:right;" name="itemCategory" maxlength="30" >
            </td>
        </tr>
        <tr>
            <td>
                Description:
            </td>
            <td>
                <input style=" float:right;"  name="itemDescription" maxlength="200" >
            </td>
        </tr>
        <tr>
            <td>
                Price:
            </td>
            <td>
                <input style="float:right;" type="text" name="itemPrice" maxlength="40" >
            </td>
        </tr>
        <tr>
            <td>
                Image Link:
            </td>
            <td>
                <input style="float:right;" type="text" name="itemImg" maxlength="40" >
            </td>
        </tr>
        <?php echo "<input type = 'hidden' name = 'itemToEdit' value = $itemToEdit>"; ?>
        <tr>
            <td>
                <input class='button' name="submit" type="submit" value="Edit Item">
            </td>
            <td style="float:right;">
                <input class='button' name="Clear Input" type="reset" value="Clear Input">
            </td>
        </tr>
    </table>
    </form>

    <form action = './catalog.php'>
    <table style="margin-left: auto; margin-right: auto; margin-top: auto; margin-bottom: auto; border-radius: 4px; padding: 8px;">
        <tr>
            <td>
                <button class="button">Return to catalog</button>
            </td>
        </tr>
    </table>
    </form>



</main>
<?php include('../php/footer.php'); ?>
</html>
