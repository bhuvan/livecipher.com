<?php

  include ("./inc/dbi.inc");
  include ("./inc/hash.inc");

function draw_top_table($File = "index.php")
{
  global $HOME, $MISC, $TABLE;
  
  $TABLE["WIDTH"]   =  '100%';
  $TABLE["BORDER"]  =  '0';

  $Name  = $MISC["FULLNAME"];
  $Today = date("D M j G:i:s T Y");

  start_table($TABLE);

     echo "<tr class=dark-border>";
       echo "<td>&nbsp;</td>";
       $Page = "$HOME$File";
       echo "<td align=right><font color=white size=3>" . "&nbsp;" ; 
       echo "<b><a href=$Page>$Page</a></b>" . "</font></td>";
     echo "</tr>";

     echo "<tr class=dark-border>";
       echo "<td align=left><font size=6>" . "&nbsp;"  . $Name . "</font></td>";
       echo "<td>&nbsp;</td>";
     end_tr();

     echo "<tr class=dark-border>";
       echo "<td>&nbsp;</td>";
       $TS = strftime("%d/%m/%Y, %T %Z", filemtime($File));
       echo "<td align=right>&nbsp;<font size=1>ason</font>&nbsp;" . "<font color=gray size=4>$TS</font>" . "</td>";
     echo "</tr>";

     if ($MISC["THEME"] == 'BLUE')
     {
       echo "<tr bgcolor='#8784c6'>";
         echo "<td>&nbsp;</td>";
         echo "<td>&nbsp;</td>";
       echo "</tr>";
     }
  end_table();
  echo "<br>";
  echo "<tr><td>";
}

function draw_link_bar()
{
  global $TABLE, $SERVICES;

  $TABLE["CSPACING"]  = '1';
  $TABLE["CPADDING"]  = '3';

  $TABLE["BORDER"]  = '0';
  $TABLE["CLASS"]   = 'darkborder';

  start_table($TABLE);
    foreach ($SERVICES as $Link=>$Service)
    {
      echo "<tr>";
        echo "<td class=linkbar>";
        if ($Service == $Page)
        {
          echo "<a href='$Link'>&nbsp;>>&nbsp;$Service</a><br>";
        }
        else
        {
          echo "<a href='$Link'>&nbsp;>>&nbsp;$Service</a><br>";
        }
        echo "</td>";
      end_tr();
    }
  end_table();
  echo '
  <script type="text/javascript"><!--
  google_ad_client = "pub-9564390926410370";
  google_ad_width = 234;
  google_ad_height = 60;
  google_ad_format = "234x60_as";
  google_ad_channel ="";
  //--></script>
  <script type="text/javascript"
    src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
    </script>
  ';

  google_search_box();
  $TABLE["CSPACING"]  = '0';
  $TABLE["CPADDING"]  = '3';
}

function google_search_box()
{
echo '
<!-- Search Google -->
<center>
<form method="get" action="http://www.google.com/custom" target="_top">
<table bgcolor="#ffffff">
<tr><td nowrap="nowrap" valign="top" align="left" height="32">
<a href="http://www.google.com/">
<img src="http://www.google.com/logos/Logo_25wht.gif" border="0" alt="Google" align="middle"></img></a>
<input type="text" name="q" size="15" maxlength="255" value=""></input>
<input type="submit" name="sa" value="Search"></input>
<input type="hidden" name="client" value="pub-9564390926410370"></input>
<input type="hidden" name="forid" value="1"></input>
<input type="hidden" name="ie" value="ISO-8859-1"></input>
<input type="hidden" name="oe" value="ISO-8859-1"></input>
<input type="hidden" name="safe" value="active"></input>
<input type="hidden" name="cof" value="GALT:#008000;GL:1;DIV:#336699;VLC:663399;AH:center;BGC:FFFFFF;LBGC:336699;ALC:0000FF;LC:0000FF;T:000000;GFNT:0000FF;GIMP:0000FF;FORID:1;"></input>
<input type="hidden" name="hl" value="en"></input>
</td></tr></table>
</form>
</center>
<!-- Search Google -->
';
}

function number_append($Number = 0)
{
  $Last = substr ($Number, -1, 1);
  $LastPlusOne= substr ($Number, -2, 1);
  if ($LastPlusOne == 1 and strlen($Number) > 1)
  {
    return $Number . "th";
  }
  
  switch ($Last)
  {
    case 0: case 4: case 5: case 6: case 7: case 8: case 9: return $Number . "th";
    case 1: return $Number . "st";
    case 2: return $Number . "nd";
    case 3: return $Number . "rd";
    case 5: return $Number . "th";
  }
}


function draw_user_details()
{
  global $SERVICES, $TABLE;

  echo "<td>";
  $TABLE["BORDER"] = '0';
  $TABLE["CLASS"]  = 'darkborder';

  start_table($TABLE);
    echo "<tr class=dark-border>";
      echo "<td>";
      echo "<b>Your Details!</b>" . "</td>";
    end_tr();

    start_tr();
      echo "<td class=summary>";
      $TABLE["CPADDING"] = '3';
      $TABLE["CSPACING"] = '1';
      start_table($TABLE);
      start_tr();
        echo "<td class=content>Your IP Address is  &nbsp; " . $_SERVER["REMOTE_ADDR"] . "</td>";
      end_tr();

      insert_visitor_number();
      $Visitor_No = get_visitor_number();
      start_tr();
        echo "<td class=content>You are " . number_append($Visitor_No) . "  visitor of this website</td>";
      end_tr();

      $Page_Count  = get_page_count($_SERVER["REMOTE_ADDR"]);
      $Page        = $SERVICES[basename($_SERVER["SCRIPT_FILENAME"])];
      start_tr();
        echo "<td class=content>You are visiting this page, <font color=blue><b>$Page</b></font>, for the " . number_append($Page_Count) . " time</td>";
      end_tr();
      end_table();
      
      echo "</td>";
    echo "</tr>";
  end_table();

  echo "</td>";
}

function insert_visitor_number()
{
  global $_SERVER, $SERVICES;
  $Id = 'INSERT_VISITOR_NUMBER';

  $Values = array (
                    $_SERVER["REMOTE_ADDR"], 
                    $_SERVER["HTTP_USER_AGENT"], 
                    $SERVICES[basename($_SERVER["SCRIPT_FILENAME"])], 
                    // date("D M j G:i:s T Y")
                    date("Y-m-d H:i:s")
                  );

  $Arr = insert_record ($Id, $Values);
  return $Arr;
}

function get_visitor_number()
{
  global $SERVICES;
  $Id = 'GET_VISITOR_NUMBER';
  $Sql = build_sql ($Id, false);

  $Home = $SERVICES["index.php"];
  $Sql .= " WHERE visited_page = '$Home'";
  $Arr = execute_sql($Sql);
  return $Arr[0];
}

function get_page_count()
{
  global $_SERVER, $SERVICES;

  $Ip   = $_SERVER["REMOTE_ADDR"];
  $Page = $SERVICES[basename($_SERVER["SCRIPT_FILENAME"])];

  $Id = 'GET_PAGE_COUNT';
  $Sql = build_sql ($Id, false);

  $Sql .= " WHERE visitor_ip = '$Ip' and visited_page = '$Page'";
  $Arr = execute_sql($Sql);
  return $Arr[0];
}

?>

