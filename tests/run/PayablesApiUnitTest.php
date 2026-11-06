<?php

require_once(__DIR__ . '/../../api/odoo/PayablesApi.php');

use PHPUnit\Framework\TestCase;

final class PayablesApiUnitTest extends TestCase
{
	public function testValidDefaultsAndStableTypes(): void
	{
		$filters = PayablesRequest::parse([]);
		$this->assertSame([20, 21, 22, 23], $filters['transaction_types']);
		$this->assertSame(1, $filters['page']);
		$this->assertSame(100, $filters['page_size']);
		$this->assertFalse($filters['include_void']);
		$this->assertTrue($filters['include_settled']);
	}

	public function testValidRequest(): void
	{
		$filters = PayablesRequest::parse(['supplier_id' => '104824986', 'from_date' => '2026-01-01', 'to_date' => '2026-11-05', 'transaction_type' => '22,20,20', 'include_void' => 'true', 'include_settled' => 'false', 'page' => '2', 'page_size' => '500']);
		$this->assertSame([22, 20], $filters['transaction_types']);
		$this->assertTrue($filters['include_void']);
		$this->assertFalse($filters['include_settled']);
		$this->assertSame(2, $filters['page']);
	}

	/** @dataProvider invalidRequests */
	public function testInvalidRequests(array $query, string $code): void
	{
		try {
			PayablesRequest::parse($query);
			$this->fail('Expected request validation to fail');
		} catch (PayablesApiException $e) {
			$this->assertSame(400, $e->status);
			$this->assertSame($code, $e->errorCode);
		}
	}

	public static function invalidRequests(): array
	{
		return [
			'invalid supplier' => [['supplier_id' => "x' OR 1=1--"], 'INVALID_SUPPLIER_ID'],
			'invalid date' => [['from_date' => '2026-02-30'], 'INVALID_DATE'],
			'reversed range' => [['from_date' => '2026-02-02', 'to_date' => '2026-02-01'], 'INVALID_DATE_RANGE'],
			'invalid type' => [['transaction_type' => '20,99'], 'INVALID_TRANSACTION_TYPE'],
			'type injection' => [['transaction_type' => '20,22 OR 1=1'], 'INVALID_TRANSACTION_TYPE'],
			'invalid bool' => [['include_void' => 'sometimes'], 'INVALID_BOOLEAN'],
			'zero page' => [['page' => '0'], 'INVALID_PAGINATION'],
			'large page' => [['page_size' => '501'], 'INVALID_PAGE_SIZE'],
			'overflowing page' => [['page' => (string)PHP_INT_MAX, 'page_size' => '2'], 'INVALID_PAGINATION'],
			'array input' => [['supplier_id' => ['A']], 'INVALID_PARAMETER'],
			'unknown input' => [['drop_table' => '1'], 'UNKNOWN_PARAMETER'],
		];
	}

	public function testAuthentication(): void
	{
		PayablesRequest::authenticate('Bearer correct-token', 'correct-token');
		$this->addToAssertionCount(1);
	}

	/** @dataProvider invalidAuthentication */
	public function testInvalidAuthentication(?string $header): void
	{
		$this->expectException(PayablesApiException::class);
		$this->expectExceptionMessage('valid bearer token');
		PayablesRequest::authenticate($header, 'correct-token');
	}

	public static function invalidAuthentication(): array
	{
		return [[null], [''], ['Basic abc'], ['Bearer wrong-token']];
	}

	public function testMissingServerConfiguration(): void
	{
		try {
			PayablesRequest::authenticate('Bearer anything', null);
			$this->fail('Expected missing configuration to fail');
		} catch (PayablesApiException $e) {
			$this->assertSame(500, $e->status);
			$this->assertSame('API_NOT_CONFIGURED', $e->errorCode);
		}
	}
}
