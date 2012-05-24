<html>
	<head>
		<title>Facebook Profile Crawler</title>
	</head>
	<body>
		<?php
		if (version_compare(PHP_VERSION, '5.0.0', '<'))
			echo 'This script run only on PHP 5 or higher, your PHP version : ' . PHP_VERSION;
		else {
		?>
		<form method="POST" action="php5Extractor.php">
			<table>
				<tr><td><h3>Enter your facebook email account and the account password</h3></td></tr>
				<tr>
					<td>
						<table cellspacing=10>
							<tr>
								<td>Email :</td>
								<td><input type="text" name="email" size="25" /></td>
			  				</tr>
			  				<tr>
			  					<td>Password :</td>
			  					<td><input type="password" name="password" size="25" /></td>
			  				</tr>
			  				<tr><td colspan="2"><input type="submit" value="Get Results"/></td></tr>
			  			</table>
			  		</td>
			  	</tr>
			</table>
		</form>
		<?php } ?>

		&copy 2009 TUFaT.com, All Rights Reserved


	</body>
</html>