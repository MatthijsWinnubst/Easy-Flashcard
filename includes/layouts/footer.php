	<footer>
    	<div class="footer">&copy; <?php echo date("Y"); ?>, Matthijs Winnubst</div>
	</footer>
</body>
</html>

<?php
  // 5. Close database connection
	if (isset($connection)) {
	  mysqli_close($connection);
	}
?>
