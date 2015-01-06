<?php
namespace Tests\Drahak\Restful\Utils;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Utils\RequestFilter;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Utils\RequestFilter.
 *
 * @testCase Tests\Drahak\Restful\Utils\RequestFilterTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Utils
 */
class RequestFilterTest extends TestCase
{

	/** @var MockInterface */
	private $request;

	/** @var RequestFilter */
	private $filter;
    
    protected function setUp()
    {
		parent::setUp();
		$this->request = $this->mockista->create('Nette\Http\IRequest');
		$this->filter = new RequestFilter($this->request);
    }
    
    public function testGetFieldsListFromString()
    {
		$this->request->expects('getQuery')
			->once()
			->with(RequestFilter::FIELDS_KEY)
			->andReturn('-any,item,list,');

		$result = $this->filter->getFieldList();
		Assert::type('array', $result);
		Assert::same($result, array('-any','item','list'));
    }

    public function testGetFieldListFromArrayInUrl() 
    {
    	$fields = array('-any', 'item', 'list');
		$this->request->expects('getQuery')
			->once()
			->with(RequestFilter::FIELDS_KEY)
			->andReturn($fields);

		$result = $this->filter->getFieldList();
		Assert::type('array', $result);
		Assert::equal($result, $fields);
    }

	public function testGetSortList()
	{
		$this->request->expects('getQuery')
			->once()
			->with('sort')
			->andReturn('-any,item,list,');

		$expected = array(
			'any' => RequestFilter::SORT_DESC,
			'item' => RequestFilter::SORT_ASC,
			'list' => RequestFilter::SORT_ASC
		);

		$result = $this->filter->getSortList();
		Assert::type('array', $result);
		Assert::same($result, $expected);
	}

	public function testGetSearchQuery()
	{
		$this->request->expects('getQuery')
			->once()
			->with('q')
			->andReturn('search string');

		Assert::equal($this->filter->getSearchQuery(), 'search string');
	}

	public function testCreatePaginator()
	{
		$this->request->expects('getQuery')
			->once()
			->with('offset', NULL)
			->andReturn(20);
		$this->request->expects('getQuery')
			->once()
			->with('limit', NULL)
			->andReturn(10);

		$paginator = $this->filter->getPaginator();
		Assert::true($paginator instanceof Nette\Utils\Paginator);
		Assert::equal($paginator->getItemsPerPage(), 10);
		Assert::equal($paginator->getPage(), 3);
		Assert::equal($paginator->getOffset(), 20);
	}

	public function testThrowsExceptionWhenOffsetOrLimitNotProvided()
	{
		$this->request->expects('getQuery')
			->once()
			->with('offset', NULL)
			->andReturn(20);
		$this->request->expects('getQuery')
			->once()
			->with('limit', NULL)
			->andReturn(NULL);

		Assert::throws(function() {
			$this->filter->getPaginator();
		}, 'Drahak\Restful\InvalidStateException');
	}

}
\run(new RequestFilterTest());
