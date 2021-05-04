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
namespace MediaWiki\Extension\IFrameTag;

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
		'src' => 'handleUrl',
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
	 * Return this host if it is safe.  Otherwise, throw an error.
	 *
	 * @param string $host to check
	 * @return string
	 * @todo Make hosts config var
	 */
	protected function isSafeHost( $host ): string {
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
}
