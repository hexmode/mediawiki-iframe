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
	/** @var ?string */
	protected static $name = "iframe";

	/** @var array<string, string> */
	protected $attrMap = [
		'allowfullscreen' => 'handleBool',
		'height' => 'handleInt',
		'src' => 'setSource',
		'width' => 'handleInt',
	];

	/** @var array<int, string> */
	protected $mandatoryAttributes = [ "src" ];
	protected $config;

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
	 * Convenience function for throwing errors when we need to
	 *
	 * @param array<string,string|int> $parsed the result of parse_url()
	 * @param string $part a key into $parsed
	 * @return string the part requested
	 */
	private function getPart( array $parsed, $part ): string {
		return strval( $parsed[$part] ?? "" );
	}

	/**
	 * Convenience function for throwing errors when we need to
	 *
	 * @param array<string,string|int> $parsed the result of parse_url()
	 * @param string $part a key into $parsed
	 * @param string $url the whole url, used for error messages
	 * @return string the part requested
	 */
	private function getPartOrError(
		array $parsed,
		$part,
		$url
	): string {
		if ( !isset( $parsed[$part] ) ) {
			throw new AttrException( "Part missing: $part not found in $url" );
		}
		return $this->getPart( $parsed, $part );
	}

	/**
	 * Return this scheme if it is safe.  Otherwise, throw an error.
	 *
	 * @param string $scheme to check
	 * @return string
	 * @todo Make schemes config var
	 */
	private function isSafeScheme( $scheme ): string {
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
	private function isSafeHost( $host ): string {
		$host = strtolower( $host );
		$validHosts = (array)$this->config->getDomains();
		$inv = array_flip( $validHosts );
		if ( count( $inv ) > 0 && !isset( $inv[$host] ) ) {
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
	protected function setSource( $name, $url ): string {
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
			$ret .= self::getPart( $parsed, 'path', $url );
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
