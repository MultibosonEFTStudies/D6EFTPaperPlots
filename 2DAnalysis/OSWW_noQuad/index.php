<?php
$pwd = preg_replace("|^\/((\w)\w+)\/|","/eos/user/$2/$1/www/",$_SERVER["REQUEST_URI"]);
$pwd = preg_replace("(\?.*)","",$pwd);
preg_match("|^\/(\w+)\/|",$_SERVER["REQUEST_URI"],$usr);
$pwdshort = preg_replace("(.*www/)","$usr[1]/",$pwd);
chdir($pwd);
?>
<html>
<head>
<title><?php echo $pwdshort; ?></title>
<link rel="SHORTCUT ICON" type="image/x-icon" href="https://ineuteli.web.cern.ch/ineuteli/favicon.ico"/>
<style type='text/css'>
  body {
    font-family: "Candara", sans-serif;
    font-size: 9pt;
    line-height: 10.5pt;
  }
  div.pic h3 {
    font-size: 9pt;
    margin: 0.5em 1em 0.2em 1em;
  }
  div.pic p {
    font-size: 11pt;
    margin: 0.2em 1em 0.1em 1em;
  }
  div.pic {
    display: block;
    float: left;
    background-color: white;
    border: 1px solid #ccc;
    padding: 2px;
    text-align: center;
    margin: 2px 10px 10px 2px;
    -moz-box-shadow: 6px 4px 4px rgb(80,80,90);    /* Firefox 3.5 */
    -webkit-box-shadow: 6px 4px 4px rgb(80,80,90); /* Chrome, Safari */
    box-shadow: 6px 4px 4px rgb(80,80,90);         /* New browsers */  
    width: 320px;
    min-height: 330px;
    max-height: 380px;
  }
  h1 { color: rgb(40,40,80); }
  h2 { padding-top: 5pt; }
  h2 a { color: rgb(20,20,100); } 
  h3 a { color: rgb(40,40,120); }
  a { text-decoration: none; color: rgb(50,50,150); }
  a:hover { text-decoration: underline; color: rgb(80,80,250); }
  div.dirlinks h2 { padding-top: 0pt; margin-bottom: 4pt; margin-left: -15pt; color: rgb(20,20,80); }
  div.dirlinks { margin: 0 15pt; } 
  div.dirlinks a {
    font-size: 11pt; font-weight: bold;
    padding: 0 0.5em; 
  }
  pre {
    font-family: monospace;
    max-width:1000px;
    white-space: pre-wrap;     /* css-3 */
    white-space: -moz-pre-wrap !important; /* Mozilla */
    white-space: -pre-wrap;    /* Opera 4-6 */
    white-space: -o-pre-wrap;  /* Opera 7 */
    word-wrap:   break-word;   /* Internet Explorer 5.5+ */
  }
</style>
</head>
<body>
<h1><?php echo $pwd;?></h1>
<!-- <h1><?php echo getcwd();?></h1> -->
<?php
$has_subs = false;
foreach(glob("*") as $filename){
    if(is_dir($filename) && !preg_match("/^\..*|.*private.*/", $filename)){
      $has_subs = true;
      break;
    }
}
if($has_subs){
    print "<div class=\"dirlinks\">\n";
    print "<h2>Directories</h2>\n";
    print "<a href=\"../\">[parent]</a> ";
    foreach(glob("*") as $filename){
      if(is_dir($filename) && ($_SERVER['PHP_AUTH_USER'] == 'gpetrucc' || !preg_match("/^\..*|.*private.*/", $filename))){
          print " <a href=\"$filename\">[$filename]</a>";
      }
    }
    print "</div>";
}else{
    print "<div class=\"dirlinks\">\n";
    print "<h2><a href=\"../\">[parent]</a></h2>";
    print "</div>";
}

foreach(array("00_README.txt", "README.txt", "readme.txt") as $readme){
    if(file_exists($readme)){
        #print "<pre class='readme'>\n"; readfile($readme); print "</pre>";
        $readmeblock = file_get_contents($readme);
        #$readmeblock = preg_replace("|\[\[([^\|\]]*)\|([^\|\]]*)\]\]|","<a href=\"$1\">$2</a>",$readmeblock);
        $readmeblock = preg_replace("|\[\[((?:(?!\]\])[^\|])*)\|([^\|\]]*)\]\]|","<a href=\"$1\">$2</a>",$readmeblock);
        $readmeblock = preg_replace("|\[\[([^\|\]]*)\]\]|","<a href=\"$1\">$1</a>",$readmeblock);
        print "<pre class='readme'>\n"; print $readmeblock; print "</pre>";
    }
}
?>

<h2><a name="plots">Plots</a></h2>
<p><form>Filter: <input type="text" name="match" size="30" value="<?php if(isset($_GET['match'])) print htmlspecialchars($_GET['match']); ?>" /><input type="Submit" value="Go" /><input type="checkbox"  name="regexp" <?php if($_GET['regexp']) print "checked=\"checked\""?> >RegExp</input></form></p>
<div>
<?php
$displayed = array();

if($_GET['noplots']){
    print "Plots will not be displayed.\n";
}else{
    $other_exts = array('.pdf','.jpg','.jpeg','.jpg','.cxx','.eps','.root','.txt','.tex','.log','.dir','.info','.psd');
    $filenames = glob("*.png"); natsort($filenames);
    $keywords = explode(" ",$_GET['match']);
    foreach($filenames as $filename){
        if(isset($_GET['match'])){
          $matched = True;
          foreach($keywords as $keyword){
            if(isset($_GET['regexp']) && $_GET['regexp']){
              if(!preg_match('/.*'.$keyword.'.*/', $filename)){
                $matched = False;
                continue;
              }
            }else{
              if(!fnmatch('*'.$keyword.'*', $filename)){
                $matched = False;
                continue;
              }
            }
          }
          if(!$matched) continue;
        }
        array_push($displayed, $filename);
        $brfname = str_replace("_","_<wbr>",$filename); //&shy;
        print "<div class='pic'>\n";
        print "<h3><a href=\"$filename\">$brfname</a></h3>";
        print "<a href=\"$filename\"><img src=\"$filename\" style=\"border: none; max-width: 300px; max-height: 360px; \"></a>";
        $others = array();
        foreach($other_exts as $ex){
            $other_filename = str_replace('.png', $ex, $filename);
            if(file_exists($other_filename)){
              array_push($others, "<a class=\"file\" href=\"$other_filename\">[" . $ex . "]</a>");
              if($ex != '.txt') array_push($displayed, $other_filename);
            }
        }
        if($others) print "<p>Also as ".implode(', ',$others)."</p>";
        print "</div>";
    }
}
?>
</div>

<div style="display: block; clear:both;">
<h2><a name="files">Other files</a></h2>
<ul>
<?php
foreach(glob("*") as $filename){
    if($_GET['noplots'] || !in_array($filename, $displayed)){
        if(isset($_GET['match'])){
          if(isset($_GET['regexp']) && $_GET['regexp']){
            if(!preg_match('/.*'.$_GET['match'].'.*/', $filename)) continue;
          }else{
            if(!fnmatch('*'.$_GET['match'].'*', $filename)) continue;
          }
        }
        if(is_dir($filename)){
          print "<li>[DIR] <b><a href=\"$filename\">$filename</a></b></li>";
        }else{
          print "<li><a href=\"$filename\">$filename</a></li>";
        }
    }
}
?>
</ul>
</div>
<br>
</body>
</html>