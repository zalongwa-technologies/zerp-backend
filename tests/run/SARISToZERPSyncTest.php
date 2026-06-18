<?php

require_once(__DIR__ . '/../../includes/ZERPSync.php');

use PHPUnit\Framework\TestCase;

class SARISToZERPSyncTest extends TestCase {
	public function testShortCustomerCodeIsPreserved(): void {
		$this->assertSame('STU123', ZERPSync::customerCode('STU123'));
	}

	public function testLongOrFormattedCustomerCodeIsStableAndFitsSchema(): void {
		$first = ZERPSync::customerCode('MUM/2026/000123');
		$second = ZERPSync::customerCode('MUM/2026/000123');

		$this->assertSame($first, $second);
		$this->assertSame(10, strlen($first));
		$this->assertMatchesRegularExpression('/^S[A-F0-9]{9}$/', $first);
	}

	public function testDifferentRegistrationNumbersProduceDifferentCodes(): void {
		$this->assertNotSame(
			ZERPSync::customerCode('MUM/2026/000123'),
			ZERPSync::customerCode('MUM/2026/000124')
		);
	}

	public function testLongRemoteReferenceIsStableAndFitsDebtorTrans(): void {
		$source = str_repeat('INV-2026-', 10);
		$reference = ZERPSync::remoteReference($source);

		$this->assertSame(50, strlen($reference));
		$this->assertSame($reference, ZERPSync::remoteReference($source));
	}

	public function testEmptyIdentifiersAreRejected(): void {
		$this->expectException(InvalidArgumentException::class);
		ZERPSync::customerCode('');
	}
}

?>
