<?php

require_once(__DIR__ . '/ZERPXMLRPCClient.php');

class ZERPSync {
	private $pdo;
	private $client;
	private $config;
	private $runId;
	private $stats;
	private $currentMethod = '';
	private $bankMappings = null;

	public function __construct(PDO $pdo, ZERPXMLRPCClient $client, array $config) {
		$this->pdo = $pdo;
		$this->client = $client;
		$this->config = $config;
		$this->runId = $this->uuid();
		$this->stats = [
			'run_id' => $this->runId,
			'students_synced' => 0,
			'invoices_synced' => 0,
			'payments_synced' => 0,
			'partial' => 0,
			'failed' => 0,
			'skipped' => 0,
			'locked' => false,
			'error_summary' => [],
		];
	}

	public function run() {
		if (function_exists('set_time_limit')) {
			set_time_limit(0);
		}
		if (!$this->acquireLock()) {
			$this->stats['locked'] = true;
			$this->stats['skipped']++;
			return $this->stats;
		}

		try {
			$this->resetStaleClaims();
			saris_cli_log('== ZERP: students ==');
			$this->syncStudents();
			saris_cli_log('== ZERP: invoices ==');
			$this->syncInvoices();
			$this->refreshPaymentWaitingStates();
			saris_cli_log('== ZERP: payments ==');
			$this->syncPayments();
			$this->stats['error_summary'] = $this->errorSummary();
		} finally {
			$this->releaseLock();
		}
		return $this->stats;
	}

	public static function customerCode($registrationNumber) {
		$source = trim((string)$registrationNumber);
		if ($source === '') {
			throw new InvalidArgumentException('Student registration number is empty.');
		}
		$clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $source));
		if ($clean !== '' && strlen($clean) <= 10 && strcasecmp($clean, $source) === 0) {
			return $clean;
		}
		return 'S' . strtoupper(substr(hash('sha256', $source), 0, 9));
	}

	public static function remoteReference($reference) {
		$reference = trim((string)$reference);
		if ($reference === '') {
			throw new InvalidArgumentException('Source reference is empty.');
		}
		if (strlen($reference) <= 50) {
			return $reference;
		}
		return substr($reference, 0, 37) . '-' . substr(hash('sha256', $reference), 0, 12);
	}

	private function syncStudents() {
		// batch_size controls memory/request chunking, not the total records per run.
		$lastId = 0;
		while (true) {
			$rows = $this->eligibleRows('students', $lastId);
			if (!$rows) {
				break;
			}
			foreach ($rows as $row) {
				$lastId = max($lastId, (int)$row['id']);
				if (!$this->claim('students', $row['id'])) {
					$this->stats['skipped']++;
					continue;
				}
				$row = $this->rowById('students', $row['id']);
				saris_cli_log("  student id={$row['id']} regno={$row['student_regnumber']}...");
				try {
					$code = $row['zerp_customer_code'] ?: self::customerCode($row['student_regnumber']);
					$branchCode = substr((string)$this->config['branch_code'], 0, 10);
					if ($this->limit($row['student_fullname'], 40) === '') {
						throw new InvalidArgumentException('Student name is empty.');
					}
					if (empty($row['customer_synced_at'])) {
						$this->currentMethod = 'weberp.xmlrpc_GetCustomer';
						$existing = $this->client->getCustomer($code);
						if ($existing !== null) {
							$existingName = trim((string)($existing['name'] ?? ''));
							$expectedName = $this->limit($row['student_fullname'], 40);
							if ($existingName !== '' && strcasecmp($existingName, $expectedName) !== 0) {
								throw new RuntimeException('Customer code ' . $code . ' already belongs to "' . $existingName . '".');
							}
						} else {
							$this->currentMethod = 'weberp.xmlrpc_InsertCustomer';
							$this->client->insertCustomer($this->customerPayload($row, $code));
						}
						$this->updateStudentCustomer($row['id'], $code);
					}

					if (empty($row['branch_synced_at'])) {
						$this->currentMethod = 'weberp.xmlrpc_GetCustomerBranch';
						$existingBranch = $this->client->getBranch($code, $branchCode);
						if ($existingBranch === null) {
							$this->currentMethod = 'weberp.xmlrpc_InsertBranch';
							try {
								$this->client->insertBranch($this->branchPayload($row, $code, $branchCode));
							} catch (Throwable $exception) {
								throw new RuntimeException(
									'Branch creation failed with configured area "'
									. $this->config['branch_area']
									. '", salesperson "' . $this->config['salesperson']
									. '", location "' . $this->config['default_location']
									. '": ' . $exception->getMessage(),
									0,
									$exception
								);
							}
						}
						$this->updateStudentBranch($row['id']);
					}

					$this->markSynced('students', $row['id']);
					$this->stats['students_synced']++;
					saris_cli_log("    synced (customer {$code})");
				} catch (Throwable $exception) {
					$fresh = $this->rowById('students', $row['id']);
					$status = !empty($fresh['customer_synced_at']) || !empty($fresh['branch_synced_at']) ? 'partial' : 'failed';
					$this->markError('students', $row, $status, $exception);
					saris_cli_log("    {$status}: " . $exception->getMessage());
				}
			}
		}
	}

	private function syncInvoices() {
		$lastId = 0;
		while (true) {
			$sql = "SELECT i.*
				FROM invoices i
				INNER JOIN students s ON s.student_regnumber = i.student_regnumber
				WHERE s.sync_status = 'synced'
				  AND i.sync_status IN ('pending', 'failed', 'partial')
				  AND i.sync_attempts < ?
				  AND i.id > ?
				ORDER BY i.id
				LIMIT " . $this->batchSize();
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([$this->maxAttempts(), $lastId]);
			$rows = $stmt->fetchAll();
			if (!$rows) {
				break;
			}
			foreach ($rows as $row) {
				$lastId = max($lastId, (int)$row['id']);
				if (!$this->claim('invoices', $row['id'])) {
					$this->stats['skipped']++;
					continue;
				}
				$row = $this->rowById('invoices', $row['id']);
				saris_cli_log("  invoice id={$row['id']} ref={$row['invoice_reference_number']}...");
				try {
					if (!empty($row['zerp_invoice_no'])) {
						$this->markSynced('invoices', $row['id']);
						$this->stats['invoices_synced']++;
						continue;
					}
					if ((float)$row['invoice_amount'] <= 0) {
						throw new InvalidArgumentException('Invoice amount must be greater than zero.');
					}
					$student = $this->studentByRegistration($row['student_regnumber']);
					$remoteReference = self::remoteReference($row['invoice_reference_number']);
					$this->currentMethod = 'weberp.xmlrpc_InsertSalesInvoice';
					$result = $this->client->insertSalesInvoice(
						$this->invoicePayload($row, $student, $remoteReference)
					);
					$invoiceNo = $this->remoteNumber($result, 'invoice');
					$stmtUpdate = $this->pdo->prepare(
						"UPDATE invoices
						SET zerp_invoice_no = ?, zerp_invoice_reference = ?, sync_status = 'synced',
							sync_error = NULL, sync_locked_at = NULL, synced_at = NOW()
						WHERE id = ?"
					);
					$stmtUpdate->execute([$invoiceNo, $remoteReference, $row['id']]);
					$this->stats['invoices_synced']++;
					saris_cli_log("    synced (ZERP invoice {$invoiceNo})");
				} catch (Throwable $exception) {
					$this->markError('invoices', $row, 'failed', $exception);
					saris_cli_log('    failed: ' . $exception->getMessage());
				}
			}
		}
	}

	private function syncPayments() {
		$lastId = 0;
		while (true) {
			$sql = "SELECT p.*
			FROM payments p
			INNER JOIN students s ON s.student_regnumber = p.student_regnumber
			WHERE s.sync_status = 'synced'
			  AND p.sync_status IN ('pending', 'failed', 'partial')
			  AND p.sync_attempts < ?
			  AND p.id > ?
			  AND EXISTS (
				SELECT 1
				FROM invoices i
				WHERE i.student_regnumber = p.student_regnumber
				  AND i.sync_status = 'synced'
				  AND i.zerp_invoice_no IS NOT NULL
				  AND i.zerp_invoice_reference IS NOT NULL
				  AND (
					i.invoice_reference_number = p.payment_reference_number
					OR i.invoice_reference_number = p.payment_transaction_ref
				  )
			  )
			ORDER BY p.id
			LIMIT " . $this->batchSize();
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([$this->maxAttempts(), $lastId]);
			$rows = $stmt->fetchAll();
			if (!$rows) {
				break;
			}
			foreach ($rows as $row) {
				$lastId = max($lastId, (int)$row['id']);
				if (!$this->claim('payments', $row['id'])) {
					$this->stats['skipped']++;
					continue;
				}
				$row = $this->rowById('payments', $row['id']);
				saris_cli_log("  payment id={$row['id']} ref={$row['payment_reference_number']}...");
				try {
					if ((float)$row['payment_amount'] <= 0) {
						throw new InvalidArgumentException('Payment amount must be greater than zero.');
					}
					$student = $this->studentByRegistration($row['student_regnumber']);
					$invoice = $this->matchingInvoice($row);

					if ($invoice === null || empty($invoice['zerp_invoice_no']) || empty($invoice['zerp_invoice_reference'])) {
						throw new LogicException('The eligible payment query returned a payment without a synchronized invoice match.');
					}

					if (empty($row['zerp_receipt_no'])) {
						$this->currentMethod = 'weberp.xmlrpc_InsertDebtorReceipt';
						$result = $this->client->insertDebtorReceipt($this->receiptPayload($row, $student));
						$receiptNo = $this->remoteNumber($result, 'receipt');
						$stmtReceipt = $this->pdo->prepare(
							"UPDATE payments
							SET zerp_receipt_no = ?, sync_status = 'partial', sync_error = NULL,
								sync_locked_at = NULL
							WHERE id = ?"
						);
						$stmtReceipt->execute([$receiptNo, $row['id']]);
						$row['zerp_receipt_no'] = $receiptNo;
					}

					if (empty($row['allocation_synced_at'])) {
						$this->currentMethod = 'weberp.xmlrpc_AllocateTrans';
						$this->client->allocateTransaction([
							'debtorno' => $student['zerp_customer_code'],
							'type' => 12,
							'transno' => (int)$row['zerp_receipt_no'],
							'customerref' => $invoice['zerp_invoice_reference'],
						]);
					}

					$stmtDone = $this->pdo->prepare(
						"UPDATE payments
						SET zerp_invoice_no = ?, allocation_synced_at = COALESCE(allocation_synced_at, NOW()),
							sync_status = 'synced', sync_error = NULL, sync_locked_at = NULL, synced_at = NOW()
						WHERE id = ?"
					);
					$stmtDone->execute([$invoice['zerp_invoice_no'], $row['id']]);
					$this->stats['payments_synced']++;
					saris_cli_log('    synced');
				} catch (Throwable $exception) {
					$fresh = $this->rowById('payments', $row['id']);
					$status = !empty($fresh['zerp_receipt_no']) ? 'partial' : 'failed';
					$this->markError('payments', $row, $status, $exception);
					saris_cli_log("    {$status}: " . $exception->getMessage());
				}
			}
		}
	}

	private function customerPayload(array $student, $code) {
		return [
			'debtorno' => $code,
			'name' => $this->limit($student['student_fullname'], 40),
			'currcode' => (string)$this->config['currency'],
			'salestype' => (string)$this->config['sales_type'],
			'paymentterms' => (string)$this->config['payment_terms'],
			'holdreason' => (int)$this->config['hold_reason'],
			'typeid' => (int)$this->config['customer_type'],
			'clientsince' => date('Y-m-d'),
		];
	}

	private function branchPayload(array $student, $code, $branchCode) {
		return [
			'debtorno' => $code,
			'branchcode' => $branchCode,
			'brname' => $this->limit($student['student_fullname'], 40),
			'braddress1' => $this->limit($student['student_programme'], 40),
			'braddress2' => $this->limit($student['student_intake'], 40),
			'area' => (string)$this->config['branch_area'],
			'salesman' => (string)$this->config['salesperson'],
			'phoneno' => $this->limit($student['student_phone'], 20),
			'contactname' => $this->limit($student['student_fullname'], 30),
			'email' => $this->limit($student['student_email'], 55),
			'defaultlocation' => (string)$this->config['default_location'],
			'taxgroupid' => (int)$this->config['tax_group'],
			'defaultshipvia' => (int)$this->config['ship_via'],
			'deliverblind' => 1,
			'disabletrans' => 0,
		];
	}

	private function invoicePayload(array $invoice, array $student, $remoteReference) {
		$date = $this->dateOnly($invoice['invoice_date']);
		return [
			'partcode' => (string)$this->config['invoice_part_code'],
			'salesarea' => (string)$this->config['invoice_location'],
			'debtorno' => $student['zerp_customer_code'],
			'branchcode' => (string)$this->config['branch_code'],
			'trandate' => $date,
			'inputdate' => date('Y-m-d H:i:s'),
			'settled' => 0,
			'reference' => $remoteReference,
			'tpe' => (string)$this->config['sales_type'],
			'rate' => (float)$this->config['invoice_rate'],
			'ovamount' => (float)$invoice['invoice_amount'],
			'ovgst' => 0,
			'ovfreight' => 0,
			'ovdiscount' => 0,
			'diffonexch' => 0,
			'alloc' => 0,
			'invtext' => $this->limit($invoice['invoice_desciption'], 255),
			'shipvia' => (int)$this->config['ship_via'],
			'edisent' => 0,
			'consignment' => '',
			'salesperson' => (string)$this->config['salesperson'],
			'jobref' => '',
		];
	}

	private function getBankAccountForPayment(array $payment) {
		if ($this->bankMappings === null) {
			try {
				$stmt = $this->pdo->query("SELECT match_keyword, bank_account_code FROM saris_bank_mappings");
				$this->bankMappings = $stmt ? ($stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: []) : [];
			} catch (Throwable $e) {
				// Table might not exist yet, fallback to empty mappings
				$this->bankMappings = [];
			}
		}

		$source = trim((string)($payment['payment_source'] ?? ''));
		if ($source !== '' && isset($this->bankMappings[$source])) {
			return $this->bankMappings[$source];
		}

		$amountType = trim((string)($payment['payment_amount_type'] ?? ''));
		if ($amountType !== '' && isset($this->bankMappings[$amountType])) {
			return $this->bankMappings[$amountType];
		}

		return (string)$this->config['bank_account'];
	}

	private function receiptPayload(array $payment, array $student) {
		$reference = $payment['payment_receipt_number']
			?: ($payment['payment_transaction_ref'] ?: ('SARIS-PAY-' . $payment['id']));
		return [
			'debtorno' => $student['zerp_customer_code'],
			'branchcode' => (string)$this->config['branch_code'],
			'trandate' => $this->dateOnly($payment['payment_date']),
			'amountfx' => (float)$payment['payment_amount'],
			'paymentmethod' => (string)$this->config['payment_method'],
			'bankaccount' => $this->getBankAccountForPayment($payment),
			'reference' => self::remoteReference($reference),
			'discountfx' => 0,
		];
	}

	private function matchingInvoice(array $payment) {
		$candidates = array_unique(array_filter([
			trim((string)$payment['payment_reference_number']),
			trim((string)$payment['payment_transaction_ref']),
		]));
		if (!$candidates) {
			return null;
		}
		$placeholders = implode(',', array_fill(0, count($candidates), '?'));
		$params = array_merge([$payment['student_regnumber']], array_values($candidates));
		$stmt = $this->pdo->prepare(
			"SELECT * FROM invoices
			WHERE student_regnumber = ?
			  AND invoice_reference_number IN (" . $placeholders . ")
			  AND sync_status = 'synced'
			ORDER BY id DESC
			LIMIT 1"
		);
		$stmt->execute($params);
		$row = $stmt->fetch();
		return $row ?: null;
	}

	private function eligibleRows($table, $afterId = 0) {
		$sql = "SELECT * FROM `" . $table . "`
			WHERE sync_status IN ('pending', 'failed', 'partial')
			  AND sync_attempts < ?
			  AND id > ?
			ORDER BY id
			LIMIT " . $this->batchSize();
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([$this->maxAttempts(), (int)$afterId]);
		return $stmt->fetchAll();
	}

	private function claim($table, $id) {
		$stmt = $this->pdo->prepare(
			"UPDATE `" . $table . "`
			SET sync_status = 'processing', sync_attempts = sync_attempts + 1,
				sync_locked_at = NOW(), sync_error = NULL
			WHERE id = ?
			  AND sync_status IN ('pending', 'failed', 'partial')
			  AND sync_attempts < ?"
		);
		$stmt->execute([(int)$id, $this->maxAttempts()]);
		return $stmt->rowCount() === 1;
	}

	private function markSynced($table, $id) {
		$stmt = $this->pdo->prepare(
			"UPDATE `" . $table . "`
			SET sync_status = 'synced', sync_error = NULL, sync_locked_at = NULL, synced_at = NOW()
			WHERE id = ?"
		);
		$stmt->execute([(int)$id]);
	}

	private function markError($table, array $row, $status, Throwable $exception) {
		$message = $this->sanitizeError($exception->getMessage());
		$stmt = $this->pdo->prepare(
			"UPDATE `" . $table . "`
			SET sync_status = ?, sync_error = ?, sync_locked_at = NULL
			WHERE id = ?"
		);
		$stmt->execute([$status, $message, $row['id']]);
		$this->stats[$status === 'partial' ? 'partial' : 'failed']++;
		$this->log(
			rtrim($table, 's'),
			$row['id'],
			$this->sourceReference($table, $row),
			$status,
			(int)($row['sync_attempts'] ?? 0),
			$exception
		);
	}

	private function updateStudentCustomer($id, $code) {
		$stmt = $this->pdo->prepare(
			"UPDATE students
			SET zerp_customer_code = ?, customer_synced_at = COALESCE(customer_synced_at, NOW()),
				sync_status = 'partial', sync_error = NULL, sync_locked_at = NULL
			WHERE id = ?"
		);
		$stmt->execute([$code, $id]);
	}

	private function updateStudentBranch($id) {
		$stmt = $this->pdo->prepare(
			"UPDATE students
			SET branch_synced_at = COALESCE(branch_synced_at, NOW()), sync_status = 'partial',
				sync_error = NULL, sync_locked_at = NULL
			WHERE id = ?"
		);
		$stmt->execute([$id]);
	}

	private function rowById($table, $id) {
		$stmt = $this->pdo->prepare("SELECT * FROM `" . $table . "` WHERE id = ?");
		$stmt->execute([(int)$id]);
		$row = $stmt->fetch();
		if (!$row) {
			throw new RuntimeException('Local ' . $table . ' record ' . $id . ' no longer exists.');
		}
		return $row;
	}

	private function studentByRegistration($registration) {
		$stmt = $this->pdo->prepare(
			"SELECT * FROM students WHERE student_regnumber = ? AND sync_status = 'synced' LIMIT 1"
		);
		$stmt->execute([$registration]);
		$row = $stmt->fetch();
		if (!$row || empty($row['zerp_customer_code'])) {
			throw new RuntimeException('The linked student has not been synchronized to ZERP.');
		}
		return $row;
	}

	private function remoteNumber(array $result, $type) {
		if (!isset($result[1]) || !is_numeric($result[1])) {
			throw new RuntimeException('ZERP did not return a valid ' . $type . ' number.');
		}
		return (int)$result[1];
	}

	private function acquireLock() {
		$stmt = $this->pdo->prepare("SELECT GET_LOCK(?, 0)");
		$stmt->execute(['saris_to_zerp_sync']);
		return (int)$stmt->fetchColumn() === 1;
	}

	private function releaseLock() {
		$stmt = $this->pdo->prepare("SELECT RELEASE_LOCK(?)");
		$stmt->execute(['saris_to_zerp_sync']);
	}

	private function resetStaleClaims() {
		foreach (['students', 'invoices', 'payments'] as $table) {
			$this->pdo->exec(
				"UPDATE `" . $table . "`
				SET sync_status = 'failed', sync_error = 'Previous synchronization attempt was interrupted.',
					sync_locked_at = NULL
				WHERE sync_status = 'processing'
				  AND sync_locked_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)"
			);
		}
	}

	private function refreshPaymentWaitingStates() {
		$matchCondition = "EXISTS (
			SELECT 1
			FROM invoices i
			WHERE i.student_regnumber = payments.student_regnumber
			  AND i.sync_status = 'synced'
			  AND i.zerp_invoice_no IS NOT NULL
			  AND i.zerp_invoice_reference IS NOT NULL
			  AND (
				i.invoice_reference_number = payments.payment_reference_number
				OR i.invoice_reference_number = payments.payment_transaction_ref
			  )
		)";

		$this->pdo->exec(
			"UPDATE payments
			SET sync_status = 'pending',
				sync_attempts = 0,
				sync_error = 'Waiting for a synchronized invoice reference match.',
				sync_locked_at = NULL
			WHERE allocation_synced_at IS NULL
			  AND zerp_receipt_no IS NULL
			  AND sync_status <> 'processing'
			  AND NOT " . $matchCondition
		);

		$this->pdo->exec(
			"UPDATE payments
			SET sync_status = 'partial',
				sync_attempts = 0,
				sync_error = 'Receipt already posted; waiting for a synchronized invoice reference match.',
				sync_locked_at = NULL
			WHERE allocation_synced_at IS NULL
			  AND zerp_receipt_no IS NOT NULL
			  AND sync_status <> 'processing'
			  AND NOT " . $matchCondition
		);
	}

	private function errorSummary() {
		$stmt = $this->pdo->prepare(
			"SELECT record_type, message, COUNT(*) AS total
			FROM zerp_sync_log
			WHERE run_id = ?
			  AND sync_status IN ('partial', 'failed')
			GROUP BY record_type, message
			ORDER BY total DESC
			LIMIT 5"
		);
		$stmt->execute([$this->runId]);
		$summary = [];
		foreach ($stmt->fetchAll() as $row) {
			$summary[] = [
				'record_type' => $row['record_type'],
				'count' => (int)$row['total'],
				'message' => $this->sanitizeError($row['message']),
			];
		}
		return $summary;
	}

	private function log($type, $recordId, $reference, $status, $attempt, Throwable $exception) {
		$faultCode = $exception instanceof ZERPXMLRPCException
			? $exception->getFaultCodeValue()
			: null;
		$stmt = $this->pdo->prepare(
			"INSERT INTO zerp_sync_log
				(run_id, record_type, record_id, source_reference, xmlrpc_method,
				 attempt_number, sync_status, fault_code, message)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
		);
		$stmt->execute([
			$this->runId,
			$type,
			$recordId,
			$this->limit($reference, 100),
			$this->limit($this->currentMethod, 100),
			$attempt,
			$status,
			$faultCode,
			$this->sanitizeError($exception->getMessage()),
		]);
	}

	private function sourceReference($table, array $row) {
		if ($table === 'students') {
			return $row['student_regnumber'];
		}
		if ($table === 'invoices') {
			return $row['invoice_reference_number'];
		}
		return $row['payment_receipt_number'] ?: $row['payment_transaction_ref'];
	}

	private function dateOnly($value) {
		$timestamp = strtotime((string)$value);
		if ($timestamp === false) {
			throw new InvalidArgumentException('Invalid transaction date.');
		}
		return date('Y-m-d', $timestamp);
	}

	private function limit($value, $length) {
		return mb_substr(trim((string)$value), 0, $length);
	}

	private function sanitizeError($message) {
		$message = preg_replace('/(password|secret|token)\\s*[=:]\\s*[^\\s,;]+/i', '$1=[redacted]', (string)$message);
		return $this->limit($message, 2000);
	}

	private function batchSize() {
		return max(1, min(1000, (int)($this->config['batch_size'] ?? 100)));
	}

	private function maxAttempts() {
		return max(1, (int)($this->config['max_attempts'] ?? 5));
	}

	private function uuid() {
		$bytes = random_bytes(16);
		$bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
		$bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
		$hex = bin2hex($bytes);
		return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
			. '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
	}
}

?>
