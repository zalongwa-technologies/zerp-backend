<?php

require_once(__DIR__ . '/../../api/odoo/ReceivablesApi.php');

use PHPUnit\Framework\TestCase;

final class ReceivablesApiUnitTest extends TestCase
{
	public function testValidRequestAndDefaults(): void
	{
		$filters = ReceivablesRequest::parse([
			'resource' => 'invoice_lines', 'customer_id' => 'CUSTOMER-1', 'invoice_id' => '42',
			'updated_since' => '2026-01-01T00:00:00+03:00', 'include_voided' => 'true',
			'include_lines' => 'false', 'page' => '2', 'page_size' => '500',
		]);
		$this->assertSame('invoice_lines', $filters['resource']);
		$this->assertSame('CUSTOMER-1', $filters['customer_id']);
		$this->assertSame(42, $filters['invoice_id']);
		$this->assertTrue($filters['include_voided']);
		$this->assertFalse($filters['include_lines']);
		$this->assertSame(2, $filters['page']);
		$this->assertSame(500, $filters['page_size']);
	}

	/** @dataProvider invalidRequests */
	public function testInvalidRequests(array $query, string $code): void
	{
		try {
			ReceivablesRequest::parse($query);
			$this->fail('Expected validation to fail');
		} catch (ReceivablesApiException $e) {
			$this->assertSame(400, $e->status);
			$this->assertSame($code, $e->errorCode);
		}
	}

	public static function invalidRequests(): array
	{
		return [
			'missing resource' => [[], 'INVALID_RESOURCE'],
			'unknown resource' => [['resource' => 'suppliers'], 'INVALID_RESOURCE'],
			'identifier injection' => [['resource' => 'customers', 'customer_id' => "x' OR 1=1--"], 'INVALID_IDENTIFIER'],
			'invalid date' => [['resource' => 'invoices', 'from_date' => '2026-02-30'], 'INVALID_DATE'],
			'reversed dates' => [['resource' => 'invoices', 'from_date' => '2026-02-02', 'to_date' => '2026-02-01'], 'INVALID_DATE_RANGE'],
			'invalid timestamp' => [['resource' => 'items', 'updated_since' => '2026-01-01'], 'INVALID_DATETIME'],
			'invalid status' => [['resource' => 'invoices', 'status' => 'cancelled'], 'INVALID_STATUS'],
			'invalid boolean' => [['resource' => 'invoices', 'include_lines' => 'maybe'], 'INVALID_BOOLEAN'],
			'zero page' => [['resource' => 'customers', 'page' => '0'], 'INVALID_INTEGER'],
			'large page' => [['resource' => 'customers', 'page_size' => '501'], 'INVALID_PAGE_SIZE'],
			'unknown parameter' => [['resource' => 'customers', 'drop_table' => '1'], 'UNKNOWN_PARAMETER'],
		];
	}

	public function testAuthentication(): void
	{
		ReceivablesRequest::authenticate('Bearer correct-token', 'correct-token');
		$this->addToAssertionCount(1);
	}

	public function testAuthenticationRejectsWrongToken(): void
	{
		$this->expectException(ReceivablesApiException::class);
		$this->expectExceptionMessage('valid bearer token');
		ReceivablesRequest::authenticate('Bearer wrong-token', 'correct-token');
	}
}
