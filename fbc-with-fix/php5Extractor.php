<?php

	require("font.php");

	set_time_limit (60*60*24) ;
	error_reporting(E_ERROR);

	// Print a mass matrix
	function printMas($mas) {
		for($y=0;$y<sizeof($mas);$y++) {
			for($x=0;$x<sizeof($mas[0]);$x++) {
				echo ($mas[$y][$x] == 0) ? "<b><font color=#EEEDE5>0</font></b>" : "<b><font color=black>9</font></b>";
			}
			echo "<br/>";
		}
	}

	// print a specified rectange of a mass matrix
	function printMasRect($mas, $sx, $ex, $sy, $ey) {
		for($y = $sy; $y < $ey; $y++) {
			for($x = $sx; $x < $ex; $x++) {
				echo ($mas[$y][$x] == 0) ? "<b><font color=#EEEDE5>0</font></b>" : "<b><font color=black>9</font></b>";
			}
			echo "<br/>";
		}
	}

	// get the first non-blank pixel in the mas matrix pass column
	function getFirstPixel($mas, $column) {
		for($y = 0; $y < sizeof($mas); $y++) {
			if($mas[$y][$column] != 0) return $y;
		}
	}

	// load an image and remove all not needed pixels
	function getClearImage($image, $debug = false) {

		$pngFile = fopen("email.png", "w");
		fputs($pngFile, $image);
		fclose($pngFile);

		$imageSize = @GetImageSize("email.png");
		$imageWidth = $imageSize[0];
		$imageHeight = $imageSize[1];

		$im = imagecreatefrompng("email.png");
		unlink("email.png");
		$mas = array();

		for($y=0;$y<$imageHeight;$y++) {
			for($x=0;$x<$imageWidth;$x++) {
				$rgb = ImageColorAt($im, $x, $y);
				$rgpComp = imagecolorsforindex($im, $rgb);
				if($rgpComp["red"] >= 140 || $rgpComp["green"] >= 140 || $rgpComp["blue"] >= 140) {
					$rgb = 0x000000;
				} else $rgb = 0xFFFFFF;
				imagesetpixel($im, $x, $y, $rgb);
				$mas[$y][$x] = ($rgb > 0) ? 9 : 0;
			}
		}

		//imagepng($im, "out.png");
		if($debug) printMas($mas);
		return $mas;
	}

	function checkCharBegin($mas, $charHorzEnd, $debug = false) {

		$beginPixel = getFirstPixel($mas, $charHorzEnd);

		$charsValues = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o",
		"p","q","r","s","t","u","v","w","x","y","z","@","0","1","2","3","4","5","6","7","8","9","_","-",".");

		for($letterIndex = 0; $letterIndex < sizeof($GLOBALS['char_global_mas']); ++$letterIndex) {

			$letter = $GLOBALS['char_global_mas'][$letterIndex]["char"];
			$letterBeginPixel = getFirstPixel($letter, 0);

			if($debug) echo "Check for letter : $charsValues[$letterIndex]<br/>";

			if($beginPixel - $letterBeginPixel + sizeof($letter) - 1 < sizeof($mas) &&
			   $charHorzEnd + sizeof($letter[0]) - 1 < sizeof($mas[0])) {
				if($debug) echo "Valid Width<br/>";
				if($beginPixel - $letterBeginPixel >= 0 &&
					   $beginPixel + sizeof($letter) - $letterBeginPixel < sizeof($mas)) {
					if($debug) echo "Valid Height<br/>";
					$same = true;
					for($y = 0; $y < sizeof($letter); ++$y) {
						for($x = 0; $x < sizeof($letter[0]); ++$x) {
							if($mas[$y + $beginPixel - $letterBeginPixel][$x + $charHorzEnd] != $letter[$y][$x]) {
								$same = false;
								break;
							}
						}
					}
					if($debug) echo "SAME : $same<br/>";
					if($same == true) return array("F" => true, "LetterIndex" =>$letterIndex);
				}
				if($debug) echo "COL : $charHorzEnd, BEGIN PIXEL : $beginPixel, LET_BEGIN : $letterBeginPixel<br/>";
			}
		}

		return array("F" => false, "LetterIndex" => 0);
	}

	function parseEmail($image) {

		$mas = getClearImage($image, false);

		$charsValues = array("a","b","c","d","e","f","g","h","i","j","k","p","l","m","n","o",
		"q","r","s","t","u","v","w","x","y","z","@","0","1","2","3","4","5","6","7","8","9","_","-",".");

		//checkCharBegin($mas, 87, true); return;

		$emailString = '';
		$begin = 0;
		while($begin < sizeof($mas[0])) {
			//echo "BEG : $begin<br/>";
			$res = checkCharBegin($mas, $begin);
			if($res["F"] == true) {
				$emailString .= $charsValues[$res["LetterIndex"]];
				$begin += sizeof($GLOBALS['char_global_mas'][$res["LetterIndex"]]["char"][0]);
			} else $begin++;
		}

		return $emailString;
	}

	/*$mail = "facebook.png";
	echo "<img src=\"mails/$mail\"><br/>";
	$emailImage = file_get_contents("mails/$mail");
	echo parseEmail($emailImage);
	return;

	/*$d = dir("mails");
	echo "<table>";

	while (false !== ($entry = $d->read())) {
		if($entry != '.' && $entry != '..' && $entry != 'Thumbs.db' && !is_dir($entry)) {
			echo "<tr>";
				echo "<td><img src=\"mails/$entry\"></td>";
				$emailImage = file_get_contents("mails/$entry");
				echo "<td>".parseEmail($emailImage)."</td>";
			echo "</tr>";
		}
	}

	$d->close();
	echo "</table>";
	return;*/

	function getProfileInformation($profileId, $profile) {
		$record = '';

		$record .= "\"";
		if(preg_match('/<dt>Networks:<\/dt><dd>[a-z|A-Z|0-9|\040|\-|\.|_|,]*<\/dd>/i', $profile, $matches) != 0)
			$record .= preg_replace('[<dt>Networks\:<\/dt>|<dd>|<\/dd>]', '', $matches[0]);
		else $record .= "Not Defined";;

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

	    if(preg_match('/<dt>Sex:<\/dt><dd>[a-z|A-Z]*<\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Sex\:<\/dt>|<dd>|<\/dd>]', '', $matches[0]);
		else $record .=  "Not Defined";;

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<dt>Birthday:<\/dt><dd>[a-z|A-Z|0-9|\040|\-|\.|_|,]*<\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Birthday\:<\/dt>|<dd>|<\/dd>]', '', $matches[0]);
		else $record .=  "Not Defined";;

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<dt>Hometown:<\/dt><dd>[a-z|A-Z|0-9|\040|\-|\.|_|,]*<\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Hometown\:<\/dt>|<dd>|<\/dd>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<dt>Relationship Status:<\/dt><dd>[a-z|A-Z|0-9|\040|\-|\.|_|,]*<\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Relationship Status\:<\/dt>|<dd>|<\/dd>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<dt>Mobile:<\/dt><dd>[a-z|A-Z|0-9|\040|\-|\.|_|,]*<\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Mobile\:<\/dt>|<dd>|<\/dd>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<dt>Other:<\/dt><dd>[a-z|A-Z|0-9|\040|\-|\.|_|,]*<\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Other\:<\/dt>|<dd>|<\/dd>]', '', $matches[0]);
		else $record .= "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<a href=\"\/s\.php\?adv\&amp\;k=[0-9]*\&amp\;n=[-|0-9]*\&amp\;z2=[^\&amp\;]*\&amp\;o=[0-9]*\">[^<\/a>]*<\/a>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<a href=\"\/s\.php\?adv\&amp\;k=[0-9]*\&amp\;n=[-|0-9]*\&amp\;z2=[^\&amp\;]*\&amp\;o=[0-9]*\">|<\/a>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<a href=\"\/s\.php\?adv\&amp\;k=[0-9]*\&amp\;n=[-|0-9]*\&amp\;c2=[0-9]*\&amp\;o=[0-9]*\">[^<]*<\/a>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<a href=\"\/s\.php\?adv\&amp\;k=[0-9]*\&amp\;n=[-|0-9]*\&amp\;c2=[0-9]*\&amp\;o=[0-9]*\">|<\/a>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<a href=\"\/s\.php\?adv\&amp\;k=[0-9]*\&amp\;n=[-|0-9]*\&amp\;a2=[^&]*\&amp\;o=[0-9]*\">[^<]*<\/a>/i', $profile, $matches) != 0)
			$record .=  preg_replace('/[<a href=\"\/s\.php\?adv\&amp\;k=[0-9]*\&amp\;n=[-|0-9]*\&amp\;a2=[^&]*\&amp\;o=[0-9]*\">|<\/a>]/i', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<a href=\"aim\:goim\?screenname=[^\"]*\"[^<]*>[^<]*<\/a>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<a href=\"aim\:goim\?screenname=[^\"]*\"[^<]*>|<\/a>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<dt>Google Talk\:<\/dt><dd>[^<]*<\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Google Talk\:<\/dt><dd>|<\/dd>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<dt>Windows Live\:<\/dt><dd>[^<]*<\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Windows Live\:<\/dt><dd>|<\/dd>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<dt>Skype\:<\/dt><dd>[^<]*<\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Skype\:<\/dt><dd>|<\/dd>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<dt>Yahoo\:<\/dt><dd>[^<]*<\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Yahoo\:<\/dt><dd>|<\/dd>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";
		$record .= ',';
		$record .= "\"";

		if(preg_match('/<dt>Website\:<\/dt><dd><a[^>]*>[^<]*<\/a><\/dd>/i', $profile, $matches) != 0)
			$record .=  preg_replace('[<dt>Website\:<\/dt><dd><a[^>]*>|<\/a><\/dd>]', '', $matches[0]);
		else $record .=  "Not Defined";

		$record .= "\"";

		if(preg_match_all('/<img src=\"\/string_image.php[^>]*/i', $profile, $matches) != 0) {
			$i = 0;
			for(; $i < sizeof($matches[0]) && $i < 5; ++$i) {
				$email = $matches[0][$i];
				preg_match('/src=\"[^\"]*\"/i', $email, $imgSrc);
				$emailImage = file_get_contents("http://www.facebook.com".preg_replace('[src=|\"]', '', $imgSrc[0])."<br/>");
				/*$Ifile = fopen("$profileId - ".($i).".png", "w");
				fputs($Ifile, $emailImage);
				fclose($Ifile);*/
				$record .=  ",\"".parseEmail($emailImage)."\"";
			}
			while($i++ < 5) $record .= ",\"Not Defined\"";
		} else {
			$i = 0;
			for(; $i < 5; ++$i) {
				$record .=  ",\"Not Defined\"";
			}
		}

		return $record;
	}

  function loginToFacebook($err, $errstr, $context, $header, $request_data) {
    $fp = stream_socket_client("ssl://login.facebook.com:443", $err, $errstr, 60, STREAM_CLIENT_CONNECT, $context);

    if (!$fp) {
        trigger_error('Connection Error : '.$errstr);
        return NULL;
      }

    echo "<b>Connection succeeded</b><br/>"; flush(); ob_flush();
    echo "<b>Sending login request</b><br/>"; flush(); ob_flush();

    fputs($fp, $header);
      fputs($fp, $request_data);

    $loginPage = '';

    while(!feof($fp)) { $loginPage .= fgets($fp, 2048); }

    $res_map = explode("\n",$loginPage);

      fclose($fp);

    if(true) echo "<b>Login succeeded</b><br/>";
    else echo "<b>Login Failed...Incorrect email or password</b><br/>";

     flush(); ob_flush();
    return $res_map;
  }

  function getHeader($profileId, $cookiesHeader) {
    $header =  "GET /profile.php?id=$profileId&v=info HTTP/1.1\r\n";
    $header .= "Host: www.facebook.com\r\n";
    $header .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.3a) Gecko/20021207\r\n";
    $header .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
    $header .= "Accept-Charset: windows-1256,utf-8;q=0.7,*;q=0.7\r\n";
    $header .= "Accept-Language: ar,en-us;q=0.7,en;q=0.3\r\n";
    $header .= $cookiesHeader."\r\n";
    $header .= "Connection: close\r\n\r\n";
    return $header;
  }

  function getProfileSockPointer($err, $errstr, $context, $header,
                                 $request_data) {
    $fp = stream_socket_client("www.facebook.com:80", $err, 
                               $errstr, 60, STREAM_CLIENT_CONNECT, $context);
    fputs($fp, $header);
    fputs($fp, $request_data);
    return $fp;
  }

  function getLoginCookieHeader($res_map) {
    $cookiesHeader = 'Cookie: ';

    foreach($res_map as $res_item) {
      $parts = explode(":", $res_item);
      if(trim($parts[0] == 'Set-Cookie')) {
        $innerParts = explode(";", $parts[1]);
        $cookiesHeader .= $innerParts[0].";";
      }
    }
    return $cookiesHeader;
  }

	$err = '';
	$errstr = '';

	$context = stream_context_create();
	$result = stream_context_set_option($context, 'ssl', 'verify_host', true);
	$result = stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
	$login_request_data = http_build_query(array('email'=>$_REQUEST["email"],'pass'=>$_REQUEST["password"]));

  	$login_header = "POST /login.php HTTP/1.1\r\n";
  	$login_header .= "Host: login.facebook.com\r\n";
  	$login_header .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.3a) Gecko/20021207\r\n";
	$login_header .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
	$login_header .= "Accept-Charset: windows-1256,utf-8;q=0.7,*;q=0.7\r\n";
	$login_header .= "Accept-Language: ar,en-us;q=0.7,en;q=0.3\r\n";
	$login_header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$login_header .= "Cookie: test_cookie=1\r\n";
	$login_header .= "Content-Length: ".strlen($login_request_data)."\r\n";
	$login_header .= "Connection: close\r\n\r\n";

	echo "<b>Trying to connect to login.facebook.com</b><br/>"; flush(); ob_flush();

  //------------------------------------------------------------------
  $res_map = loginToFacebook($err, $errstr, $context, 
                             $login_header, $login_request_data);
  //------------------------------------------------------------------

	echo "<b>Fetching and parsing friends page</b><br/>"; flush(); ob_flush();

	$cookiesHeader = getLoginCookieHeader($res_map);

	$header =  "GET /friends/?everyone&ref=tn HTTP/1.1\r\n";
  $header .= "Host: www.facebook.com\r\n";
  $header .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.3a) Gecko/20021207\r\n";
	$header .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
	$header .= "Accept-Charset: windows-1256,utf-8;q=0.7,*;q=0.7\r\n";
	$header .= "Accept-Language: ar,en-us;q=0.7,en;q=0.3\r\n";


	$header .= $cookiesHeader."\r\n";
	$header .= "Connection: close\r\n\r\n";

	$fp = stream_socket_client("www.facebook.com:80", $err, $errstr, 60, STREAM_CLIENT_CONNECT, $context);

	if (!$fp) {
    	trigger_error('Connection Error : '.$errstr);
    	return NULL;
  	}

	fputs($fp, $header);

	$allFriendsPage = '';
	while(!feof($fp)) { $allFriendsPage .= fgets($fp, 2048); }

	fclose($fp);

	preg_match('/You have [0-9]* friends/i', $allFriendsPage, $matches);

	echo "<b>Friends page was parsed and ".$matches[0]."</b><br/>"; flush(); ob_flush();

	preg_match_all('/Friends.dnd\(event, this, [0-9]*, \'[a-z|A-Z|0-9|\040|\-|\.|_]*\'\);/i', $allFriendsPage, $matches);
	$csvFile = "Name,Networks,Sex,Birthday,Hometown,Relationship Status,Mobile,Other,Postal Code,Current Home Town,Address,AIM,Google Talk,Windows Live,Skype,Yahoo,Website,Email #1,Email #2,Email #3,Email #4,Email #5,\r\n";

	$count = 1;

	foreach($matches[0] as $match) {
	// while ($count <= 500) {

		//if($count == 0) break;

		//$count--;

		$item = str_replace(array("Friends.dnd(event, this, ", "'", ")", ";"), "", $match);
		$parts = explode(",", $item);

		$profileId = trim($parts[0]);
		$friendName = trim($parts[1]);
		// $profileId = "860140253";
		// $friendName = "Shirish Agarwal";
		echo "<b>Extracting '$friendName' Profile Information</b><br/>"; flush(); ob_flush();

		$request_data = http_build_query(array('id'=>$profileId));

    $header = getHeader($profileId, $cookiesHeader);
    $fp = getProfileSockPointer($err, $errstr, $context, $header,
                                $request_data);
		$profilePage = '';
		while(!feof($fp)) { $profilePage .= fgets($fp, 2048); }
    // echo "$count: String length of profile page => " . strlen($profilePage) .  "<br/>";
    // flush(); ob_flush();

    // if login session expired, profile page size = 639.
    if (strlen($profilePage) <= 1000) {
      // login session expired, login again ...
      echo "<b>Login session expired, Login to facebook again ... </b><br/>"; 
      flush(); ob_flush();
      $res_map = loginToFacebook($err, $errstr, $context, 
                                 $login_header, $login_request_data);
      $cookiesHeader = getLoginCookieHeader($res_map);
      $header = getHeader($profileId, $cookiesHeader);
      $fp = getProfileSockPointer($err, $errstr, $context, $header,
                                  $request_data);
      $profilePage = '';
      while(!feof($fp)) { $profilePage .= fgets($fp, 2048); }
    }

		$csvFile .= "\"".$friendName."\",".getProfileInformation($profileId, $profilePage)."\r\n";

    fclose($fp);
    // $count++;
	}

	echo "<b>Building the CSV file</b><br/>"; flush(); ob_flush();

	$output = fopen("friends_contact_information.csv", "w");
	fputs($output, $csvFile);
	fclose($output);

	echo "Click <a href=\"friends_contact_information.csv\">here</a> to download the CSV file<br/>"; flush(); ob_flush();
?>
