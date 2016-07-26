<?php
$wgExtensionFunctions[] = "wfIFRAME";
function wfIFRAME() {
    global $wgParser;
    $wgParser->setHook( "iframe", "RenderIFRAME" );
}

function getPartOrError( $parsed, $part, $url ) {
    if ( !isset( $parsed[$part] ) ) {
        throw new Exception( "$part not found in $url" );
    }
    return $parsed[$part];
}

function cleanURL( $url ) {
    $ret = false;
    $parsed = parse_url( $url );
    if ( $parsed ) {
        try {
            $ret = getPartOrError( $parsed, 'scheme', $url ) . '://';
            $ret .= getPartOrError( $parsed, 'host', $url );
            if ( isset( $parsed['port'] ) ) {
                $ret .= ':' . $parsed['port'];
            }
            $ret .= getPartOrError( $parsed, 'path', $url );
            if ( isset( $parsed['query'] ) ) {
                $ret .= '?' . $parsed['query'];
            }
            if ( isset( $parsed['fragment'] ) ) {
                $ret .= '#' . $parsed['fragment'];
            }
        } catch ( Exception $e ) {
            $ret = false;
        }
    }
    return $ret;
}

function RenderIFRAME( $input, $argv ) {
    global $wgScriptPath;

    // no safety check: only .pdf files
    clearstatcache();
    $url = null;
    if ( stripos($input , "http") === 0 || stripos($input , "ftp") === 0 ) {
        $url = cleanURL( $input );
    } else if ( isset( $argv['src'] ) ) {
        $url = cleanURL( $argv['src'] );
    } else if ( isset( $argv['url'] ) ) {
        $url = cleanURL( $argv['url'] );
    }

    if ( $url ) { //no error occured
        $attr['src'] = $url;
        if ( !isset( $argv['width'] ) && !isset( $argv['height'] ) ) {
            $attr['width']  = 800;
            $attr['height'] = 600;
        } else {
            if ( isset( $argv['width'] ) ) {
                $attr['width'] = $argv['width'];
            } else {
                $attr['width'] = $argv['height'];
            }
            if ( isset( $argv['height'] ) ) {
                $attr['height'] = $argv['height'];
            } else {
                $attr['height'] = $argv['width'];
            }
        }
        if ( isset( $argv['seamless'] ) ) {
            $attr['seamless'] = true;
        }
        if ( isset( $argv['allowfullscreen'] ) ) {
            $attr['allowfullscreen'] = true;
        }

        return Html::rawElement( 'iframe', $attr );
    }
    return '';
}
