<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'Admin Reports & Management';
include('../php/header.php'); ?>
<main>
    <?php
    authenticate_admin($db);

    ?>
    <h3>Reports</h3>
    <a href='../pages/pointoverview.php'>Point Overview</a><br>
    <a href='../pages/orderoverview.php'>Order Overview</a><br>
    <a href='../pages/org_changes.php'>Organization Changes</a><br>
    <a href='../pages/org_statuses.php'>Organization Statuses</a><br>
    <a href='../pages/productorders.php'>Product Orders</a><br>
    <br><hr><br>
    <h3>Entity Management</h3>
    <a href='../pages/userlist.php'>User List</a><br>
    <a href='../pages/create_account.php'>Create an Account</a><br>
    <a href='../pages/orgcontrol.php'>Create or Delete an Organization</a><br>

    <?php include('../php/footer.php'); ?>
