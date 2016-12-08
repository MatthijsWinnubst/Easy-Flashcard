<?php include("includes/layouts/header.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php require_once("includes/functions.php"); ?>

<!-- checken of er al een user in de SESSION zit -> cardbox.php, anders naar login.php -->

<?php redirect_to("login.php");?>