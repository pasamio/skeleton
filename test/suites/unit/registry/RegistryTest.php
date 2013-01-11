<?php
/**
 * @package     Gris-Gris.Skeleton
 * @subpackage  Registry
 *
 * @copyright   Copyright (C) 2013 Respective authors. All rights reserved.
 * @license     Licensed under the MIT License; see LICENSE.md
 * @link        http://www.phpunit.de/manual/current/en/installation.html
 */

namespace Grisgris\Test\Suites\Unit\Registry;

use Grisgris\Test\Reflection;

use Grisgris\Test\TestCase;
use Grisgris\Registry\Registry;

/**
 * Test case class for Registry.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Registry
 * @since       13.1
 */
class RegistryTest extends TestCase
{
	/**
	 * @var    Registry  The object to be tested.
	 * @since  13.1
	 */
	private $_instance;

	/**
	 * Tests the `__clone` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::__clone
	 * @since   13.1
	 */
	public function test__clone()
	{
		$a = new Registry(array('a' => '123', 'b' => '456'));
		$a->set('foo', 'bar');
		$b = clone $a;

		$this->assertThat(
			serialize($a),
			$this->equalTo(serialize($b))
		);

		$this->assertThat(
			$a,
			$this->logicalNot($this->identicalTo($b)),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Tests the `__toString` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::__toString
	 * @since   13.1
	 */
	public function test__toString()
	{
		$object = new \stdClass;
		$a = new Registry($object);
		$a->set('foo', 'bar');

		// __toString only allows for a JSON value.
		$this->assertThat(
			(string) $a,
			$this->equalTo('{"foo":"bar"}'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Tests the `jsonSerialize` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::jsonSerialize
	 * @since   13.1
	 */
	public function testJsonSerialize()
	{
		if (version_compare(PHP_VERSION, '5.4.0', '<'))
		{
			$this->markTestSkipped('This test requires PHP 5.4 or newer.');
		}

		$object = new \stdClass;
		$a = new Registry($object);
		$a->set('foo', 'bar');

		// __toString only allows for a JSON value.
		$this->assertThat(
			json_encode($a),
			$this->equalTo('{"foo":"bar"}'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Tests serializing Registry objects.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testSerialize()
	{
		$a = new Registry;
		$a->set('foo', 'bar');

		$serialized = serialize($a);
		$b = unserialize($serialized);

		$this->assertThat(
			$b,
			$this->equalTo($a),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Tests the `def` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::def
	 * @since   13.1
	 */
	public function testDef()
	{
		$a = new Registry;

		$this->assertThat(
			$a->def('foo', 'bar'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '. def should return default value'
		);

		$this->assertThat(
			$a->get('foo'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '. default should now be the current value'
		);
	}

	/**
	 * Tests the `bindData` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::bindData
	 * @since   13.1
	 */
	public function testBindData()
	{
		$a = new Registry;
		$parent = new \stdClass;

		Reflection::invoke($a, 'bindData', $parent, 'foo');
		$this->assertThat(
			$parent->{0},
			$this->equalTo('foo'),
			'Line: ' . __LINE__ . ' The input value should exist in the parent object.'
		);

		Reflection::invoke($a, 'bindData', $parent, array('foo' => 'bar'));
		$this->assertThat(
			$parent->{'foo'},
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . ' The input value should exist in the parent object.'
		);

		Reflection::invoke($a, 'bindData', $parent, array('level1' => array('level2' => 'value2')));
		$this->assertThat(
			$parent->{'level1'}->{'level2'},
			$this->equalTo('value2'),
			'Line: ' . __LINE__ . ' The input value should exist in the parent object.'
		);

		Reflection::invoke($a, 'bindData', $parent, array('intarray' => array(0, 1, 2)));
		$this->assertThat(
			$parent->{'intarray'},
			$this->equalTo(array(0, 1, 2)),
			'Line: ' . __LINE__ . ' The un-associative array should bind natively.'
		);
	}

	/**
	 * Tests the `exists` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::exists
	 * @since   13.1
	 */
	public function testExists()
	{
		$a = new Registry;
		$a->set('foo', 'bar1');
		$a->set('config.foo', 'bar2');
		$a->set('deep.level.foo', 'bar3');

		$this->assertThat(
			$a->exists('foo'),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' The path should exist, returning true.'
		);

		$this->assertThat(
			$a->exists('config.foo'),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' The path should exist, returning true.'
		);

		$this->assertThat(
			$a->exists('deep.level.foo'),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' The path should exist, returning true.'
		);

		$this->assertThat(
			$a->exists('deep.level.bar'),
			$this->isFalse(),
			'Line: ' . __LINE__ . ' The path should not exist, returning false.'
		);

		$this->assertThat(
			$a->exists('bar.foo'),
			$this->isFalse(),
			'Line: ' . __LINE__ . ' The path should not exist, returning false.'
		);
	}

	/**
	 * Tests the `get` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::get
	 * @since   13.1
	 */
	public function testGet()
	{
		$a = new Registry;
		$a->set('foo', 'bar');
		$this->assertEquals('bar', $a->get('foo'), 'Line: ' . __LINE__ . ' get method should work.');
		$this->assertNull($a->get('xxx.yyy'), 'Line: ' . __LINE__ . ' get should return null when not found.');
	}

	/**
	 * Tests the `loadArray` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::loadArray
	 * @since   13.1
	 */
	public function testLoadArray()
	{
		$array = array(
			'foo' => 'bar'
		);
		$registry = new Registry;
		$result = $registry->loadArray($array);

		// Returns itself for chaining.
		$this->assertSame($registry, $result);

		// Test getting a known value.
		$this->assertThat(
			$registry->get('foo'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Tests the `loadFile` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::loadFile
	 * @since   13.1
	 */
	public function testLoadFile()
	{
		$registry = new Registry;

		// JSON.
		$result = $registry->loadFile(__DIR__ . '/stubs/registry.json');

		// Returns itself for chaining.
		$this->assertSame($registry, $result);

		// Test getting a known value.
		$this->assertThat(
			$registry->get('foo'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);

		// INI.
		$result = $registry->loadFile(__DIR__ . '/stubs/registry.ini', 'ini');

		// Returns itself for chaining.
		$this->assertSame($registry, $result);

		// Test getting a known value.
		$this->assertThat(
			$registry->get('foo'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);

		// INI + section.
		$result = $registry->loadFile(__DIR__ . '/stubs/registry.ini', 'ini', array('processSections' => true));

		// Returns itself for chaining.
		$this->assertSame($registry, $result);

		// Test getting a known value.
		$this->assertThat(
			$registry->get('section.foo'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);

		// XML and PHP versions do not support stringToObject.
	}

	/**
	 * Tests the `loadString` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::loadString
	 * @since   13.1
	 */
	public function testLoadString()
	{
		$registry = new Registry;
		$result = $registry->loadString('foo="testloadini1"', 'INI');

		// Test getting a known value.
		$this->assertThat(
			$registry->get('foo'),
			$this->equalTo('testloadini1'),
			'Line: ' . __LINE__ . '.'
		);

		// Returns itself for chaining.
		$this->assertSame($registry, $result);

		$result = $registry->loadString("[section]\nfoo=\"testloadini2\"", 'INI');

		// Returns itself for chaining.
		$this->assertSame($registry, $result);

		// Test getting a known value.
		$this->assertThat(
			$registry->get('foo'),
			$this->equalTo('testloadini2'),
			'Line: ' . __LINE__ . '.'
		);

		$result = $registry->loadString("[section]\nfoo=\"testloadini3\"", 'INI', array('processSections' => true));

		// Returns itself for chaining.
		$this->assertSame($registry, $result);

		// Test getting a known value after processing sections.
		$this->assertThat(
			$registry->get('section.foo'),
			$this->equalTo('testloadini3'),
			'Line: ' . __LINE__ . '.'
		);

		$string = '{"foo":"testloadjson"}';

		$registry = new Registry;
		$result = $registry->loadString($string);

		// Returns itself for chaining.
		$this->assertSame($registry, $result);

		// Test getting a known value.
		$this->assertThat(
			$registry->get('foo'),
			$this->equalTo('testloadjson'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Tests the `loadObject` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::loadObject
	 * @since   13.1
	 */
	public function testLoadObject()
	{
		$object = new \stdClass;
		$object->foo = 'testloadobject';

		$registry = new Registry;
		$result = $registry->loadObject($object);

		// Returns itself for chaining.
		$this->assertSame($registry, $result);

		// Test getting a known value.
		$this->assertThat(
			$registry->get('foo'),
			$this->equalTo('testloadobject'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Tests the `merge` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::merge
	 * @since   13.1
	 */
	public function testMerge()
	{
		$array1 = array(
			'foo' => 'bar',
			'hoo' => 'hum',
			'dum' => array(
				'dee' => 'dum'
			)
		);

		$array2 = array(
			'foo' => 'soap',
			'dum' => 'huh'
		);
		$registry1 = new Registry;
		$registry1->loadArray($array1);

		$registry2 = new Registry;
		$registry2->loadArray($array2);

		$result = $registry1->merge($registry2);

		// Returns itself for chaining.
		$this->assertSame($registry1, $result);

		// Test getting a known value.
		$this->assertThat(
			$registry1->get('foo'),
			$this->equalTo('soap'),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$registry1->get('dum'),
			$this->equalTo('huh'),
			'Line: ' . __LINE__ . '.'
		);

		// Test merge with zero and blank value
		$json1 = '{"param1":1, "param2":"value2"}';
		$json2 = '{"param1":2, "param2":"", "param3":0, "param4":-1, "param5":1}';
		$a = new Registry($json1);
		$b = new Registry;
		$b->loadString($json2, 'JSON');
		$result = $a->merge($b);

		// Returns itself for chaining.
		$this->assertSame($a, $result);

		// New param with zero value should show in merged registry
		$this->assertEquals(2, $a->get('param1'), '$b value should override $a value');
		$this->assertEquals('value2', $a->get('param2'), '$a value should override blank $b value');
		$this->assertEquals(0, $a->get('param3'), '$b value of 0 should override $a value');
		$this->assertEquals(-1, $a->get('param4'), '$b value of -1 should override $a value');
		$this->assertEquals(1, $a->get('param5'), '$b value of 1 should override $a value');
	}

	/**
	 * Tests the `set` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::set
	 * @since   13.1
	 */
	public function testSet()
	{
		$a = new Registry;
		$a->set('foo', 'testsetvalue1');

		$this->assertThat(
			$a->set('foo', 'testsetvalue2'),
			$this->equalTo('testsetvalue2'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Tests the `toArray` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::toArray
	 * @since   13.1
	 */
	public function testToArray()
	{
		$a = new Registry;
		$a->set('foo1', 'testtoarray1');
		$a->set('foo2', 'testtoarray2');
		$a->set('config.foo3', 'testtoarray3');

		$expected = array(
			'foo1' => 'testtoarray1',
			'foo2' => 'testtoarray2',
			'config' => array('foo3' => 'testtoarray3')
		);

		$this->assertThat(
			$a->toArray(),
			$this->equalTo($expected),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Tests the `toObject` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::toObject
	 * @since   13.1
	 */
	public function testToObject()
	{
		$a = new Registry;
		$a->set('foo1', 'testtoobject1');
		$a->set('foo2', 'testtoobject2');
		$a->set('config.foo3', 'testtoobject3');

		$expected = new \stdClass;
		$expected->foo1 = 'testtoobject1';
		$expected->foo2 = 'testtoobject2';
		$expected->config = new \stdClass;
		$expected->config->foo3 = 'testtoobject3';

		$this->assertThat(
			$a->toObject(),
			$this->equalTo($expected),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Tests the `toString` method.
	 *
	 * @return  void
	 *
	 * @covers  Grisgris\Registry\Registry::toString
	 * @since   13.1
	 */
	public function testToString()
	{
		$a = new Registry;
		$a->set('foo1', 'testtostring1');
		$a->set('foo2', 'testtostring2');
		$a->set('config.foo3', 'testtostring3');

		$this->assertThat(
			trim($a->toString('JSON')),
			$this->equalTo(
				'{"foo1":"testtostring1","foo2":"testtostring2","config":{"foo3":"testtostring3"}}'
			),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			trim($a->toString('INI')),
			$this->equalTo(
				"foo1=\"testtostring1\"\nfoo2=\"testtostring2\"\n\n[config]\nfoo3=\"testtostring3\""
			),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Prepares the environment before running a test.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_instance = new Registry;
	}

	/**
	 * Cleans up the environment after running a test.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	protected function tearDown()
	{
		$this->_instance = null;

		parent::tearDown();
	}
}
