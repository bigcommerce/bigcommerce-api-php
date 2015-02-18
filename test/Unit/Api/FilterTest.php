<?php
namespace Bigcommerce\Unit\Api;

use Bigcommerce\Api\Filter;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testToQueryBuildsAnAppropriateQueryString()
    {
        $filter = new Filter(array('a' => 1, 'b' => 'orange'));
        $this->assertSame('?a=1&b=orange', $filter->toQuery());
    }

    public function testUpdateParameter()
    {
        $filter = new Filter(array('a' => 'b'));
        $this->assertSame('?a=b', $filter->toQuery());
        $filter->a = 'c';
        $this->assertSame('?a=c', $filter->toQuery());
    }

    public function testStaticCreateMethodAssumesIntegerParameterIsPageNumber()
    {
        $filter = Filter::create(1);
        $this->assertSame('?page=1', $filter->toQuery());
    }

    public function testStaticCreateMethodReturnsFilterObjectIfCalledWithFilterObject()
    {
        $original = new Filter(array('a' => 'b'));
        $filter = Filter::create($original);
        $this->assertSame($original, $filter);
    }

    public function testStaticCreateMethodReturnsCorrectlyConfiguredFilter()
    {
        $filter = Filter::create(array('a' => 'b'));
        $this->assertSame('?a=b', $filter->toQuery());
    }
}
