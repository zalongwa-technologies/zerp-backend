<?php

require_once(__DIR__ . '/../src/DBAwareTest.php');
require_once(__DIR__ . '/../../api/odoo/PayablesApi.php');

use PHPUnit\Framework\TestCase;

final class PayablesRepositoryIntegrationTest extends TestCase
{
	use DBAwareTest;

	private array $fixture = [];

	protected function setUp(): void
	{
		$this->assertCanConnect();
		$this->db->begin_transaction();
		$this->fixture = $this->createFixture();
	}

	protected function tearDown(): void
	{
		if ($this->db instanceof mysqli) {
			$this->db->rollback();
		}
		parent::tearDown();
	}

	public function testPayablesFinancialExportContract(): void
	{
		$repository = new PayablesRepository($this->db);
		$filters = PayablesRequest::parse([
			'supplier_id' => $this->fixture['supplier'],
			'from_date' => '2099-01-01',
			'to_date' => '2099-01-01',
			'page_size' => '2',
		]);

		$page1 = $repository->export($filters);
		$this->assertSame(6, $page1['total'], 'Void transactions must be excluded by default');
		$this->assertCount(2, $page1['data']);
		$page1Again = $repository->export($filters);
		$this->assertSame(array_column($page1['data'], 'external_id'), array_column($page1Again['data'], 'external_id'), 'External IDs and pagination must be stable');

		$all = [];
		for ($page = 1; $page <= 3; $page++) {
			$filters['page'] = $page;
			$all = array_merge($all, $repository->export($filters)['data']);
		}
		$this->assertCount(6, array_unique(array_column($all, 'transaction_id')), 'Pagination must not duplicate or omit transactions');
		$byId = [];
		foreach ($all as $transaction) $byId[$transaction['transaction_id']] = $transaction;

		$invoice = $byId[$this->fixture['invoice']];
		$this->assertSame('zerp-supptrans-' . $this->fixture['invoice'], $invoice['external_id']);
		$this->assertSame('zerp-supplier-' . $this->fixture['supplier'], $invoice['supplier']['external_id']);
		$this->assertSame(100.0, $invoice['net_amount']);
		$this->assertSame(18.0, $invoice['tax_amount']);
		$this->assertSame(118.0, $invoice['gross_amount']);
		$this->assertSame(55.0, $invoice['allocated_amount']);
		$this->assertSame(63.0, $invoice['outstanding_amount']);
		$this->assertIsFloat($invoice['gross_amount']);
		$this->assertCount(1, $invoice['taxes']);
		$this->assertSame('zerp-supptrans-tax-' . $this->fixture['invoice'] . '-' . $this->fixture['tax'], $invoice['taxes'][0]['external_id']);
		$this->assertSame(18.0, $invoice['taxes'][0]['amount']);
		$this->assertCount(1, $invoice['accounting_lines']);
		$this->assertSame($this->fixture['account'], $invoice['accounting_lines'][0]['account_code']);
		$this->assertCount(3, $invoice['allocations'], 'Multiple payments and a debit note must be linked to the invoice');

		$payment = $byId[$this->fixture['payment']];
		$this->assertSame(-90.0, $payment['gross_amount']);
		$this->assertCount(2, $payment['allocations'], 'One payment must link to multiple invoices');
		$this->assertSame('zerp-suppalloc-' . $this->fixture['allocation'], $payment['allocations'][0]['external_id']);
		$this->assertSame($this->fixture['payment'], $payment['allocations'][0]['source_transaction']['transaction_id']);
		$this->assertSame($this->fixture['invoice'], $payment['allocations'][0]['target_transaction']['transaction_id']);

		$unpaid = $byId[$this->fixture['unpaid_invoice']];
		$this->assertSame([], $unpaid['allocations']);
		$this->assertSame(25.0, $unpaid['outstanding_amount']);
		$this->assertArrayHasKey($this->fixture['debit'], $byId, 'Debit notes must be exported');

		$filters['include_settled'] = false;
		$filters['page'] = 1;
		$filters['page_size'] = 100;
		$unsettled = $repository->export($filters);
		$this->assertSame(2, $unsettled['total']);
		$this->assertNotContains($this->fixture['settled_invoice'], array_column($unsettled['data'], 'transaction_id'));

		$filters['include_settled'] = true;
		$filters['include_void'] = true;
		$this->assertSame(7, $repository->export($filters)['total']);
	}

	private function createFixture(): array
	{
		$supplier = $this->query('SELECT supplierid FROM suppliers ORDER BY supplierid LIMIT 1');
		$tax = $this->query('SELECT taxid FROM taxauthorities ORDER BY taxid LIMIT 1');
		$gl = $this->query('SELECT g.accountcode, p.periodno FROM chartmaster g CROSS JOIN periods p ORDER BY g.accountcode, p.periodno LIMIT 1');
		if ($supplier === [] || $tax === [] || $gl === []) {
			$this->markTestSkipped('Supplier, tax authority, GL account and period fixtures are required');
		}
		$supplierId = $supplier[0]['supplierid'];
		$base = random_int(100000000, 900000000);
		$invoice = $this->insertTransaction($base, 20, $supplierId, 'API-INV-1', 100, 18, 55, 0, 0);
		$settledInvoice = $this->insertTransaction($base + 1, 20, $supplierId, 'API-INV-2', 50, 0, 50, 1, 0);
		$unpaidInvoice = $this->insertTransaction($base + 2, 20, $supplierId, 'API-INV-3', 25, 0, 0, 0, 0);
		$payment = $this->insertTransaction($base + 3, 22, $supplierId, 'API-PAY-1', -90, 0, -90, 1, 0);
		$payment2 = $this->insertTransaction($base + 4, 22, $supplierId, 'API-PAY-2', -10, 0, -10, 1, 0);
		$debit = $this->insertTransaction($base + 5, 21, $supplierId, 'API-DEBIT-1', -5, 0, -5, 1, 0);
		$this->insertTransaction($base + 6, 20, $supplierId, 'API-VOID', 99, 0, 0, 0, 1);

		$allocation = $this->insertAllocation($payment, $invoice, 40);
		$this->insertAllocation($payment, $settledInvoice, 50);
		$this->insertAllocation($payment2, $invoice, 10);
		$this->insertAllocation($debit, $invoice, 5);

		$stmt = $this->db->prepare('INSERT INTO supptranstaxes (supptransid, taxauthid, taxamount) VALUES (?, ?, 18)');
		$taxId = (int)$tax[0]['taxid'];
		$stmt->bind_param('ii', $invoice, $taxId); $stmt->execute(); $stmt->close();
		$stmt = $this->db->prepare("INSERT INTO gltrans (type, typeno, trandate, periodno, account, narrative, amount) VALUES (20, ?, '2099-01-01', ?, ?, 'Payables API test', 100)");
		$period = (int)$gl[0]['periodno']; $account = $gl[0]['accountcode'];
		$stmt->bind_param('iis', $base, $period, $account); $stmt->execute(); $stmt->close();

		return ['supplier' => $supplierId, 'tax' => $taxId, 'account' => $account, 'invoice' => $invoice, 'settled_invoice' => $settledInvoice, 'unpaid_invoice' => $unpaidInvoice, 'payment' => $payment, 'payment2' => $payment2, 'debit' => $debit, 'allocation' => $allocation];
	}

	private function insertTransaction(int $number, int $type, string $supplier, string $reference, float $net, float $tax, float $allocated, int $settled, int $void): int
	{
		$stmt = $this->db->prepare("INSERT INTO supptrans (transno, type, supplierno, suppreference, trandate, duedate, inputdate, settled, rate, ovamount, ovgst, alloc, transtext, void) VALUES (?, ?, ?, ?, '2099-01-01', '2099-01-31', '2099-01-01 12:00:00', ?, 1, ?, ?, ?, 'Payables API integration test', ?)");
		$stmt->bind_param('iissidddi', $number, $type, $supplier, $reference, $settled, $net, $tax, $allocated, $void);
		$stmt->execute(); $id = $this->db->insert_id; $stmt->close();
		return $id;
	}

	private function insertAllocation(int $from, int $to, float $amount): int
	{
		$stmt = $this->db->prepare("INSERT INTO suppallocs (amt, datealloc, transid_allocfrom, transid_allocto) VALUES (?, '2099-01-01', ?, ?)");
		$stmt->bind_param('dii', $amount, $from, $to); $stmt->execute(); $id = $this->db->insert_id; $stmt->close();
		return $id;
	}
}
