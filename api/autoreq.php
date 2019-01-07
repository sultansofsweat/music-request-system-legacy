<?php	
	//Change working directory to ensure proper operation
	chdir("..");
	//Set the system error handler
	if(file_exists("backend/apierrorhandler.php"))
	{
		include("backend/apierrorhandler.php");
	}
    elseif(file_exists("backend/errorhandler.php"))
	{
		include("backend/errorhandler.php");
	}
	else
	{
		trigger_error("Failed to invoke system error handler. Expect information leakage.",E_USER_WARNING);
	}
	//Include useful functions
	if(file_exists("backend/functions.php"))
	{
		include("backend/functions.php");
	}
	else
	{
		if (!function_exists('http_response_code'))
		{
			function http_response_code($newcode = NULL)
			{
				static $code = 200;
				if($newcode !== NULL)
				{
					header('X-PHP-Response-Code: '.$newcode, true, $newcode);
					if(!headers_sent())
						$code = $newcode;
				}       
				return $code;
			}
		}
		die(http_response_code(500));
	}
	//Set error levels
	switch(get_system_setting("errlvl"))
	{
		case 0:
		ini_set("error_reporting",E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
		break;
		case 2:
		ini_set("error_reporting",E_ALL);
		break;
		case 1:
		default:
		ini_set("error_reporting",E_ALL & ~E_NOTICE);
		break;
	}
	
	//Get all necessary settings
	$allowed=get_system_setting("interface");
	if($allowed == "yes")
	{
		$allowed=true;
	}
	else
	{
		$allowed=false;
	}
	$key=get_system_setting("autokey");
	$pagenable=get_system_setting("apipages");
	$default="<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">\r\n
<html>\r\n
  <head>\r\n
    <meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">\r\n
    <meta name=\"generator\" content=\"CoffeeCup HTML Editor (www.coffeecup.com)\">\r\n
    <meta name=\"created\" content=\"Thu, 2 Nov 2017 18:41:42 GMT\">\r\n
    <meta name=\"description\" content=\"\">\r\n
    <meta name=\"keywords\" content=\"\">\r\n
	<link rel=\"shortcut icon\" href=\"backend/favicon.ico\">\r\n
    <title>What Are You Doing Here?</title>\r\n
    
    <style type=\"text/css\">\r\n
    <!--\r\n
    body {\r\n
      color:#000000;\r\n
      background-color:#FFFFFF;\r\n
      background-image:url('../backend/background.gif');\r\n
      background-repeat:repeat;\r\n
    }\r\n
    a  { color:#FFFFFF; background-color:#0000FF; }\r\n
    a:visited { color:#FFFFFF; background-color:#800080; }\r\n
    a:hover { color:#000000; background-color:#00FF00; }\r\n
    a:active { color:#000000; background-color:#FF0000; }\r\n
    -->\r\n
    </style>\r\n
  </head>\r\n
  <body>\r\n
  <h1 style=\"text-align:center; text-decoration:underline;\">What Are You Doing Here?</h1>\r\n
  <img style=\"display:block; margin-left:auto; margin-right:auto;\" src=\"../backend/forbidden.png\" alt=\"You are not wanted here...\" title=\"You are not wanted here...\"><br>\r\n
  <p>You have attempted to access something you do not have permissions to access. Your computer will be microwaved if you do not <a href=\"../index.php\">leave</a> immediately. Save your computer the trouble!</p>\r\n
  </body>\r\n
</html>";

	if(is_logging_enabled() === true)
	{
		set_timezone();
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempted to decline post $post via API");
		if($allowed == "yes")
		{
			if(in_array(1,$pagenable))
			{
				if($key != "" && isset($_POST['key']) && password_verify($_POST['key'],$key) === true)
				{
					$rawposts=get_requests();
					$posts=array();
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained all posts");
					if(count($rawposts) > 0)
					{
						//OUTPUT FORMAT: ID|User|Date|Request|Filename|Status|Comment|Response
						foreach($rawposts as $post)
						{
							$details=array();
							$details[]=$post[0];
							$details[]=$post[1];
							$details[]=$post[3];
							$details[]=$post[4];
							$details[]=$post[8];
							$details[]=$post[5];
							if($post[7] != "")
							{
								$details[]=$post[7];
							}
							else
							{
								$details[]="None";
							}
							
							if($post[6] != "")
							{
								$details[]=$post[6];
							}
							else
							{
								$details[]="None";
							}
							$posts[]=$details;
						}
						for($i=0;$i<count($posts);$i++)
						{
							$posts[$i]=implode("&",$posts[$i]);
						}
						$posts=implode("\n",$posts);
						http_response_code(200);
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully got requests.");
						echo stripcslashes($posts);
					}
					else
					{
						http_response_code(204);
						write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to get requests: none of the darned things exist!");
						echo $default;
					}
				}
				else
				{
					http_response_code(403);
					echo $default;
				}
			}
			else
			{
				http_response_code(404);
				echo $default;
			}
		}
		else
		{
			http_response_code(410);
			echo $default;
		}
	}
	else
	{
		if($allowed == "yes")
		{
			if(in_array(1,$pagenable))
			{
				if($key != "" && isset($_POST['key']) && password_verify($_POST['key'],$key) === true)
				{
					$rawposts=get_requests();
					$posts=array();
					if(count($rawposts) > 0)
					{
						//OUTPUT FORMAT: ID|User|Date|Request|Filename|Status|Comment|Response
						foreach($rawposts as $post)
						{
							$details=array();
							$details[]=$post[0];
							$details[]=$post[1];
							$details[]=$post[3];
							$details[]=$post[4];
							$details[]=$post[8];
							$details[]=$post[5];
							if($post[7] != "")
							{
								$details[]=$post[7];
							}
							else
							{
								$details[]="None";
							}
							
							if($post[6] != "")
							{
								$details[]=$post[6];
							}
							else
							{
								$details[]="None";
							}
							$posts[]=$details;
						}
						for($i=0;$i<count($posts);$i++)
						{
							$posts[$i]=implode("&",$posts[$i]);
						}
						$posts=implode("\n",$posts);
						http_response_code(200);
						echo stripcslashes($posts);
					}
					else
					{
						http_response_code(204);
						echo $default;
					}
				}
				else
				{
					http_response_code(403);
					echo $default;
				}
			}
			else
			{
				http_response_code(404);
				echo $default;
			}
		}
		else
		{
			http_response_code(410);
			echo $default;
		}
	}
	
	/*if(is_logging_enabled() === true)
	{
		//Logging enabled
		$_SERVER['REMOTE_ADDR']=$_SERVER['REMOTE_ADDR'];
		write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Attempted to get requests");
		if($allowed === true)
		{
			//This is permitted, check the key
			if($key != "" && isset($_POST['key']) && password_verify($_POST['key'],$key))
			{
				//Key is valid, get all requests
                $rawposts=get_requests();
				$posts=array();
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained all posts");
				if(count($rawposts) > 0)
				{
					//OUTPUT FORMAT: ID|User|Date|Request|Filename|Status|Comment|Response
					foreach($rawposts as $post)
					{
						//Make new array for details
						$details=array();
                        //Only keep the details that we want
                        $details[]=$post[0];
                        $details[]=$post[1];
                        $details[]=$post[3];
                        $details[]=$post[4];
                        $details[]=$post[8];
                        $details[]=$post[5];
                        $details[]=$post[7];
                        $details[]=$post[6];
						//Add details array to posts array
						$posts[]=$details;
					}
					//Format details
					for($i=0;$i<count($posts);$i++)
					{
						$posts[$i]=implode("&",$posts[$i]);
					}
					$posts=implode("\n",$posts);
					//Output posts
					http_response_code(200);
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Successfully got requests.");
					echo stripcslashes($posts);
				}
				else
				{
					//No posts exist
					http_response_code(404);
					write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to get requests: none of the darned things exist!");
					echo $default;
				}
			}
			elseif($key == "")
			{
				//Key is not configured
				http_response_code(500);
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to check system status: key not configured");
				echo $default;
			}
			else
			{
				//Assume the user entered the wrong key
				http_response_code(403);
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to check system status: incorrect key supplied");
				echo $default;
			}
		}
		else
		{
			//This is not permitted
			http_response_code(410);
			write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Failed to check system status: system not configured to allow this");
			echo $default;
		}
	}
	else
	{
		//Logging disabled
		if($allowed === true)
		{
			//This is permitted, check the key
			if($key != "" && isset($_POST['key']) && password_verify($_POST['key'],$key))
			{
				//Key is valid, get all requests
				//Key is valid, get all requests
                $rawposts=get_requests();
				$posts=array();
				write_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"Obtained all posts");
				if(count($rawposts) > 0)
				{
					//OUTPUT FORMAT: ID|User|Date|Request|Filename|Status|Comment|Response
					foreach($rawposts as $post)
					{
						//Make new array for details
						$details=array();
                        //Only keep the details that we want
                        $details[]=$post[0];
                        $details[]=$post[1];
                        $details[]=$post[3];
                        $details[]=format_request($post[4]);
                        $details[]=$post[8];
                        $details[]=$post[5];
                        $details[]=$post[7];
                        $details[]=$post[6];
						//Add details array to posts array
						$posts[]=$details;
					}
					//Format details
					for($i=0;$i<count($posts);$i++)
					{
						$posts[$i]=implode("&",$posts[$i]);
					}
					$posts=implode("\n",$posts);
					//Output posts
					http_response_code(200);
					echo stripcslashes($posts);
				}
				else
				{
					//No posts exist
					http_response_code(404);
					echo $default;
				}
			}
			elseif($key == "")
			{
				//Key is not configured
				http_response_code(500);
				echo $default;
			}
			else
			{
				//Assume the user entered the wrong key
				http_response_code(403);
				echo $default;
			}
		}
		else
		{
			//This is not permitted
			http_response_code(410);
			echo $default;
		}
	}*/
?>