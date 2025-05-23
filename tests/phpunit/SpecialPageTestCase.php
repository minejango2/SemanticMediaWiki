<?php

namespace SMW\Tests;

use FauxRequest;
use MediaWiki\MediaWikiServices;
use OutputPage;
use RequestContext;
use SMW\Tests\Utils\Mock\MockSuperUser;
use SpecialPage;
use WebRequest;
use WebResponse;

/**
 *
 * @group SMW
 * @group SMWExtension
 * @group medium
 *
 * @license GPL-2.0-or-later
 * @since 1.9.0.2
 *
 * @author mwjames
 */
abstract class SpecialPageTestCase extends \PHPUnit\Framework\TestCase {

	protected $obLevel;
	protected $store = null;
	protected string $text;
	protected WebResponse $response;

	protected function setUp(): void {
		parent::setUp();
		$this->obLevel = ob_get_level();
	}

	protected function tearDown(): void {
		$obLevel = ob_get_level();

		while ( ob_get_level() > $this->obLevel ) {
			ob_end_clean();
		}

		parent::tearDown();
	}

	/**
	 * @return SpecialPage
	 */
	abstract protected function getInstance();

	protected function setStore( $store ) {
		$this->store = $store;
	}

	/**
	 * Borrowed from \Wikibase\Test\SpecialPageTestBase
	 *
	 * @param string $sub The subpage parameter to call the page with
	 * @param WebRequest|null $request Web request that may contain URL parameters, etc
	 */
	protected function execute( $sub = '', ?WebRequest $request = null, $user = null ) {
		$request  = $request === null ? new FauxRequest() : $request;
		$response = $request->response();

		$page = $this->getInstance();

		if ( $this->store !== null ) {
			$page->setStore( $this->store );
		}

		$page->setContext( $this->makeRequestContext(
			$request,
			$user,
			$page->getPageTitle()
		) );

		$out = $page->getOutput();

		ob_start();
		$page->execute( $sub );

		if ( $out->getRedirect() !== '' ) {
			$out->output();
			$text = ob_get_contents();
		} elseif ( $out->isDisabled() ) {
			$text = ob_get_contents();
		} else {
			$text = $out->getHTML();
		}

		ob_end_clean();

		$code = $response->getStatusCode();

		if ( $code > 0 ) {
			$response->header( "Status: " . $code . ' ' . \HttpStatus::getMessage( $code ) );
		}

		$this->text = $text;
		$this->response = $response;
	}

	/**
	 * @return string
	 */
	protected function getText() {
		return $this->text;
	}

	/**
	 * @return FauxResponse
	 */
	protected function getResponse() {
		return $this->response;
	}

	/**
	 * @return RequestContext
	 */
	private function makeRequestContext( WebRequest $request, $user, $title ) {
		$languageFactory = MediaWikiServices::getInstance()->getLanguageFactory();

		$context = new RequestContext();
		$context->setRequest( $request );

		$out = new OutputPage( $context );
		$out->setTitle( $title );

		$context->setOutput( $out );
		$context->setLanguage( $languageFactory->getLanguage( 'en' ) );

		$user = $user === null ? new MockSuperUser() : $user;
		$context->setUser( $user );

		return $context;
	}
}
