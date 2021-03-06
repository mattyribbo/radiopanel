<?php
// RadioPanel -  Setup File
// (C) Matt Ribbins - matt@mattyribbo.co.uk
//
// This file must be deleted once run (or if manual setup has been completed);

include("inc/class.user.php");
error_reporting(E_ALL ^ E_NOTICE);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <base href="".$_SERVER['SERVER_NAME']."">
    <meta http-equiv="Content-Type" content="text/html charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <link href="style.css" rel="stylesheet" type="text/css" />
    <link href="lib/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="lib/bootstrap-theme.min.css" rel="stylesheet" type="text/css" />
    <link href="lib/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
    <title>RadioPanel Setup </title>
    <script src="lib/jquery-1.8.3.min.js"></script>
    <script src="lib/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="lib/bootstrap.min.js"></script>
    <script src="scripts.js"></script>
</head>
<body>

<div id="wrap">

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">RadioPanel</a>
		</div>
		<div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Setup</a></li>
            </ul>
		</div><!--/.nav-collapse -->
	</div>
</div>
    
    
<div class="container">


<?php


if(isset($_POST['submit'])) {
	
}
if(isset($_POST['submit']) && ($_POST['setup'] == 0)) {

	echo "<h1>Step 1</h1>\n";
	echo "<p>We need to set up a MySQL / MariaDB database. Please enter your database details.</p>\n";
	echo "<form name=\"setup\" action=\"./setup.php\" method=\"post\"><table>\n";
	echo "<tr><td>Database Host</td><td><input name=\"db_host\" type=\"text\" maxlength=\"254\" size=\"40\" value=\"\"></td></tr>";
	echo "<tr><td>Database Name</td><td><input name=\"db_name\" type=\"text\" maxlength=\"254\" size=\"40\" value=\"\"></td></tr>";
	echo "<tr><td>Database Username</td><td><input name=\"db_user\" type=\"text\" maxlength=\"254\" size=\"40\" value=\"\"></td></tr>";
	echo "<tr><td>Database Password</td><td><input name=\"db_pass\" type=\"text\" maxlength=\"254\" size=\"40\" value=\"\"></td></tr>";
	echo "<tr><td>Create database</td><td><input name=\"db_create\" type=\"checkbox\"> <strong>Note:</strong> the next step will fail if the database already exists.</td></tr>";
	echo "<input name=\"setup\" type=\"hidden\" value=\"1\">";
	echo "<tr><td></td><td><input type=\"submit\" name=\"submit\" value=\"Submit\"></td></tr>";
	echo "</table></form>";
}
else if(isset($_POST['submit']) && ($_POST['setup'] == 1)) {
	// Database
	$db_host = $_POST['db_host'];
	$db_name = $_POST['db_name'];
	$db_user = $_POST['db_user'];
	$db_pass = $_POST['db_pass'];
	$db_session = new mysqli($db_host, $db_user, $db_pass, $db_name);
	echo "<h1>Step 2</h1>\n";
	echo "<p>Radiopanel will now attempt to connect to the database and perform initial setup</p>";
	do {
		// Initial connect
		echo "<p>Connecting to database $db_host</p>";
		if($db_session->connect_error) {
			echo "<p class=\"error\">Error: Unable to connect to database. Incorrect details were provided or MySQL server does not exist</p>";
			echo "<p>Error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . ")</p>";
			break;
		}
		echo "<p>Connected - " . $db_session->host_info . "</p>";
		// If creating database, create
		if(isset($_POST['db_create'])) {
			echo "<p>Creating database: CREATE DATABASE `radiopanel`</p>";
			if(!$db_session->query("CREATE DATABASE `radiopanel` /*!40100 CHARACTER SET utf8 COLLATE 'utf8_general_ci' */;")) {
				echo "<p class=\"error\">Error: Unable to create database `radiopanel`. Either you do not have the rights to add databases or database already exists</p>";
				break;
			}
		}
		// Create tables
		echo "<p>Creating user table: CREATE TABLE `users`</p>";
		if(!$db_session->query("CREATE TABLE `users` (`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, `username` VARCHAR(50) NOT NULL DEFAULT '0', `password` VARCHAR(255) NOT NULL DEFAULT '0', `salt` VARCHAR(127) NOT NULL DEFAULT '0', `email` VARCHAR(127) NOT NULL DEFAULT '0', `access` VARCHAR(2) NOT NULL DEFAULT '0', primary key (`id`)) COMMENT='Holds user accounts' COLLATE='utf8_general_ci' ENGINE=InnoDB;")) {
			echo "<p class=\"error\">Error: Unable to create table users</p>";
			break;
		}
		echo "<p>Creating stream table: CREATE TABLE `streams`</p>";
		if(!$db_session->query("CREATE TABLE `streams` (`sid` SMALLINT UNSIGNED NULL AUTO_INCREMENT, `name` VARCHAR(64) NULL DEFAULT '0', `server` VARCHAR(256) NULL DEFAULT '0', `username` VARCHAR(64) NULL DEFAULT '0', `password` VARCHAR(64) NULL DEFAULT '0', `mountpoint` VARCHAR(64) NULL DEFAULT '0', `active` TINYINT UNSIGNED NULL DEFAULT '0', PRIMARY KEY (`sid`)) COMMENT='Holds the list of streams' COLLATE='utf8_general_ci' ENGINE=InnoDB;")) {
			echo "<p class=\"error\">Error: Unable to create table streams</p>";
			break;	
		}
		echo "<p>Creating figures table: CREATE TABLE `figures`</p>"; 
		if(!$db_session->query("CREATE TABLE `figures` (`fid` INT(16) UNSIGNED NULL AUTO_INCREMENT, `timestamp`  INT(10) UNSIGNED NULL DEFAULT NULL, `listeners` MEDIUMINT UNSIGNED NULL, PRIMARY KEY (`fid`)) COMMENT='Holds total listener figures' COLLATE='utf8_general_ci' ENGINE=InnoDB;")) {
			echo "<p class=\"error\">Error: Unable to create table figures</p>";
		}
		// Setup config.php
		echo "<p>Attempting to write config.php</p>";
		do {
			if(!$fp = fopen('config.php', 'w')) {
				echo "<p class=\"error\">Error: Unable to open config.php</p>";	
				break;
			}
			if(fwrite($fp, "<?php\n// RadioPanel - Configuration\n\n// Database\n// Hostname\n\$db_host = '$db_host';\n// Username\n\$db_user = '$db_user';\n// Database name\n\$db_name = '$db_name';\n// Password\n\$db_pass = '$db_pass';") === false) {
				echo "<p class=\"error\">Error: Unable to write to config.php</p>";	
				break;
			}
			fclose($fp);
			$config_write = true;
		} while(0);
		if($config_write === true) {
			echo "<p>Configuration successfully saved. Click 'Next' to proceed</p>";
		} else {
			echo "<p class=\"error\">Unable to save config.php</p><p>You will need to manually save and upload config.php with the lines below, or modify config.sample.php and rename config.php</p>";
			echo "<textarea rows=\"15\" cols=\"60\"><?php\n// RadioPanel - Configuration\n\n// Database\n// Hostname\n\$db_host = '$db_host';\n// Username\n\$db_user = '$db_user';\n// Database name\n\$db_name = '$db_name';\n// Password\n\$db_pass = '$db_pass';</textarea>";
		}
		echo "<form name=\"setup\" action=\"./setup.php\" method=\"post\"><input name=\"setup\" type=\"hidden\" value=\"2\"><input type=\"submit\" name=\"submit\" value=\"Next\"></form>";
		//
	} while(0);
}
else if(isset($_POST['submit']) && ($_POST['setup'] == 2)) {
	echo "<h1>Step 3</h1>";
	do {
		// Promot for new admin user account.
		echo "<p>Database has now been set up successfully. Please create an admin user account.</p>";
		echo "<h2>Account setup</h2>";
		echo "<form name=\"setup\" action=\"./setup.php\" method=\"post\"><table>\n";
		echo "<tr><td>Username</td><td><input name=\"user\" type=\"text\" maxlength=\"254\" size=\"40\" value=\"\"></td></tr>";
		echo "<tr><td>Password</td><td><input name=\"pass\" type=\"text\" maxlength=\"254\" size=\"40\" value=\"\"></td></tr>";
		echo "<tr><td>Email Address</td><td><input name=\"email\" type=\"text\" maxlength=\"254\" size=\"40\" value=\"\"></td></tr>";
		echo "<input name=\"setup\" type=\"hidden\" value=\"3\">";
		echo "<tr><td></td><td><input type=\"submit\" name=\"submit\" value=\"Submit\"></td></tr>";
		echo "</table></form>";
	} while(0);
} else if(isset($_POST['submit']) && ($_POST['setup'] == 3)) {
	$add_user = $_POST['user'];
	$add_pass = $_POST['pass'];
	$add_email = $_POST['email'];
	$config_write = false;
	echo "<h1>Step 4</h1>";
	do {
		include("config.php");
		$db_session = new mysqli($db_host, $db_user, $db_pass, $db_name);
		if($db_session->connect_error) {
			echo "<p class=\"error\">Error: Unable to connect to database.</p>";
			echo "<p>Error: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . ")</p>";
			break;
		}
		// Add user account to database
		$add_user = $db_session->real_escape_string($add_user);
		$add_pass = $db_session->real_escape_string($add_pass);
		$add_email = $db_session->real_escape_string($add_email);
		$user = new UserService($db_session);
		$result = $user->registerUser($add_user, $add_pass, $add_email, 99);
		if(!$result) {
			echo "<p class=\"error\">Error: Unable to add user account.</p>";	
			break;
		}
		echo "<p>User account added</p>";
		echo "<form name=\"setup\" action=\"./setup.php\" method=\"post\"><input name=\"setup\" type=\"hidden\" value=\"4\"><input type=\"submit\" name=\"submit\" value=\"Next\"></form>";
	} while(0);
} else if(isset($_POST['submit']) && ($_POST['setup'] == 4)) {
	echo "<h1>Step 5</h1>";
	do {
		// Promot user to set up cron job and delete this file.
		echo "<h2>Cron Job</h2>";
		echo "<p>For RadioPanel to work, you will need to setup a cron job (or scheduled task) to run periodically.<br />We recommend that the cron job is run every minute, but you may want to run less frequently.</p>";
		echo "<p>Setup cannot set this up, you need to do this manually. Open up the crontab editor and use the cron below</p>";
		echo "<br /><p class=\"code\">crontab -e<br /><br />* * * * * php ".$_SERVER['DOCUMENT_ROOT']."/index.php cron_stream</p>";
		echo "<p>If you cannot run the cron job on the same server, you can do this from a remote server by calling the URL ".$_SERVER['SERVER_NAME']."/index.php?task=cron_stream.</p><br />";
		
		echo "<h2>Delete setup file</h2>";
		echo "<p>Before you can use RadioPanel, you MUST delete this setup file. RadioPanel will not work unless you do this, and this setup file is a huge security risk if left unattended. Click 'Finish' below to attempt to delete the file and be taken to the RadioPanel homepage</p>";
		echo "<form name=\"setup\" action=\"./setup.php\" method=\"post\"><input name=\"setup\" type=\"hidden\" value=\"5\"><input type=\"submit\" name=\"submit\" value=\"Finish\"></form>";
	} while(0);
} else if(isset($_POST['submit']) && ($_POST['setup'] == 5)) {
	echo "<h1>RadioPanel Setup - Step 5</h1>";
	do {
		if(!unlink("setup.php")) {
			echo "<p class=\"error\">Error: Unable to delete setup.php. Please remove the setup.php file manually.</p>";
			echo "<a href=\"./\">Once you have deleted this fie, click here to go to RadioPanel home</a>";
		} else {
			echo "<p>Redirecting...</p>";
			echo "<script type=\"text/javascript\">window.location.replace(\"./\");</script><noscript><a href=\"./\">Click here</a></noscript>";

		}
	} while(0);
} else {
	?>
    <div class="jumbotron">
        <h1>Hello there...</h1>
        <p>We will now begin the process of setting up RadioPanel Icecast stats recorder</p>
        <p>
        	<form name="setup" action="./setup.php" method="post">
            <input name="setup" type="hidden" value="0">
            <input type="submit" name="submit" class="btn btn-lg btn-primary" value="Let's go!">
            </form>
        </p>
    </div>
    <?php
}
?>
</div>

</div>
<div id="footer">
    <div class="container">
   		<p class="text-muted">Powered by RadioPanel</p>
    </div>
</div>

</body>
</html>