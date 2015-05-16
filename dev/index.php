<html>
	<head>

		<title>Cameras</title>
		<link rel="stylesheet" media="screen" href="cams_style.css">
		<script language="javascript" type="text/javascript" src="jquery-2.0.3.js"></script>
	</head>
	<body>
		<div class="page-wrapper">
<?php
# This is a version in development that seeks to take image captures from
# the cameras and save them to a tmp sub directory
session_start();

define( 'IN_CODE', '1' );
include( 'cams_conf.php' );

$client_ip = get_ip();
$private_client = ip_is_private( $client_ip );
$proxy_exists = is_proxy();

if ( $private_client && !$proxy_exists ) {

	$loggedIn = login_status( PASSWORD_HASH );

	if ( $loggedIn == 1 ) {
		$camera_array = array();
		$camera_desc = array();
		echo '<section class="logout">';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Logged-in as: ' . $_SESSION['username'];
		echo '<br />';
		echo '<a href="logout.php">Logout</a>';
		echo '<br /><br /></section>';
		$camera_array[0] = '10.1.1.1';
		$camera_array[1] = '10.1.1.2';
		$camera_array[2] = '10.1.1.3';
		$camera_array[3] = '10.1.1.4';
		$camera_array[4] = '10.1.1.5';
		$camera_array[5] = '10.1.1.6';
		$camera_array[6] = '10.1.1.7';
		$camera_array[7] = '10.1.1.8';
		$camera_array[8] = '10.1.1.9';
		$camera_array[9] = '10.1.1.10';
		$camera_array[10] = '10.1.1.11';
		$camera_array[11] = '10.1.1.12';
		
		$GLOBALS['camera_desc'][0] = 'Room 1';
		$GLOBALS['camera_desc'][1] = 'Room 2';
		$GLOBALS['camera_desc'][2] = 'Room 3';
		$GLOBALS['camera_desc'][3] = 'Room 4';
		$GLOBALS['camera_desc'][4] = 'Room 5';
		$GLOBALS['camera_desc'][5] = 'Room 6';
		$GLOBALS['camera_desc'][6] = 'Room 7';
		$GLOBALS['camera_desc'][7] = 'Room 8';
		$GLOBALS['camera_desc'][8] = 'Room 9';
		$GLOBALS['camera_desc'][9] = 'Room 10';
		$GLOBALS['camera_desc'][10] = 'Room 11';
		$GLOBALS['camera_desc'][11] = 'Room 12';

		print_html( $camera_array );


	}	

	if ( $loggedIn == 0 ) {
		echo '<section class="banner"></section>';
		echo '<section class="login">';
		echo '<form action=' . $_SERVER['PHP_SELF'] . ' method="post">';
		echo 'Username: <input type="text" name="username"><br />';
		echo 'Password: <input type="password" name="password"><br />';
		echo '<section class="login_button">';
		echo '<input type="submit" value="Login"></form>';
		echo '</section></section>';

	}
}

else {
	header('Location: /');
}

function login_status ( $pass_hash ) {

	# Sanitizing form data input
	$username = $_POST["username"];
	$username = preg_replace('/[^a-z0-9 ]/i', '', $username);
	$password = $_POST["password"];
	$password = preg_replace('/[^a-z0-9 ]/i', '', $password);

	if ( ( $username == 'camerauser' ) && ( md5($password) == PASSWORD_HASH ) ) {
		$_SESSION['loggedin'] = 1;
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $password;
	}
	else
		$_SESSION['loggedin'] = 0;
	return $_SESSION['loggedin'];
}

function is_proxy() {
	$proxy_exists = 0;
	$proxy_headers = array(
        'HTTP_VIA',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED',
        'HTTP_CLIENT_IP',
        'HTTP_FORWARDED_FOR_IP',
        'VIA',
        'X_FORWARDED_FOR',
        'FORWARDED_FOR',
        'X_FORWARDED',
        'FORWARDED',
        'CLIENT_IP',
        'FORWARDED_FOR_IP',
        'HTTP_PROXY_CONNECTION'
    );
    foreach( $proxy_headers as $x ){
        if ( isset($_SERVER[$x]) ) 
        	$proxy_exists = 1;
    }
    return $proxy_exists;
}
	
function ip_is_private ( $ip ) {
	$pri_addrs = array (
		'10.0.0.0|10.255.255.255',
		'172.16.0.0|172.31.255.255',
		'192.168.0.0|192.168.255.255',
		'169.254.0.0|169.254.255.255',
		'127.0.0.0|127.255.255.255' );
		
	$long_ip = ip2long( $ip );
	if ( $long_ip != FALSE || $long_ip != -1 ) {
		foreach( $pri_addrs AS $pri_addr )
		{
			list( $start, $end ) = explode( '|', $pri_addr );
			
			// If is private
			if ( $long_ip >= ip2long( $start ) && $long_ip <= ip2long( $end ) )
			//return long2ip( $long_ip ) . ", " . $start . ", " . $end;
			return (1);
		}
	}
	return (0);
}

function get_ip() {
	$ip = $_SERVER['REMOTE_ADDR'];
	// If $ip is retrieved as IPv6 loopback
	if ($ip = "::1"){
		$ip = "127.0.0.0";
	}
	return $ip;
}

function get_list( $cam_array, $file_name ){
	if ( ( $handle = fopen( $file_name, "r" ) ) !== FALSE ) {
		$buff='';
		$row=1;
		while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
			$cam_array[]=$data;
		}
	return $cam_array;
	}
}

function print_html( $camlist ){
	
	
	$port = 80;
	$timeout = 1;
	$target = '/view/viewer_index.shtml?id=999';	
	$rownum = 0;
	$colnum = 0;
	$frame_ln = 5;
	$username = $_SESSION['username'];
	$password = $_SESSION['password'];
	
	$total_rows = count( $camlist );
	echo "\n".'<div class="video_wrapper">'."\n";
	while ( $rownum < $total_rows ) {
		while ( $colnum < $frame_ln && $rownum < $total_rows )  {
			$sk = fsockopen( $camlist[$rownum], $port, $errnum, $errstr, $timeout );
			switch ( !is_resource( $sk ) ) {
				case 1:
					echo '<script language="javascript" type="text/javascript">';
					echo 'alert(\'';
					echo 'Connection to ';
					echo $camlist[$rownum] . ' failed: ' . $errnum . ' ' . $errstr;
					echo '.\n\n';
					echo 'If you continue to get this error, please notify the IT Department.';
					echo '\');</script>'; 
				case 0:
					$headers = "GET" . $target . " HTTP/1.1 \r\n";
					$headers .= "Accept: */*\r\n";
					$headers .= "Accept-Language: it\r\n";
					$headers .= "Host: " . $camlist[$rownum] . "\r\n";
					$headers .= 'Authorization: Basic ' . base64_encode($username . ':' . $password);
					$headers .= "\r\n\r\n";
					
					fputs($sk,$headers);
					if ( $colnum == 0 ) {
						echo "\n<table>\n\t<tr>\n\t\t";
					}
					echo "<td>\n\t\t\t";	
					echo '<script language="javascript" type="text/javascript">';
					echo "\n\t\t\t\t";
					echo ' $(function() {';
   					echo ' $(".playback").click(function(e) {';
     				echo ' e.preventDefault();';
       				echo ' var video = $(this).next(\'video' . $rownum . '\').get(0);';
      				echo ' if (video.paused)';
           			echo ' video.play();';
       				echo ' else';
           			echo ' video.pause();';
   					echo ' });';
					echo "});\n\t\t\t</script>\n\t\t\t";
					echo '<div class="video">';
					echo "\n\t\t\t\t";
					echo '<a class="playback" href="http://' . $camlist[$rownum] . '" target="_blank" onclick="document.getElementById(\'video' . $rownum . '\').stop()">';
					echo "\n\t\t\t\t\t";
					echo '<video' . $rownum . '>';
					echo "\n\t\t\t\t\t\t";
					echo '<img src="http://' . $username . ':' . $password . '@' . $camlist[$rownum] . ':' . $port .'/mjpg/video.mjpg" id="video' . $rownum . '" alt="Browser Error / Authentication Failure" data-axis="true"/>';
					#/mjpg/video.mjpg
					#/axis-cgi/mjpg/video.cgi
					echo "\n\t\t\t\t\t";
					echo '</video' . $rownum . '>';
					echo "\n\t\t\t\t</a>\n\t\t\t</div>";
					echo "\n\t\t\t<center>";		
					echo $GLOBALS['camera_desc'][$rownum];
					echo "</center>\n\t\t\t<br />\n\t\t</td>\n\t\t";			
					$colnum++;
					$rownum++;
					copy($camlist[rownum] . '/axis-cgi/jpg/image.cgi', '/tmp/file.jpeg');
				
					if ( $colnum == $frame_ln ) {
						echo '</tr></table>';
					}
				break;
			}
		}
		$colnum = 0;
		echo "\n";
	}
	echo '</tr></table></div>';
	
}
?>
</div>
		<div class="foot">
			David Frazer - IP Camera Viewer
		</div>
	</body>

</html>