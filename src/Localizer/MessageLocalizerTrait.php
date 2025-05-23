<?php

namespace SMW\Localizer;

/**
 * @private
 *
 * @license GPL-2.0-or-later
 * @since 3.2
 *
 * @author mwjames
 */
trait MessageLocalizerTrait {

	/**
	 * @var MessageLocalizer
	 */
	private $messageLocalizer;

	/**
	 * @since 3.2
	 *
	 * @param MessageLocalizer $messageLocalizer
	 */
	public function setMessageLocalizer( MessageLocalizer $messageLocalizer ) {
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @since 3.2
	 *
	 * @param string|array ...$args
	 *
	 * @return string
	 */
	public function msg( ...$args ): string {
		if ( $this->messageLocalizer !== null ) {
			return $this->messageLocalizer->msg( ...$args );
		}

		return Message::get( $args[0] ?? '⧼n/a⧽', $args[1] ?? Message::TEXT, $args[2] ?? Message::USER_LANGUAGE );
	}

}
