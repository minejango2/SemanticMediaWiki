<?php

namespace SMW\Tests\Query\Processor;

use SMW\Query\Processor\ParamListProcessor;
use SMW\Tests\PHPUnitCompat;

/**
 * @covers \SMW\Query\Processor\ParamListProcessor
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 3.0
 *
 * @author mwjames
 */
class ParamListProcessorTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	public function testCanConstruct() {
		$printRequestFactory = $this->getMockBuilder( '\SMW\Query\PrintRequestFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			ParamListProcessor::class,
			new ParamListProcessor( $printRequestFactory )
		);
	}

	/**
	 * @dataProvider parametersProvider
	 */
	public function testPreprocess( $parameters, $showMode, $expected ) {
		$printRequestFactory = $this->getMockBuilder( '\SMW\Query\PrintRequestFactory' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new ParamListProcessor(
			$printRequestFactory
		);

		$this->assertEquals(
			$expected,
			$instance->preprocess( $parameters, $showMode )
		);
	}

	/**
	 * @dataProvider legacyParametersProvider
	 */
	public function testLegacyArray( $parameters ) {
		$printRequestFactory = $this->getMockBuilder( '\SMW\Query\PrintRequestFactory' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new ParamListProcessor(
			$printRequestFactory
		);

		$a = $instance->format(
			$parameters,
			ParamListProcessor::FORMAT_LEGACY
		);

		$this->assertIsString(

			$a[0]
		);

		$this->assertIsArray(

			$a[1]
		);

		$this->assertIsArray(

			$a[2]
		);
	}

	public function parametersProvider() {
		yield [
			[ '[[Foo::Bar]]' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [],
				'parameters' => [],
				'this'       => []
			]
		];

		// #640
		yield [
			[ '[[Foo::Bar=Foobar]]' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar=Foobar]]',
				'printouts'  => [],
				'parameters' => [],
				'this'       => []
			]
		];

		yield [
			[ '[[Foo::&lt;Bar=Foobar&gt;]]' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::<Bar=Foobar>]]',
				'printouts'  => [],
				'parameters' => [],
				'this'       => []
			]
		];

		// #3560
		yield [
			[ '[[Foo::Bar=-3DFoo]]' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar=-3DFoo]]',
				'printouts'  => [],
				'parameters' => [],
				'this'       => []
			]
		];

		yield [
			[ '[[Foo::Bar=-3DFoox003D]]' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar=-3DFoox003D]]',
				'printouts'  => [],
				'parameters' => [],
				'this'       => []
			]
		];

		// A user shouldn't use `0x003D` as representation for `=`
		yield [
			[ '[[Foo::Bar=-3DFoo0x003D]]' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar=-3DFoo=]]',
				'printouts'  => [],
				'parameters' => [],
				'this'       => []
			]
		];

		yield [
			[ '[[Foo::Bar]]', 'mainlabel=-' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [],
				'parameters' => [
					'mainlabel' => '-'
				],
				'this'       => []
			]
		];

		yield [
			[ '[[Foo::Bar]]', '?Foobar' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [
					'0bfab051cd82c364058617af13e9874a' => [
						'label'   => 'Foobar',
						'params'  => []
					]
				],
				'parameters' => [],
				'this'       => []
			]
		];

		// 4348
		yield [
			[ '[[Foo::Bar]]',
				'?Foobar',
				'+link='
			],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [
					'0bfab051cd82c364058617af13e9874a' => [
						'label'   => 'Foobar #link',
						'params'  => [ 'link' => '' ]
					]
				],
				'parameters' => [],
				'this'       => []
			]
		];

		yield [
			[ '[[Foo::Bar]]',
				'?Foobar',
				'+link=',
				'+thclass=unsortable'
			],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [
					'0bfab051cd82c364058617af13e9874a' => [
						'label'   => 'Foobar #link;thclass',
						'params'  => [ 'link' => '',
									  'thclass' => 'unsortable' ]
					]
				],
				'parameters' => [],
				'this'       => []
			]
		];

		yield [
			[ '[[Foo::Bar]]',
				'?Foobar',
				'+width=30px',
				'+link=',
				'+thclass=unsortable'
			],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [
					'0bfab051cd82c364058617af13e9874a' => [
						'label'   => "Foobar #30px;link;thclass",
						'params'  => [ 'width' => '30px', 'link' => '', 'thclass' => 'unsortable' ]
					]
				],
				'parameters' => [],
				'this'       => []
			]
		];

		yield [
			[ '[[Foo::Bar]]',
				'?Foobar',
				'+width=30px',
				'+link=',
				'+height=50px',
				'+thclass=unsortable'
			],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [
					'0bfab051cd82c364058617af13e9874a' => [
						'label'   => "Foobar #30x50px;link;thclass",
						'params'  => [ 'width' => '30px', 'height' => '50px', 'link' => '', 'thclass' => 'unsortable' ]
					]
				],
				'parameters' => [],
				'this'       => []
			]
		];

		yield [
			[ '[[Foo::Bar]]',
				'?Foobar',
				'+link=',
				'+height=50px',
				'+thclass=unsortable' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [
					'0bfab051cd82c364058617af13e9874a' => [
						'label'   => "Foobar #x50px;link;thclass",
						'params'  => [ 'height' => '50px', 'link' => '', 'thclass' => 'unsortable' ]
					]
				],
				'parameters' => [],
				'this'       => []
			]
		];

		yield [
			[ '[[Foo::Bar]]',
				'?Foobar',
				'+link=',
				'+height=100px',
				'+thclass=unsortable',
				'+width=90px' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [
					'0bfab051cd82c364058617af13e9874a' => [
						'label'   => "Foobar #90x100px;link;thclass",
						'params'  => [ 'width' => '90px', 'height' => '100px', 'link' => '', 'thclass' => 'unsortable' ]
					]
				],
				'parameters' => [],
				'this'       => []
			]
		];

		yield [
			[ '[[Foo::Bar]]',
				'?Foobar',
				'+height=100px',
				'+thclass=unsortable',
				'+width=90px',
				'+link=' ],
			false,
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [
					'0bfab051cd82c364058617af13e9874a' => [
						'label'   => "Foobar #90x100px;thclass;link",
						'params'  => [ 'width' => '90px', 'height' => '100px', 'link' => '', 'thclass' => 'unsortable' ]
					]
				],
				'parameters' => [],
				'this'       => []
			]
		];

		// #1645
		yield [
			[ 'Foo=Bar', 'link=none' ],
			true,
			[
				'showMode'   => true,
				'templateArgs' => false,
				'query'      => '[[:Foo=Bar]]',
				'printouts'  => [],
				'parameters' => [
					'link' => 'none'
				],
				'this'       => []
			]
		];

		// #3196
		yield [
			[ 'Foo=Bar', 'link=none', 'intro=[[File:Foo.png|link=Bar]]' ],
			true,
			[
				'showMode'   => true,
				'templateArgs' => false,
				'query'      => '[[:Foo=Bar]]',
				'printouts'  => [],
				'parameters' => [
					'link' => 'none',
					'intro' => '[[File:Foo.png|link=Bar]]'
				],
				'this'       => []
			]
		];

		yield [
			[ 'Foo=Bar', 'link=none', '?ABC' ],
			true,
			[
				'showMode'   => true,
				'templateArgs' => false,
				'query'      => '[[:Foo=Bar]]',
				'printouts'  => [
					'df76b46d65f71fd1a36054ec00947665' => [
						'label' => 'ABC',
						'params' => []
					]
				],
				'parameters' => [
					'link' => 'none'
				],
				'this'       => []
			]
		];

		// #502
		yield [
			[ 'Foo=Bar', 'link=none', 'template=test', '?ABC' ],
			true,
			[
				'showMode'   => true,
				'templateArgs' => true,
				'query'      => '[[:Foo=Bar]]',
				'printouts'  => [
					'2a30f08efdf827f7e76b895fde0fe670' => [
						 'label' => 'ABC',
						 'params' => []
					]
				],
				'parameters' => [
					'link' => 'none',
					'template' => 'test'
				],
				'this'       => []
			]
		];
	}

	public function legacyParametersProvider() {
		yield [
			[
				'showMode'   => false,
				'templateArgs' => false,
				'query'      => '[[Foo::Bar]]',
				'printouts'  => [
					'0bfab051cd82c364058617af13e9874a' => [
						'label'   => 'Foobar',
						'params'  => [
							'abc' => '123'
						]
					],
					'2a30f08efdf827f7e76b895fde0fe670' => [
						'label'   => 'ABC',
						'params'  => [
							'abc' => '456',
							'abc' => '+FOO'
						]
					]
				],
				'parameters' => [
					'limit' => '10'
				],
				'this'       => []
			]
		];

		yield [
			[
				'showMode'   => true,
				'templateArgs' => false,
				'query'      => '[[:Foo=Bar]]',
				'printouts'  => [],
				'parameters' => [
					'link' => 'none'
				],
				'this'       => []
			]
		];
	}
}
