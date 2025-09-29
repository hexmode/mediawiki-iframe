<?php

namespace MediaWiki\Extension\IFrameTag;

use Exception;
use GlobalVarConfig;
use JsonContent;
use MediaWiki\MediaWikiServices;
use stdClass;
use MediaWiki\Title\Title;

class Config extends GlobalVarConfig {

	/**
	 * The page that contains our user-editable configuration.
	 */
	private const CFG_PAGE = "iframe-cfg.json";
	private const KEY_DOMAINS = 'Domains';
	private const KEY_ON_WIKI_CFG = 'OnWikiConfig';

	/**
	 * The list of configured domains.
	 * @psalm-var array<string, array-key>
	 */
	private array $domains;

	public function __construct() {
		parent::__construct( 'iFrame' );
		$this->domains = $this->initDomains();
	}

	/**
	 * Factory method for MediaWikiServices
	 *
	 * @return Config
	 */
	public static function newInstance() {
		return new self();
	}

	/**
	 * Get the initial list of domains.
	 *
	 * @psalm-return array<string, array-key>
	 */
	private function initDomains(): array {
		$ret = $this->get( self::KEY_DOMAINS );
		if ( !is_array( $ret ) ) {
			throw new Exception( wfMessage( 'iframe-config-error-array-expected', gettype( $ret ) )->plain() );
		}
		$ret = array_filter( $ret, 'is_string' );
		if ( $this->get( self::KEY_ON_WIKI_CFG ) === true ) {
			$ret = array_merge( $ret, $this->getOnWikiDomains() );
		}
		return array_flip( array_map( 'strtolower', $ret ) );
	}

	/**
	 * Get array of domains where the domains are they keys, not the values, of the array and, as a result,
	 * isset($domains["example.com"]) can be used to see if the domain is in the list returned.
	 *
	 * @psalm-return array<string, array-key>
	 */
	public function getDomains(): array {
		return $this->domains;
	}

	/**
	 * Get domains from on-wiki config
	 *
	 * @psalm-return array<array-key, string>
	 */
	protected function getOnWikiDomains(): array {
		$cfg = Title::makeTitleSafe( NS_MEDIAWIKI, self::CFG_PAGE );
		$content = null;
		$value = null;

		if ( $cfg !== null ) {
			$wpf = MediaWikiServices::getInstance()->getWikiPageFactory();
			$content = $wpf->newFromTitle( $cfg )->getContent();
		}

		if ( $content instanceof JsonContent ) {
			$status = $content->getData();
			/** @var stdClass|string */
			$value = $status->isGood() ? $status->getValue() : "";
		}

		if ( !is_object( $value ) || !property_exists( $value, 'domains' ) ) {
			return [];
		}

		if ( !is_array( $value->domains ) ) {
			# Throwing MWException here since if we run into this someone has just modified
			# the CFG_PAGE (probably?)
			throw new Exception(
				wfMessage( 'iframe-cfg-json-error-array-expected', gettype( $value->domains ) )->plain()
			);
		}

		return array_filter( $value->domains, "is_string" );
	}
}
