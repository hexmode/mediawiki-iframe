<?php
/**
 * Copyright (C) 2017, 2021  NicheWork, LLC
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
namespace MediaWiki\Extension\IFrame;

use NicheWork\MW\AttrException;
use NicheWork\MW\Tag;
use Parser;
use PPFrame;

class Handler extends Tag {
	/** @var array<string, string> */
	protected array $attrMap = [
		'allowfullscreen' => 'handleBoolValue',
		'height' => 'setHeight',
		'src' => 'setSource',
		'width' => 'setWidth',
	];
	protected Config $config;

	/**
	 * Constructor for the iframe tag handler
	 *
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	protected function __construct( Parser $parser, PPFrame $frame ) {
		parent::__construct( $parser, $frame );
		$this->config = new Config();
	}

	/**
	 * Get the height
	 */
	protected function setHeight( string $value ): string {
		return $this->handleInt( $value, "height" );
	}

	/**
	 * Get the width
	 */
	protected function setWidth( string $value ): string {
		return $this->handleInt( $value, "width" );
	}

	/**
	 * Convenience function for throwing errors when we need to
	 *
	 * @param array<string,string|int> $parsed the result of parse_url()
	 * @param string $part a key into $parsed
	 * @param string $url the whole url, used for error messages
	 * @return int|string the part requested
	 */
	private function getPartOrError(
		array $parsed,
		string $part,
		string $url
	) {
		if ( !isset( $parsed[$part] ) ) {
			throw new AttrException( "$part not found in $url" );
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
	private function isSafeScheme( string $scheme ): string {
		$validSchemes = [ "http", "https", "ftp" ];
		$inv = array_flip( $validSchemes );
		if ( !isset( $inv[$scheme] ) ) {
			throw new AttrException(
				"Invalid scheme. '$scheme' is not one of "
				. implode( ", ", $validSchemes )
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
	private function isSafeHost( string $host ): string {
		$validHosts = (array)$this->config->get( "Domains" );
		$inv = array_flip( $validHosts );
		if ( !isset( $inv[$host] ) ) {
			throw new AttrException(
				"Invalid host. '$host' is not one of "
				. implode( ", ", $validHosts )
			);
		}
		return $host;
	}

	/**
	 * Clean up the URL.  Could whitelist hosts, types, and such here.
	 *
	 * @param ?string $url to clean
	 * @return ?string
	 */
	protected function setSource( ?string $url ): ?string {
		$ret = null;
		$parsed = null;
		if ( $url ) {
			$url = trim( $url, '"\'' );
			$parsed = parse_url( $url );
		}
		if ( $url && $parsed ) {
			$ret = self::isSafeScheme(
				strval( self::getPartOrError( $parsed, 'scheme', $url ) )
			) . '://';
			// Whitelist hosts here
			$ret .= self::isSafeHost(
				strval( self::getPartOrError( $parsed, 'host', $url ) )
			);
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
		}
		return $ret;
	}
}