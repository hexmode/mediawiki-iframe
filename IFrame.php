<?php
  // MediaWiki PDF Extension Ver 0.2
  // Set up MediaWiki to react to the "<PDF>" tag
  // Original file by Nilam Doctor
  // <ceo@qualifiedtutorsinc.com> to allow local PDF files

  $wgExtensionFunctions[] = "wfIFRAME";
  function wfIFRAME()
  {
    global $wgParser;
    $wgParser->setHook( "iframe", "RenderIFRAME" );
  }

  function RenderIFRAME( $input, $argv )
  {
    global $wgScriptPath;
    $output = "";
    // no safety check: only .pdf files
    clearstatcache(); 

    if ( stripos($input , "http") === 0 || stripos($input , "ftp") === 0 )
    {
      // external URL
      if (!preg_match("/;/", $input ))
//      { 
//        if (preg_match('#^(http://|ftp://)[a-zA-Z][a-zA-Z0-9\-\.]*\.(conocophillips.net|conoco.net|ppco.com)#', $input))
//        {
          $url = $input;
//        }
//        else
//        {
//          $output = "Error: the link \"$input\" is not recognized as an allowed internal/external site.";
//        }
//        $url = $input;
//      }
      else
      {
        $output = "Error: The link \"$input\" contains a \";\", which is not allowed.";
      }
    }
    else { 
      // internal Media:
      if (file_exists ( $input )) //safety check: if file exists, we assume it is safe.
        $url = $input;
      else 
        $output = "Error: The file \"$input\" was not found.";
      }
    if (empty($output)) { //no error occured
      $width  = isset($argv['width']) ? $argv['width']  : 800;
      $height = isset($argv['height'])? $argv['height'] : 600;
      $id = basename($input, ".pdf");
      $output = '<iframe width="' . $width . '" height="' . $height . '" src="' . $url . '" frameborder="0" framebordercolor="#000000"></iframe>';
    }
    return $output;
  }
?>
