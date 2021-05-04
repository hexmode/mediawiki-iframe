<?php

namespace MediaWiki\Extension\IFrameTag;

use GlobalVarConfig;
use MWException;
use Title;
use WikiPage;

class Config extends GlobalVarConfig {

	/**
	 * The page that contains our user-editable configuration.
	 */
	const CFG_PAGE = "MediaWiki:IFrame-cfg.json";

	const KEY_DOMAINS = 'Domains';
	const KEY_ON_WIKI_CFG = 'OnWikiConfig';

	/**
	 * The list of configured domains.
	 * @var string[]
	 */
	private $domains = null;

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
		if ( $this->domains === null ) {
			$ret = $this->get( self::KEY_DOMAINS );
			if ( $this->get( self::KEY_ON_WIKI_CFG ) === true ) {
				$ret = array_merge( $ret, $this->getOnWikiDomains() );
			}
			$this->domains = array_map( function ( $name ) { return strtolower( $name ); }, $ret );
		}
		return $this->domains;
	}

	/**
	 * Get domains from on-wiki config
	 *
	 * @return array<int, string>
	 */
	protected function getOnWikiDomains(): array {
		$ret = [];
		$cfg = Title::newFromText( self::CFG_PAGE );
		$content = WikiPage::factory( $cfg )->getContent();
		if ( $content && method_exists( $content, "getData" ) ) {
			$value = $content->getData()->value;
			if ( !property_exists( $value, 'domains' ) || !is_array( $value->domains ) ) {
				# Throwing MWException here since if we run into this someone has just modified
				# the CFG_PAGE (probably?)
				throw new MWException( sprintf(
					"The contents of 'domains' on %s should be an array of domains.", self::CFG_PAGE
				) );
			}
			$ret = $value->domains;
		}
		return $ret;
	}
}
