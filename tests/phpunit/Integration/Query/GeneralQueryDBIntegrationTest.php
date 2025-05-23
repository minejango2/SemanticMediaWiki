<?php

namespace SMW\Tests\Integration\Query;

use SMW\DataValueFactory;
use SMW\DataValues\PropertyValue;
use SMW\DIProperty;
use SMW\Query\Language\SomeProperty;
use SMW\Query\Language\ThingDescription;
use SMW\Query\PrintRequest;
use SMW\Tests\SMWIntegrationTestCase;
use SMW\Tests\Utils\UtilityFactory;
use SMWQuery as Query;

/**
 * @group SMW
 * @group SMWExtension
 *
 * @group semantic-mediawiki-integration
 * @group semantic-mediawiki-query
 *
 * @group mediawiki-database
 * @group Database
 * @group medium
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class GeneralQueryDBIntegrationTest extends SMWIntegrationTestCase {

	private $subjectsToBeCleared = [];

	private $dataValueFactory;
	private $queryResultValidator;
	private $semanticDataFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->dataValueFactory = DataValueFactory::getInstance();
		$this->queryResultValidator = UtilityFactory::getInstance()->newValidatorFactory()->newQueryResultValidator();
		$this->semanticDataFactory = UtilityFactory::getInstance()->newSemanticDataFactory();

		$this->testEnvironment->addConfiguration( 'smwgQueryResultCacheType', false );
	}

	protected function tearDown(): void {
		foreach ( $this->subjectsToBeCleared as $subject ) {
			$this->getStore()->deleteSubject( $subject->getTitle() );
		}

		parent::tearDown();
	}

	public function testPropertyBeforeAfterDataRemoval() {
		$property = new DIProperty( 'SomePagePropertyBeforeAfter' );
		$property->setPropertyTypeId( '_wpg' );

		$this->assertEmpty(
			$this->searchForResultsThatCompareEqualToOnlySingularPropertyOf( $property )->getResults()
		);

		$semanticData = $this->semanticDataFactory->newEmptySemanticData( __METHOD__ );

		$semanticData->addDataValue(
			$this->dataValueFactory->newDataValueByItem( $semanticData->getSubject(), $property )
		);

		$this->getStore()->updateData( $semanticData );

		$this->queryResultValidator->assertThatQueryResultContains(
			$semanticData->getSubject(),
			$this->searchForResultsThatCompareEqualToOnlySingularPropertyOf( $property )
		);

		$this->assertNotEmpty(
			$this->searchForResultsThatCompareEqualToOnlySingularPropertyOf( $property )->getResults()
		);

		$this->getStore()->clearData( $semanticData->getSubject() );

		$this->assertEmpty(
			$this->searchForResultsThatCompareEqualToOnlySingularPropertyOf( $property )->getResults()
		);

		$this->subjectsToBeCleared = [
			$semanticData->getSubject()
		];
	}

	public function testUserDefinedPropertyUsedForInvalidValueAssignment() {
		$property = new DIProperty( 'SomePropertyWithInvalidValueAssignment' );
		$property->setPropertyTypeId( '_tem' );

		$dataValue = $this->dataValueFactory->newDataValueByProperty( $property, '1 Jan 1970' );

		$semanticData = $this->semanticDataFactory->newEmptySemanticData( __METHOD__ );
		$semanticData->addDataValue( $dataValue );

		$this->getStore()->updateData( $semanticData );

		$this->assertSame(
			0,
			$this->searchForResultsThatCompareEqualToOnlySingularPropertyOf( $property )->getCount()
		);

		$this->subjectsToBeCleared = [
			$semanticData->getSubject()
		];
	}

	private function searchForResultsThatCompareEqualToOnlySingularPropertyOf( DIProperty $property ) {
		$propertyValue = new PropertyValue( '__pro' );
		$propertyValue->setDataItem( $property );

		$description = new SomeProperty(
			$property,
			new ThingDescription()
		);

		$description->addPrintRequest(
			new PrintRequest( PrintRequest::PRINT_PROP, null, $propertyValue )
		);

		$query = new Query(
			$description
		);

		$query->querymode = Query::MODE_INSTANCES;

		return $this->getStore()->getQueryResult( $query );
	}

}
