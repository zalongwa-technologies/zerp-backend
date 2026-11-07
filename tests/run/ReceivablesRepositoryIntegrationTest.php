<?php

require_once(__DIR__ . '/../src/DBAwareTest.php');
require_once(__DIR__ . '/../../api/odoo/ReceivablesApi.php');

use PHPUnit\Framework\TestCase;

final class ReceivablesRepositoryIntegrationTest extends TestCase
{
	use DBAwareTest;

	protected function setUp(): void
	{
		$this->assertCanConnect();
		$this->db->begin_transaction();
	}

	protected function tearDown(): void
	{
		if ($this->db instanceof mysqli) $this->db->rollback();
		parent::tearDown();
	}

	public function testInvoiceExportsHeaderAndLineTaxRecords(): void
	{
		$fixture = $this->fixtureDependencies();
		$number = random_int(100000000, 900000000);

		$stmt = $this->db->prepare("INSERT INTO debtortrans
			(transno,type,debtorno,branchcode,trandate,inputdate,prd,settled,reference,rate,ovamount,ovgst,alloc)
			VALUES (?,10,?,?,'2099-01-01','2099-01-01 12:00:00',?,0,'Receivables API tax test',1,100,18,0)");
		$stmt->bind_param('issi', $number, $fixture['customer'], $fixture['branch'], $fixture['period']);
		$stmt->execute();
		$invoiceId = $this->db->insert_id;
		$stmt->close();

		$stmt = $this->db->prepare('INSERT INTO debtortranstaxes (debtortransid,taxauthid,taxamount) VALUES (?,?,18)');
		$stmt->bind_param('ii', $invoiceId, $fixture['tax']);
		$stmt->execute();
		$stmt->close();

		$stmt = $this->db->prepare("INSERT INTO stockmoves
			(stockid,type,transno,loccode,trandate,userid,debtorno,branchcode,price,prd,reference,qty,discountpercent,standardcost)
			VALUES (?,10,?,?,'2099-01-01','api-test',?,?,100,?,'Taxed item',1,0,0)");
		$stmt->bind_param('sisssi', $fixture['stock'], $number, $fixture['location'], $fixture['customer'], $fixture['branch'], $fixture['period']);
		$stmt->execute();
		$moveId = $this->db->insert_id;
		$stmt->close();

		$stmt = $this->db->prepare('INSERT INTO stockmovestaxes (stkmoveno,taxauthid,taxrate,taxontax,taxcalculationorder) VALUES (?,?,0.18,0,1)');
		$stmt->bind_param('ii', $moveId, $fixture['tax']);
		$stmt->execute();
		$stmt->close();

		$repository = new ReceivablesRepository($this->db);
		$filters = ReceivablesRequest::parse(['resource' => 'invoices', 'invoice_id' => (string)$invoiceId, 'include_lines' => 'true']);
		$result = $repository->export($filters);

		$this->assertSame(1, $result['total']);
		$this->assertCount(1, $result['data']);
		$invoice = $result['data'][0];
		$this->assertSame('18.00', $invoice['tax_amount']);
		$this->assertCount(1, $invoice['taxes']);
		$this->assertSame('zerp-debtortrans-tax-' . $invoiceId . '-' . $fixture['tax'], $invoice['taxes'][0]['external_id']);
		$this->assertSame($fixture['tax'], $invoice['taxes'][0]['tax_authority_id']);
		$this->assertSame('18.00', $invoice['taxes'][0]['amount']);
		$this->assertCount(1, $invoice['lines']);
		$this->assertCount(1, $invoice['lines'][0]['taxes']);
		$this->assertSame('zerp-stockmove-tax-' . $moveId . '-' . $fixture['tax'], $invoice['lines'][0]['taxes'][0]['external_id']);
		$this->assertSame('0.18', $invoice['lines'][0]['taxes'][0]['rate']);
		$this->assertSame('18.00', $invoice['lines'][0]['tax_amount']);
		$this->assertSame('118.00', $invoice['lines'][0]['line_total']);
	}

	private function fixtureDependencies(): array
	{
		$rows = $this->query("SELECT d.debtorno customer,cb.branchcode branch,s.stockid stock,l.loccode location,
			p.periodno period,ta.taxid tax
			FROM debtorsmaster d INNER JOIN custbranch cb ON cb.debtorno=d.debtorno
			CROSS JOIN stockmaster s CROSS JOIN locations l CROSS JOIN periods p CROSS JOIN taxauthorities ta
			ORDER BY d.debtorno,cb.branchcode,s.stockid,l.loccode,p.periodno,ta.taxid LIMIT 1");
		if ($rows === []) $this->markTestSkipped('Customer, branch, item, location, period and tax fixtures are required');
		return [
			'customer' => $rows[0]['customer'], 'branch' => $rows[0]['branch'], 'stock' => $rows[0]['stock'],
			'location' => $rows[0]['location'], 'period' => (int)$rows[0]['period'], 'tax' => (int)$rows[0]['tax'],
		];
	}
}
