<?php

namespace SMW\Listener\EventListener\EventListeners;

use Onoi\EventDispatcher\DispatchContext;
use Onoi\EventDispatcher\EventListener;
use Psr\Log\LoggerAwareTrait;
use SMW\Property\SpecificationLookup;

/**
 * @license GPL-2.0-or-later
 * @since 3.2
 *
 * @author mwjames
 */
class InvalidatePropertySpecificationLookupCacheEventListener implements EventListener {

	use LoggerAwareTrait;

	const EVENT_ID = 'InvalidatePropertySpecificationLookupCache';

	/**
	 * @var SpecificationLookup
	 */
	private $propertySpecificationLookup;

	/**
	 * @since 3.2
	 *
	 * @param SpecificationLookup $propertySpecificationLookup
	 */
	public function __construct( SpecificationLookup $propertySpecificationLookup ) {
		$this->propertySpecificationLookup = $propertySpecificationLookup;
	}

	/**
	 * @since 3.2
	 *
	 * {@inheritDoc}
	 */
	public function execute( ?DispatchContext $dispatchContext = null ) {
		$subject = $dispatchContext->get( 'subject' );
		$context = $dispatchContext->get( 'context' );

		$this->propertySpecificationLookup->invalidateCache(
			$subject
		);

		$this->logger->info(
			[ 'Event', 'InvalidatePropertySpecificationLookupCache', "{triggered_by}", "{id}" ],
			[ 'role' => 'user', 'triggered_by' => $context, 'id' => $subject->getHash() ]
		);
	}

	/**
	 * @since 3.2
	 *
	 * {@inheritDoc}
	 */
	public function isPropagationStopped() {
		return false;
	}

}
