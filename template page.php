<?php require_once("includes/session.php"); ?>
<?php require_once("includes/db_connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>

<?php

	// Process the GET- en POST-variabelen
	
	// Validations

	// 2. Perform database query


    $query  = "";
	$query .= "";

	$result = mysqli_query($connection, $query);
	confirm_query($result);
?>	

<?php include("includes/layouts/header.php"); ?>

<?php

	//
	// page body HTML
	// page body HTML
	// page body HTML
	//
	
	// 3. Use query results	
	// 4. close result
	mysqli_free_result($result);

?>

<?php include("includes/layouts/footer.php"); ?>
