<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class WildcardMatcherTest extends TestCase
{
    protected function setUp(): void
    {
        $container = new FacadeContainer();
        $this->exporter = $container->exporter;

        $this->matcher = new EqualToMatcher('x', true, $this->exporter);
        $this->minimumArguments = 1;
        $this->maximumArguments = 2;
        $this->subject = new WildcardMatcher($this->matcher, $this->minimumArguments, $this->maximumArguments);
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcher, $this->subject->matcher());
        $this->assertSame($this->minimumArguments, $this->subject->minimumArguments());
        $this->assertSame($this->maximumArguments, $this->subject->maximumArguments());
    }

    public function describeData()
    {
        $container = new FacadeContainer();
        $anyMatcher = $container->anyMatcher;
        $equalToMatcher = new EqualToMatcher('x', true, $container->exporter);

        //                                   matcher          minimum maximum expected
        return [
            'Any amount of anything'     => [$anyMatcher,     0,      -1,     '<any>*'],
            'Any amount of equal to'     => [$equalToMatcher, 0,      -1,     '"x"*'],
            'Minimum amount of anything' => [$anyMatcher,     111,    -1,     '<any>{111,}'],
            'Maximum amount of anything' => [$anyMatcher,     0,      111,    '<any>{,111}'],
            'Range of anything'          => [$anyMatcher,     111,    222,    '<any>{111,222}'],
            'Exact amount of anything'   => [$anyMatcher,     111,    111,    '<any>{111}'],
        ];
    }

    /**
     * @dataProvider describeData
     */
    public function testDescribe($matcher, $minimumArguments, $maximumArguments, $expected)
    {
        $this->subject = new WildcardMatcher($matcher, $minimumArguments, $maximumArguments);

        $this->assertSame($expected, $this->subject->describe());
        $this->assertSame($expected, strval($this->subject));
    }

    public function testMatches()
    {
        $this->assertFalse($this->subject->matches(''));
    }
}
