<?php

namespace MediaWiki\Extension\IFrame;

use GlobalVarConfig;
use MWException;
use Title;
use WikiPage;

class Config extends GlobalVarConfig {

	const CFG_PAGE = "MediaWiki:IFrame-cfg.json";

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

	/**
	 * Get domains
	 *
	 * @return array<int, string>
	 */
	public function getDomains(): array {
		$ret = $this->get( "Domains" );
		$cfg = Title::newFromText( self::CFG_PAGE );
		$content = WikiPage::factory( $cfg )->getContent();
		if ( $content && method_exists( $content, "getData" ) ) {
			$value = $content->getData()->value;
			if ( !property_exists( $value, 'domains' ) || !is_array( $value->domains ) ) {
				throw new MWException( sprintf(
					"The contents of 'domains' on %s should be an array of domains.", self::CFG_PAGE
				) );
			}
			$ret = array_merge( $ret, $value->domains );
		}
		return $ret;
	}
}
