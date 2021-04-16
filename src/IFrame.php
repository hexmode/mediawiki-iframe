<?php
/**
 * Copyright (C) 2017  NicheWork, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Mark A. Hershberger <mah@nichework.com>
 */
namespace MediaWiki\Extension;

use Html;
use MWException;
use Parser;

class IFrame {
	/**
	 * Initialize the hook
	 *
	 * @param Parser $parser from mw
	 */
	public static function init( Parser $parser ): void {
		$parser->setHook( "iframe", "MediaWiki\\Extension\\IFrame::renderIFRAME" );
	}

	/**
	 * Convenience function for throwing errors when we need to
	 *
	 * @param array<string,string|int> $parsed the result of parse_url()
	 * @param string $part a key into $parsed
	 * @param string $url the whole url, used for error messages
	 * @return int|string the part requested
	 */
	protected static function getPartOrError( array $parsed, string $part, string $url ) {
		if ( !isset( $parsed[$part] ) ) {
			throw new MWException( "$part not found in $url" );
		}
		return $parsed[$part];
	}

	/**
	 * Return this scheme if it is safe.  Otherwise, throw an error.
	 *
	 * @param string $scheme to check
	 * @return string
	 * @todo Make schemes config var
	 */
	protected static function isSafeScheme( string $scheme ): string {
		$validSchemes = [ "http", "https", "ftp" ];
		$inv = array_flip( $validSchemes );
		if ( !isset( $inv[$scheme] ) ) {
			throw new MWException(
				"Invalid scheme. '$scheme' is not one of " . implode( ", ", $validSchemes )
			);
		}
		return $scheme;
	}

	/**
	 * Return this host if it is safe.  Otherwise, throw an error.
	 *
	 * @param string $host to check
	 * @return string
	 * @todo Make hosts config var
	 */
	protected static function isSafeHost( string $host ): string {
		$validHosts = [ 'www.wikipathways.org' ];
		$inv = array_flip( $validHosts );
		if ( !isset( $inv[$host] ) ) {
			throw new MWException(
				"Invalid host. '$host' is not one of " . implode( ", ", $validHosts )
			);
		}
		return $host;
	}

	/**
	 * Clean up the URL.  Could whitelist hosts, types, and such here.
	 *
	 * @param string $url to clean
	 * @return ?string
	 */
	protected static function cleanURL( string $url ): ?string {
		$ret = null;
		$parsed = parse_url( $url );
		if ( $parsed ) {
			try {
				$ret = self::isSafeScheme( strval( self::getPartOrError( $parsed, 'scheme', $url ) ) )
					 . '://';
				// Whitelist hosts here
				$ret .= self::isSafeHost( strval( self::getPartOrError( $parsed, 'host', $url ) ) );
				if ( isset( $parsed['port'] ) ) {
					$ret .= ':' . $parsed['port'];
				}
				$ret .= self::getPartOrError( $parsed, 'path', $url );
				if ( isset( $parsed['query'] ) ) {
					$ret .= '?' . $parsed['query'];
				}
				if ( isset( $parsed['fragment'] ) ) {
					$ret .= '#' . $parsed['fragment'];
				}
			} catch ( MWException $e ) {
				$ret = null;
			}
		}
		return $ret;
	}

	/**
	 * Parser function to handle <iframe> elements
	 *
	 * @param string $input What is inside the <iframe> element
	 * @param array<string, string> $argv attributes on <iframe> element
	 * @return string that parser will inject
	 */
	public static function renderIFRAME( string $input, array $argv ): string {
		// no safety check: only .pdf files
		$url = null;
		$attr = [];
		if ( stripos( $input, "http://" ) === 0 ||
		stripos( $input, "ftp" ) === 0 ) {
			$url = self::cleanURL( $input );
		} elseif ( isset( $argv['src'] ) ) {
			$url = self::cleanURL( $argv['src'] );
		} elseif ( isset( $argv['url'] ) ) {
			$url = self::cleanURL( $argv['url'] );
		}

		if ( $url === null ) {
			return '';
		}

		// no error occurred
		$attr['src'] = $url;
		if ( !isset( $argv['width'] ) && !isset( $argv['height'] ) ) {
			$attr['width']	= 800;
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
		if ( isset( $argv['style'] ) && stristr( $argv['style'], "overflow:hidden" ) !== false ) {
			$argv['style'] = "overflow:hidden";
		}

		return Html::rawElement( 'iframe', $attr );
	}
}
