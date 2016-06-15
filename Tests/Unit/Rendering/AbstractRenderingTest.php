<?php
/**
 * Class AbstractRenderingTest
 */

namespace FRUIT\Ink\Tests\Unit\Rendering;

use FRUIT\Ink\Rendering\Header;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class AbstractRenderingTest
 */
class AbstractRenderingTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testNoBreakContent()
    {
        $instance = new Header();
        $string = "Test";
        $expected = "Test";
        $this->assertSame($expected, $instance->breakContent($string));
    }
}
