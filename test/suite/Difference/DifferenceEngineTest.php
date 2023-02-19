<?php

declare(strict_types=1);

namespace Eloquent\Phony\Difference;

use AllowDynamicProperties;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class DifferenceEngineTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = new FacadeContainer();
        $this->subject = $this->container->differenceEngine;
        $this->subject->setUseColor(false);
    }

    public function differenceData()
    {
        //                         from               to                      expected
        return [
            'Equal'       => ['foo,bar,baz,qux', 'foo,bar,baz,qux',      'foo,bar,baz,qux'],
            'Insertion'   => ['foo,bar,baz,qux', 'foo,bar,doom,baz,qux', 'foo,bar,{+doom,+}baz,qux'],
            'Deletion'    => ['foo,bar,baz,qux', 'foo,bar,qux',          'foo,bar,[-baz,-]qux'],
            'Replacement' => ['foo,bar,baz,qux', 'foo,bar,doom,qux',     'foo,bar,[-baz-]{+doom+},qux'],
            'Unrelated'   => ['#0{}',            'foo#0{bar}',           '{+foo+}#0[-{}-]{+{bar}+}'],
        ];
    }

    /**
     * @dataProvider differenceData
     */
    public function testDifference($from, $to, $expected)
    {
        $this->assertSame($expected, $this->subject->difference($from, $to));
    }
}
