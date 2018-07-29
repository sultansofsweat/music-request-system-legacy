<?php
	//Set the system error handler
	if(file_exists("backend/errorhandler.php"))
	{
		include("backend/errorhandler.php");
	}
	else
	{
		trigger_error("Failed to invoke system error handler. Expect information leakage.",E_USER_WARNING);
	}
	//Include useful functions page, if it exists
	if(file_exists("backend/functions.php"))
	{
		include("backend/functions.php");
	}
	//Set error levels
	switch(get_system_setting("errlvl"))
	{
		case 0:
		error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
		break;
		case 2:
		error_reporting(E_ALL);
		break;
		case 1:
		default:
		error_reporting(E_ALL & ~E_NOTICE);
		break;
	}
?>
<?php
	//Get system name
	$sysname=system_name();
?>
<?php
	//Open session
	$altsesstore=alt_ses_store();
	if($altsesstore !== false)
	{
		session_save_path($altsesstore);
	}
	session_start();
?>
<?php
	//Administrative check function (on a separate page)
	if(file_exists("backend/securitycheck.php"))
	{
		include ("backend/securitycheck.php");
	}
	else
	{
		die("Failed to open file \"backend/securitycheck.php\" in read mode. It should now be microwaved.");
	}
?>
<?php
	//Function for setting session
	function login()
	{
		//Set session cookie
		$_SESSION['sradmin']="y";
		//Destroy existing last access time
		if(isset($_SESSION['lastaccess']) && $_SESSION['lastaccess'] != "")
		{
			unset($_SESSION['lastaccess']);
		}
		//Set the system ID (if one is set)
		$_SESSION['systemid']=get_system_setting("sysid");
		//Set tokens based on security level
		switch(intval(get_system_setting("security")))
		{
			case 1:
			//IP only
			$_SESSION['ip']=$_SERVER['REMOTE_ADDR'];
			break;
			case 2:
			//Useragent only
			$_SESSION['ua']=$_SERVER['HTTP_USER_AGENT'];
			break;
			case 3:
			//IP and useragent
			$_SESSION['ip']=$_SERVER['REMOTE_ADDR'];
			$_SESSION['ua']=$_SERVER['HTTP_USER_AGENT'];
			break;
			case 4:
			//Unique identifier only
			$_SESSION['identifier']=mt_rand(0,2000000);
			break;
			case 5:
			//Unique identifier and IP
			$_SESSION['ip']=$_SERVER['REMOTE_ADDR'];
			$_SESSION['identifier']=mt_rand(0,2000000);
			break;
			case 6:
			//Unique identifer and useragent
			$_SESSION['ua']=$_SERVER['HTTP_USER_AGENT'];
			$_SESSION['identifier']=mt_rand(0,2000000);
			break;
			case 7:
			//All the things
			$_SESSION['ip']=$_SERVER['REMOTE_ADDR'];
			$_SESSION['ua']=$_SERVER['HTTP_USER_AGENT'];
			$_SESSION['identifier']=mt_rand(0,2000000);
			break;
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="generator" content="CoffeeCup HTML Editor (www.coffeecup.com)">
    <meta name="created" content="Wed, 17 Jun 2015 12:33:52 GMT">
    <meta name="description" content="Listening to a live stream? Got a song you have to hear? This is the place to request it!">
	<link rel="shortcut icon" href="backend/favicon.ico">
    <title><?php echo $sysname; ?>Music Request System-Login</title>
    
    <style type="text/css">
    <!--
    body {
      color:#000000;
	  background-color:#FFFFFF;
      background-image:url('backend/background.gif');
      background-repeat:repeat;
    }
    a  { color:#FFFFFF; background-color:#0000FF; }
    a:visited { color:#FFFFFF; background-color:#800080; }
    a:hover { color:#000000; background-color:#00FF00; }
    a:active { color:#000000; background-color:#FF0000; }
    -->
    </style>
  </head>
  <body>
<?php
	$disabled=false;
	if(isset($_POST['s']) && $_POST['s'] == "y")
	{
		//Start submission
		if(is_logging_enabled() === true)
		{
			//Change the timezone
			set_timezone();
			//Verify password
			if(get_system_password() !== false)
			{
				if(password_verify($_POST['pass'],get_system_password()) === true)
				{
					//Run the login routine
					login();
					//Check page reference
					if(isset($_POST['ref']) && file_exists($_POST['ref'] . ".php"))
					{
						$ref=$_POST['ref'];
					}
					else
					{
						$ref="index";
					}
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully entered admin mode");
					echo("<p><b>You have been logged in successfully.</b> Click <a href=\"$ref.php\">here</a> to continue.</p>\r\n");
					$disabled=true;
				}
				else
				{
					//Trigger incorrect password error
					trigger_error("Invalid password entered",E_USER_WARNING);
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Entered invalid admin password");
				}
			}
			else
			{
				//Password file cannot be opened (no need to re-trigger an error; password function does that for us)
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to validate password");
			}
		}
		else
		{
			//Change the timezone
			set_timezone();
			//Verify password
			if(get_system_password() !== false)
			{
				if(password_verify($_POST['pass'],get_system_password()) === true)
				{
					//Run the login routine
					login();
					//Check page reference
					if(isset($_POST['ref']) && $_POST['ref'] != "" && file_exists($_POST['ref'] . ".php"))
					{
						$ref=$_POST['ref'];
					}
					else
					{
						$ref="index";
					}
					echo("<p><b>You have been logged in successfully.</b> Click <a href=\"$ref.php\">here</a> to continue.</p>\r\n");
					$disabled=true;
				}
				else
				{
					//Trigger incorrect password error
					trigger_error("Invalid password entered",E_USER_WARNING);
				}
			}
		}
	}
	else
	{
		if(is_logging_enabled() === true)
		{
			//Change the timezone
			set_timezone();
			//Logging enabled on system
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Visited admin login page");
		}
		if(securitycheck() === true)
		{
			//User is already an administrator!
			trigger_error("You are already an administrator! What are you doing here?");
			$disabled=true;
		}
		elseif(securitycheck() === false && isset($_SESSION['sradmin']) && $_SESSION['sradmin'] == "y")
		{
			//User logged in, but something wrong with security check
			trigger_error("The session you are attempting to use was partially microwaved. Perhaps it timed out, or perhaps you are a dodo bird and are trying to break in. Either way, you must log in again.");
		}
	}
	if(!file_exists("backend/password.txt"))
	{
		//Password file does not exist, throw hands up in air
		trigger_error("The password file has been abducted by Russians, and the system cannot possibly log you in. Contact the software vendor.",E_USER_ERROR);
		$disabled=true;
	}
?>
  <h1 style="text-align:center; text-decoration:underline;"><?php echo $sysname; ?>MRS-Enter Administrative Mode</h1>
  <?php if(isset($disabled) && $disabled === true) { echo("<!--\r\n"); } ?>
  <form method="post" action="login.php">
  <input type="hidden" name="s" value="y">
  <input type="hidden" name="ref" value="<?php if(isset($_GET['ref'])) { echo preg_replace("/[^a-z]/","",$_GET['ref']); } ?>">
  Password: <input type="password" name="pass" required="required"><br>
  <input type="submit" value="Log in"><input type="button" value="Cancel" onclick="window.location.href='index.php'">
  </form>
  <?php if(isset($disabled) && $disabled === true) { echo("\r\n-->\r\n"); } ?>
  </body>
</html>