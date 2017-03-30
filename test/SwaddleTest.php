<?php

use liampm\Swaddle\Exception\SwaddlePropertyDoesNotExistException;
use liampm\Swaddle\Swaddle;
use PHPUnit\Framework\TestCase;

/**
 * Test the Swaddle.
 */
class SwaddleTest extends TestCase
{

    /**
     * Test that can wrap an array.
     *
     * @covers liampm\Swaddle\Swaddle::wrapArray()
     */
    public function test_wrapping_array()
    {
        $this->assertInstanceOf(Swaddle::class, Swaddle::wrapArray([]));
        $this->assertInstanceOf(Swaddle::class, Swaddle::wrapArray(['country' => 'UK']));
        $this->assertInstanceOf(Swaddle::class, Swaddle::wrapArray([
            'timezone' => 'Europe/London',
            'default'  => 'UTC',
        ]));
    }
    /**
     * Test that can wrap an object.
     *
     * @covers liampm\Swaddle\Swaddle::wrapObject()
     */
    public function test_wrapping_object()
    {
        $this->assertInstanceOf(Swaddle::class, Swaddle::wrapObject(new stdClass()));
        $this->assertInstanceOf(Swaddle::class, Swaddle::wrapObject((object)['length' => 33.333]));
        $this->assertInstanceOf(Swaddle::class, Swaddle::wrapObject((object)[
            'height' => 10.1,
            'score' => -100,
            'age'   => 15,
        ]));
    }

    /**
     * Test that it correctly determines that it does not have a property.
     *
     * @covers liampm\Swaddle\Swaddle::hasProperty()
     */
    public function test_has_non_existent_property()
    {
        $swaddle = Swaddle::wrapArray([]);

        $this->assertFalse($swaddle->hasProperty('favourites'));
    }

    /**
     * Test that it correctly determines that it has a property.
     *
     * @covers liampm\Swaddle\Swaddle::hasProperty()
     */
    public function test_has_existing_property()
    {
        $swaddle = Swaddle::wrapArray(['favourites' => []]);

        $this->assertTrue($swaddle->hasProperty('favourites'));
    }

    /**
     * Test that an existing property even when there isn't a default value.
     *
     * @covers liampm\Swaddle\Swaddle::getProperty()
     */
    public function test_retrieving_existing_property_without_default()
    {
        $swaddle = Swaddle::wrapArray(['locale' => 'en_GB']);

        $this->assertSame('en_GB', $swaddle->getProperty('locale'));
    }

    /**
     * Test that an existing property is returned over the default value.
     *
     * @covers liampm\Swaddle\Swaddle::getProperty()
     */
    public function test_retrieving_existing_property_with_default()
    {
        $swaddle = Swaddle::wrapArray(['type' => 'line']);

        $this->assertSame('line', $swaddle->getProperty('type', 'bar'));
    }

    /**
     * Test that a stdClass property is converted to a Swaddle when retrieved from a deep Swaddle.
     *
     * @covers liampm\Swaddle\Swaddle::getProperty()
     */
    public function test_retrieving_stdClass_property_in_deep_swaddle()
    {
        $swaddle = Swaddle::wrapArray(['object' => new stdClass()]);

        $property = $swaddle->getProperty('object');

        $this->assertInstanceOf(Swaddle::class, $property);
        $this->assertEquals(Swaddle::wrapObject(new stdClass()), $property);
    }

    /**
     * Test that an array property is converted to a Swaddle when retrieved from a deep Swaddle.
     *
     * @covers liampm\Swaddle\Swaddle::getProperty()
     */
    public function test_retrieving_array_property_in_deep_swaddle()
    {
        $swaddle = Swaddle::wrapArray(['object' => []]);

        $property = $swaddle->getProperty('object');

        $this->assertInstanceOf(Swaddle::class, $property);
        $this->assertEquals(Swaddle::wrapArray([]), $property);
    }

    /**
     * Test that a stdClass property remains unaltered when retrieved from a non deep Swaddle.
     *
     * @covers liampm\Swaddle\Swaddle::getProperty()
     */
    public function test_retrieving_stdClass_property_in_non_deep_swaddle()
    {
        $swaddle = Swaddle::wrapArray(['settings' => (object)['on' => true]], false);

        $this->assertEquals((object)['on' => true], $swaddle->getProperty('settings'));
    }

    /**
     * Test that an array property remains unaltered when retrieved from a non deep Swaddle.
     *
     * @covers liampm\Swaddle\Swaddle::getProperty()
     */
    public function test_retrieving_array_property_in_non_deep_swaddle()
    {
        $swaddle = Swaddle::wrapArray(['settings' => ['on' => true]], false);

        $this->assertEquals(['on' => true], $swaddle->getProperty('settings'));
    }

    /**
     * Test that the default value is returned when trying to get a property that does not exist and a default is provided.
     *
     * @covers liampm\Swaddle\Swaddle::getProperty()
     */
    public function test_retrieving_non_existent_property_with_default()
    {
        $swaddle = Swaddle::wrapArray([]);

        $this->assertSame(7, $swaddle->getProperty('count', 7));
    }

    /**
     * Test that an exception is thrown when trying to get a property that does not exist without providing a default value.
     *
     * @covers liampm\Swaddle\Swaddle::getProperty()
     */
    public function test_retrieving_non_existent_property_without_default_throws()
    {
        $swaddle = Swaddle::wrapArray(['enabled' => false]);

        $this->expectException(SwaddlePropertyDoesNotExistException::class);
        $this->expectExceptionMessage(
            'There is no property with the name "disabled" in this Swaddle. Available properties are: "enabled".'
        );

        $swaddle->getProperty('disabled');
    }

    /**
     * Test the non-silent removal of a property that does exist.
     *
     * @covers liampm\Swaddle\Swaddle::removeProperty()
     */
    public function test_non_silent_removal_of_existing_property()
    {
        $swaddle = Swaddle::wrapArray(['font' => 'Comic sans']);

        $swaddle->removeProperty('font', true);

        $this->assertEquals(Swaddle::wrapArray([]), $swaddle);
    }

    /**
     * Test the silent removal of a property that does exist.
     *
     * @covers liampm\Swaddle\Swaddle::removeProperty()
     */
    public function test_silent_removal_of_existing_property()
    {
        $swaddle = Swaddle::wrapArray(['hasParent' => true, 'hasChildren' => false]);

        $swaddle->removeProperty('hasParent', true);

        $this->assertEquals(Swaddle::wrapArray(['hasChildren' => false]), $swaddle);
    }

    /**
     * Test that an exception is thrown when we do a non-silent remove of a property which does not exist.
     *
     * @covers liampm\Swaddle\Swaddle::removeProperty()
     */
    public function test_non_silent_removal_of_non_existent_property_throws()
    {
        $swaddle = Swaddle::wrapArray(['background-colour' => 'red', 'colour' => 'black']);

        $this->expectException(SwaddlePropertyDoesNotExistException::class);
        $this->expectExceptionMessage(
            'There is no property with the name "foreground-colour" in this Swaddle. Available properties are: ' .
            '"background-colour", "colour".'
        );

        $swaddle->removeProperty('foreground-colour');
    }

    /**
     * Test that nothing happens when we silently remove a property which does not exist.
     *
     * @covers liampm\Swaddle\Swaddle::removeProperty()
     */
    public function test_silent_removal_of_non_existent_property()
    {
        $swaddle         = Swaddle::wrapArray(['limit' => 10]);
        $expectedSwaddle = Swaddle::wrapArray(['limit' => 10]);

        $swaddle->removeProperty('cap', true);

        $this->assertEquals($expectedSwaddle, $swaddle);
    }

    /**
     * Test that we can set a value for a property which already existed.
     *
     * @covers liampm\Swaddle\Swaddle::setProperty()
     */
    public function test_setting_of_existing_property()
    {
        $swaddle = Swaddle::wrapArray(['reversed' => true]);

        $swaddle->setProperty('reversed', 'false');

        $this->assertEquals(Swaddle::wrapArray(['reversed' => 'false']), $swaddle);
    }

    /**
     * Test that we can set a value for a property which did not previously exist.
     *
     * @covers liampm\Swaddle\Swaddle::setProperty()
     */
    public function test_setting_of_non_existant_property()
    {
        $swaddle = Swaddle::wrapArray(['used' => true, 'user_count' => 101]);

        $swaddle->setProperty('likes', 3);

        $this->assertEquals(Swaddle::wrapArray(['used' => true, 'user_count' => 101, 'likes' => 3]), $swaddle);
    }

    /**
     * Test that we can unwrap an empty Swaddle and get the expected result.
     *
     * @covers liampm\Swaddle\Swaddle::unwrap()
     */
    public function test_unwrapping_empty_swaddle()
    {
        $swaddle = Swaddle::wrapArray([]);

        $this->assertEquals(new stdClass(), $swaddle->unwrap());
    }

    /**
     * Test that we can unwrap a simple Swaddle and get the expected result.
     *
     * @covers liampm\Swaddle\Swaddle::unwrap()
     */
    public function test_unwrapping_simple_swaddle()
    {
        $swaddle = Swaddle::wrapArray(['working' => 'hope so']);

        $this->assertEquals((object)['working' => 'hope so'], $swaddle->unwrap());
    }

    /**
     * Test that we can unwrap a complex Swaddle and get the expected result.
     *
     * @covers liampm\Swaddle\Swaddle::unwrap()
     */
    public function test_unwrapping_complex_swaddle()
    {
        $config = [
            'title' => 'Mad stuff',
            'properties' => (object)[
                'this' => 'is this',
                'that' => 'is that'
            ],
            'things' => [
                1,
                false,
                0.1,
            ],
            'meta' => [
                'author' => 'Liam',
                'tested' => 'A bit',
            ],
        ];

        $swaddle = Swaddle::wrapArray($config);

        $this->assertEquals((object)$config, $swaddle->unwrap());
    }
}