<?php

namespace MediaWiki\Extension\IFrame;

use GlobalVarConfig;

class Config extends GlobalVarConfig {

	public function __construct() {
		parent::__construct( 'iFrame' );
	}

	/**
	 * Factory method for MediaWikiServices
	 * @return Config
	 */
	public static function newInstance() {
		return new self();
	}
}
