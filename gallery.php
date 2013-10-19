<?php

//        Virasawmi PHP JPG Gallery 1.13 (5/25/06)
//
//    by: Gordon Virasawmi
//  Site: http://www.mccanime.com/

//  Description:
//
//         This program is designed to be the easiest PHP gallery manager
//       on the web.  My goal was to incorporate a simple "drop and go"
//       type installation.

//   Philosophy:
//
//         Everyone should be able to share their photos in a nice format
//       for free. The world needs more art. You can repay me by showing
//       me more of the world through your images. (and visitng my site ^__^)
//                                                             |
//                                                             |
//                            (Shameless Promotion, Doh!) ---- +
//
       
//    Donation:
//
//         If you feel this program is worth a donation, please check out my
//         website.  I plan to add a donation feature in the future.
//
//         Donations will help fund and encourage development in the future.
//

//  This package comes with 3 files:
//
//       gallery.php - This file.  This is the main module.
//       browser.php - Add on file. Creates a browseable interface for
//                     extensive, tree like galleries.
//           php.jpg - The official PHP logo.  Also used by the program to denote
//                     unrendered directories.
//

//  Install instructions:
//
//       Simply drop these files into the root of your gallery.
//
//       When surfing your webspace, click browser.php. (see advanced install notes for more detail)
//
//       That's it!
//

//  Advanced install instructions:
//
//       To incorporate your own webpage design, simply create a header.php and a
//       footer.php and place them in the same folder as the 3 main files.
// 
//       These files may be case sensitive (Linux/Unix). Make sure your files are
//       Written in lower case for this program to detect them.
//
//       The program requires write access Linux and Unix users mad need to CHMOD
//       their directories.
//
//       I've left most of the variables that you can change near the top of the
//       program. Please experiment with these varaibles to achieve your desired
//       result.
//         
//    Processor variables:  When the program starts rendering photos it will
//                          incriment exponentially per grab. (as to satisfy
//                          speed and visual effectiveness for users)
//
//           jumper = initial hopping value (I used one for the quickest response)
//
//        maxjumper = The maximum the program can incriment by.
//                    This function is very important as to limit how much
//                    is processed per grab.
//
//                    Beta testing has concluded that 20 is optimal for
//                    the following conditions:
//
//                    Server: AMD Durun 1000mhz, 256 ram, Windows 2000 SP4, your basic $200 computer
//                      HTTP: OmniHTTPd 3.05, PHP 4.3
//
//                     Files: 640 x 480 JPGs (usually 30 JPGs per gallery, but number of files
//                            is irrelivent.
//
//                            If you have bigger files or a slower computer, you may want to
//                            reduce this number.
//

//  Special thanks to every PHP tutorial on the web.

// --------------------------------------

// Updates:
//    1.11 - Recoded popup code in javascript.  Less php uploaded to client. Client side processing.
//    1.12 - Fixed jpg with spaces in name issue. (Thanks Dan)
//           Inserted width=641 tag to force proper popup window fitting.
//    1.13 - Removed "proper fitting fix (1.12)
//           Popup resizes to image size. (requested by Dan and mccanime.com users)
//
// --------------------------------------

// Set the width of the gallery table generated.
$gallerywidth=8;

// Show Gallery Title in Thumbs page.
$showgallerytitle=true;

// Show Website URL in popup.
//
// (For windows XP users using SP2, Microsoft programmed popups so that
//  they are forced to show the URL of the popup. This is a security feature that
//  helps users tell if they want to be on that site at a glance.  This option
//  allows you to choose whether you would like the URL in the popup menu bar OR
//  in an address bar.)
$showaddressbarinpopup=false;

// Render cache meta tag for browsers.  This will help speed up your web page.
$cachetopublic=true;

// See notes on "Processor Variables" near top of script.
  $maxjumper=20;
  $jumper=1;



// ------------- Include Header --------------------

$wherefrom = str_replace("%20", " ", $_SERVER['PHP_SELF']);
$pieces = explode("/", $wherefrom);
$phpwherefrom = $pieces[count($pieces)-1];
$wherefrom = $pieces[count($pieces)-2];

      if (isset($remotedir)) {$wherefrom=$remotedir;}

$wherefrom = str_replace("%20", " ", $wherefrom);
$wherefrom = str_replace("_", " ", $wherefrom);

echo '<html><head><title>'.$wherefrom.'</title></head></html>';

$filert='';

while (is_file($filert."gallery.php") == false) {

$filert=$filert.'../';

   } 

if (is_file($filert.'header.php')) { include $filert.'header.php'; }

// --------------------------------------

// ------------- Function to make thumb, requires G2.DLL Library -----------------

function makethumb($filename) {

// Set a maximum height, width, and quality of thumbnails
$width = 100;
$height = 80;
$quality = 80;


// Get new dimensions
list($width_orig, $height_orig) = getimagesize($filename);

if ($width && ($width_orig < $height_orig)) {
   $width = ($height / $height_orig) * $width_orig;
} else {
   $height = ($width / $width_orig) * $height_orig;
}

// Resample
$image_p = imagecreatetruecolor($width, $height);
$image = imagecreatefromjpeg($filename);
imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

// Output
   $newfile="thumb/".$filename;

   imagejpeg($image_p, $newfile, $quality);
}


// --------------------------------------

// ----------------  Legacy code for popup preloading ----------------------

// ---   What this does is read the GET input. If true, write fancy HTML ---

  if (isset($_GET["image"])) {
    $image=$_GET["image"];

    echo '<img src="'.$image.'">';

    exit();

   }

// --------------------------------------



// ---------- Main Module --------------------------


   if (is_file("timestamp.txt")==false) {

$fp = fopen("timestamp.txt", "w", 0); #open for writing
  fputs($fp, microtime()); #write all of $data to our opened file
  fclose($fp); #close the file
}




if ($handle = opendir('.')) {


   $thumbdir=0;
   if (is_file("thumb")) {unlink("thumb");}

   while (false !== ($file = readdir($handle))) {

   if ($file=="thumb" && is_dir($file)) { $thumbdir=1; }

   }

   closedir($handle);
  }

  if ($thumbdir==0) { mkdir("thumb"); }



// --------------------------------------

// ------- Checks to see if thumbs exist for JPGs ----------------------

//              --- If NoT, create "rendering" splashscreen ---


if ($handle = opendir('.')) {

  $basecount=0;
  $thumbcount=0;

   while (false !== ($file = readdir($handle))) {

       if (substr_count(strtolower($file), '.jpg') == 1) {

        $basecount++;

        if (file_exists("thumb/".$file) == false) {


         } else {
         $thumbcount++;
         }

        }

   }
   closedir($handle);
  }

  echo "<head>";

 if ($basecount!=$thumbcount) {

   echo '<meta http-equiv="refresh" content="1">';

 echo "</head><body><br><br>";
 echo "Thanks for choosing the drop and go PHP Gallery Generator.<br>
 The easiest way to create galleries.<br>
 Written in PHP by <b>Gordon Virasawmi</b><br><br>";
 echo "<b><u>Please wait</u>, we are preparing the gallery...</b><hr>";
 echo "This is a one time only process, after this the gallery will load quickly for all web users.<hr><br>";
 echo "<fieldset>Gallery Name: ".$wherefrom."<br>";
 echo "Total Images: ".$basecount."<br>";
 echo "Images Indexed: ".$thumbcount."<br><br>";
 echo "Status: <b>";
 echo sprintf("%01.2f", ($thumbcount/$basecount)*100);
 echo "%</b></fieldset>";

   } else { 

if ($cachetopublic) {

  echo '<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="PUBLIC">';
  }

echo "</head><body>";}

  $previewbase=$basecount;
  $previewthumb=$thumbcount;

// --------------------------------------

// ----- Javascript in Gallery to create popup ------

           echo '<script> var popfile; ';
           echo 'function popupwindow(popfile) { ';

           echo "Fig1Window = window.open(";

           echo "popfile, 'image','";

if ($showaddressbarinpopup==true) {
           echo "location=1,";
            } 

           echo "status=0,toolbar=0,directories=0,menubar=0,scrollbars=1,resizable=1,left=0,top=0');";


           echo "Fig1Window.opener = self; ";
           echo 'Fig1Window.document.write ("<html><head><title>'.$wherefrom.'</title></head><body bgcolor=#000000 topmargin=0 leftmargin=0 marginheight=0 marginwidth=0 onLoad=\'self.opener.windowopened=1; x=document.theimage.width; y=document.theimage.height; x=x+29; y=y+62; window.moveBy(0, 5); window.resizeTo(x,y);\' onUnload=\'self.opener.windowopened=0;\'><a href=\'javascript:close();\'><img src=\'"+popfile+"\' alt=\'Click to close\' border=0 name=\'theimage\'></a></body></html>"); ';

           echo "Fig1Window.document.close(); ";

           echo 'Fig1Window.focus() } </script>';

// --------------------------------------

// ----------- Checks if thumb exists, if not, makes them ----------------------

if ($handle = opendir('.')) {

  $basecount=0;
  $thumbcount=0;
  $makecount=0;

   while (false !== ($file = readdir($handle))) {

       if (substr_count(strtolower($file), '.jpg') == 1) {

        $basecount++;

        if (file_exists("thumb/".$file) == false && $makecount<$jumper) {

         makethumb($file);
         $makecount++;
         if ($thumbcount/2>1 && $thumbcount/2<$maxjumper) {$jumper=$thumbcount/2;} else {
           if ($thumbcount/2>$maxjumper) {$jumper=$maxjumper;} }

         } else {
         $thumbcount++;
         }

        }

   }
   closedir($handle);
  }

if ($previewbase==$previewthumb) {

// --------- End Make Thumb Routine ------------------------


// --------- Render Gallery    -----------------------------


if ($showgallerytitle) {
  echo $wherefrom."<br>";
  echo "<hr>";
  }

  // http://cre.ations.net/ Gallery Script Add On
  echo "<script src='http://cre.ations.net/imagezoom.js' type='text/javascript'></script><span id='insertimageshere' style='position: absolute; text-decoration: none'>&nbsp;</span><a id='lostfocusthingy' style='position: absolute; text-decoration: none' href='#'>&nbsp;</a>";
  
  echo '<table><tr><script language="JavaScript1.2">';
  echo 'var GalleryWidth = "'.$gallerywidth.'"; ';

  echo "var GalleryImages = new Array(";

$tablerow=0;


 if ($handle = opendir('.')) {
   while (false !== ($file = readdir($handle))) {


      if (isset($remotedir)) {$filed=$remotedir."/".$file;
                              $filet=$remotedir."/thumb/".$file;
                                } else {
                              $filed=$file;
                              $filet="thumb/".$file;
                             }


       if (substr_count(strtolower($file), '.jpg') == 1) {



           echo '"'.$filed.'"';


       $tablerow++;
       if ($tablerow<$thumbcount) {
          echo ",";
          }

       }

   }

   closedir($handle);

 }

echo "); ";

//echo "var tablerow = 0, templength = GalleryImages.length; for (drawgallery = 0; drawgallery < templength; drawgallery++) { document.write('<td align=\"center\"><a href=\"'); document.write(\"javascript: popupwindow('\"+GalleryImages[drawgallery]+\"')\"); document.write('\">'); document.write(\"<img border=0 src=\"); document.write('\"'); document.write(\"thumb/\"+GalleryImages[drawgallery]); document.write('\"'); document.write(\"></a></td>\"); tablerow++; if (tablerow > GalleryWidth-1) { tablerow=0; document.write(\"</tr><tr>\"); } }";

echo "</script></tr></table>";

}

  $searchbots=1;
  $searchbots=$searchbots+substr_count(strtolower($_SERVER['HTTP_USER_AGENT']), 'googlebot');
  $searchbots=$searchbots+substr_count(strtolower($_SERVER['HTTP_USER_AGENT']), 'yahoo');
  $searchbots=$searchbots+substr_count(strtolower($_SERVER['HTTP_USER_AGENT']), 'msn');

  if ($searchbots > 0) {

    if ($handle = opendir('.')) {


       while (false !== ($file = readdir($handle))) {

           if (substr_count(strtolower($file), '.jpg') == 1) {


            if (file_exists("thumb/".$file) == false) {


             } else {

	               // http://cre.ations.net/ Gallery Script Add On

             // echo '<a href="'.$file.'" alt="'.$wherefrom.'" title="'.$wherefrom.'"><img src="thumb/'.$file.'" alt="'.$wherefrom.'"></a>';
             
             echo '<a class=\'expand_a\' onclick=\'imagezoom(this, "'.$file.'")\'>';
             echo '<img src="thumb/'.$file.'" class="expand_img" /></a>';
             

             }

            }

       }
       closedir($handle);
      }


   }

// -------------- End Render Gallery ------------------

// ---------------- Include Footer  -------------------

if (is_file($filert.'footer.php')) { include $filert.'footer.php'; }

?>
