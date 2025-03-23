<?php
	session_start();
	session_destroy();
	echo '<script>alert("Anda Telah Logout dari Simkanti");window.location="login.php"</script>';
?>
