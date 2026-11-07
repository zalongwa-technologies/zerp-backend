<?php

declare(strict_types=1);

final class PayablesApiException extends RuntimeException
{
	public function __construct(
		public readonly int $status,
		public readonly string $errorCode,
		string $message
	) {
		parent::__construct($message);
	}
}

final class PayablesRequest
{
	private const ALLOWED_TYPES = [20, 21, 22, 23];

	public static function authenticate(?string $authorization, mixed $configuredToken): void
	{
		if (!is_string($configuredToken) || $configuredToken === '') {
			throw new PayablesApiException(500, 'API_NOT_CONFIGURED', 'The payables API is not configured');
		}
		if (!is_string($authorization)
			|| !preg_match('/^Bearer[ ]+(.+)$/i', trim($authorization), $matches)
			|| !hash_equals($configuredToken, trim($matches[1]))) {
			throw new PayablesApiException(401, 'UNAUTHORIZED', 'A valid bearer token is required');
		}
	}

	public static function parse(array $query): array
	{
		$allowed = ['supplier_id', 'from_date', 'to_date', 'transaction_type', 'include_void', 'include_settled', 'page', 'page_size'];
		foreach (array_keys($query) as $key) {
			if (!in_array($key, $allowed, true)) {
				throw new PayablesApiException(400, 'UNKNOWN_PARAMETER', 'Unknown query parameter: ' . $key);
			}
			if (is_array($query[$key])) {
				throw new PayablesApiException(400, 'INVALID_PARAMETER', $key . ' must have one value');
			}
		}

		$supplierId = isset($query['supplier_id']) ? trim((string)$query['supplier_id']) : null;
		if ($supplierId === '') {
			throw new PayablesApiException(400, 'INVALID_SUPPLIER_ID', 'supplier_id cannot be empty');
		}
		if ($supplierId !== null && !preg_match('/^[A-Za-z0-9_.-]{1,10}$/', $supplierId)) {
			throw new PayablesApiException(400, 'INVALID_SUPPLIER_ID', 'supplier_id contains invalid characters or exceeds 10 characters');
		}

		$fromDate = self::date($query, 'from_date');
		$toDate = self::date($query, 'to_date');
		if ($fromDate !== null && $toDate !== null && $fromDate > $toDate) {
			throw new PayablesApiException(400, 'INVALID_DATE_RANGE', 'from_date must not be later than to_date');
		}

		$types = self::types($query['transaction_type'] ?? null);
		$page = self::positiveInteger($query['page'] ?? '1', 'page');
		$pageSize = self::positiveInteger($query['page_size'] ?? '100', 'page_size');
		if ($pageSize > 500) {
			throw new PayablesApiException(400, 'INVALID_PAGE_SIZE', 'page_size must not exceed 500');
		}
		if ($page > intdiv(PHP_INT_MAX, $pageSize)) {
			throw new PayablesApiException(400, 'INVALID_PAGINATION', 'page is too large');
		}

		return [
			'supplier_id' => $supplierId,
			'from_date' => $fromDate,
			'to_date' => $toDate,
			'transaction_types' => $types,
			'include_void' => self::boolean($query['include_void'] ?? null, false, 'include_void'),
			'include_settled' => self::boolean($query['include_settled'] ?? null, true, 'include_settled'),
			'page' => $page,
			'page_size' => $pageSize,
		];
	}

	private static function date(array $query, string $name): ?string
	{
		if (!isset($query[$name])) {
			return null;
		}
		$value = (string)$query[$name];
		$date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
		$errors = DateTimeImmutable::getLastErrors();
		if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $value) {
			throw new PayablesApiException(400, 'INVALID_DATE', $name . ' must use YYYY-MM-DD format');
		}
		return $value;
	}

	private static function types(mixed $value): array
	{
		if ($value === null || $value === '') {
			return self::ALLOWED_TYPES;
		}
		$parts = explode(',', (string)$value);
		$types = [];
		foreach ($parts as $part) {
			if (!preg_match('/^[0-9]+$/', $part)) {
				throw new PayablesApiException(400, 'INVALID_TRANSACTION_TYPE', 'transaction_type must be a comma-separated list of supported integers');
			}
			$type = (int)$part;
			if (!in_array($type, self::ALLOWED_TYPES, true)) {
				throw new PayablesApiException(400, 'INVALID_TRANSACTION_TYPE', 'Supported transaction types are 20, 21, 22 and 23');
			}
			$types[$type] = $type;
		}
		return array_values($types);
	}

	private static function boolean(mixed $value, bool $default, string $name): bool
	{
		if ($value === null) {
			return $default;
		}
		$parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		if ($parsed === null) {
			throw new PayablesApiException(400, 'INVALID_BOOLEAN', $name . ' must be true or false');
		}
		return $parsed;
	}

	private static function positiveInteger(mixed $value, string $name): int
	{
		if (!is_string($value) && !is_int($value)) {
			throw new PayablesApiException(400, 'INVALID_PAGINATION', $name . ' must be a positive integer');
		}
		$value = (string)$value;
		if (!preg_match('/^[1-9][0-9]*$/', $value) || (float)$value > PHP_INT_MAX) {
			throw new PayablesApiException(400, 'INVALID_PAGINATION', $name . ' must be a positive integer');
		}
		return (int)$value;
	}
}

final class PayablesRepository
{
	public function __construct(private readonly mysqli $db)
	{
	}

	public function supplierExists(string $supplierId): bool
	{
		$rows = $this->select('SELECT 1 FROM suppliers WHERE supplierid = ? LIMIT 1', 's', [$supplierId]);
		return $rows !== [];
	}

	public function export(array $filters): array
	{
		[$where, $types, $params] = $this->filters($filters);
		$count = $this->selectOne('SELECT COUNT(*) AS total FROM supptrans st WHERE ' . $where, $types, $params);
		$total = (int)$count['total'];
		$offset = ($filters['page'] - 1) * $filters['page_size'];

		$sql = "SELECT st.id, st.transno, st.type, sy.typename, st.supplierno, st.suppreference,
				st.trandate, st.duedate, st.inputdate, st.rate, st.ovamount, st.ovgst, st.alloc,
				st.settled, st.hold, st.void, st.transtext, st.chequeno,
				s.suppname, s.address1, s.address2, s.address3, s.address4, s.address5, s.address6,
				s.currcode, s.taxgroupid, s.taxref, s.phn, s.port, s.email, s.fax, s.telephone, s.url,
				c.decimalplaces, pt.terms
			FROM supptrans st
			INNER JOIN suppliers s ON s.supplierid = st.supplierno
			INNER JOIN systypes sy ON sy.typeid = st.type
			INNER JOIN currencies c ON c.currabrev = s.currcode
			INNER JOIN paymentterms pt ON pt.termsindicator = s.paymentterms
			WHERE $where
			ORDER BY st.inputdate ASC, st.id ASC
			LIMIT ? OFFSET ?";
		$pageParams = array_merge($params, [$filters['page_size'], $offset]);
		$rows = $this->select($sql, $types . 'ii', $pageParams);
		if ($rows === []) {
			return ['total' => $total, 'data' => []];
		}

		$ids = array_map(static fn(array $row): int => (int)$row['id'], $rows);
		$taxes = $this->taxes($ids);
		$lines = $this->accountingLines($rows);
		$allocations = $this->allocations($ids);
		$contacts = $this->contacts(array_values(array_unique(array_column($rows, 'supplierno'))));
		$data = [];
		foreach ($rows as $row) {
			$id = (int)$row['id'];
			$places = (int)$row['decimalplaces'];
			$gross = (float)$row['ovamount'] + (float)$row['ovgst'];
			$data[] = [
				'external_id' => 'zerp-supptrans-' . $id,
				'transaction_id' => $id,
				'transaction_number' => (int)$row['transno'],
				'transaction_type' => (int)$row['type'],
				'transaction_type_name' => $row['typename'],
				'supplier' => $this->supplier($row, $contacts[$row['supplierno']] ?? []),
				'supplier_reference' => $row['suppreference'],
				'transaction_date' => $this->validDate($row['trandate']),
				'due_date' => $this->validDate($row['duedate']),
				'input_date' => $this->dateTime($row['inputdate']),
				'currency' => $row['currcode'],
				'exchange_rate' => (float)$row['rate'],
				'net_amount' => round((float)$row['ovamount'], $places),
				'tax_amount' => round((float)$row['ovgst'], $places),
				'gross_amount' => round($gross, $places),
				'allocated_amount' => round((float)$row['alloc'], $places),
				'outstanding_amount' => round($gross - (float)$row['alloc'], $places),
				'settled' => (bool)$row['settled'],
				'on_hold' => (bool)$row['hold'],
				'void' => (bool)$row['void'],
				'narrative' => $row['transtext'] ?? '',
				'cheque_number' => $row['chequeno'],
				'taxes' => array_map(static function (array $tax) use ($places): array {
					$tax['amount'] = round($tax['amount'], $places);
					return $tax;
				}, $taxes[$id] ?? []),
				'accounting_lines' => $lines[$id] ?? [],
				'allocations' => array_map(static function (array $allocation) use ($places): array {
					$allocation['allocated_amount'] = round($allocation['allocated_amount'], $places);
					return $allocation;
				}, $allocations[$id] ?? []),
			];
		}
		return ['total' => $total, 'data' => $data];
	}

	private function filters(array $filters): array
	{
		$clauses = ['st.type IN (' . implode(',', array_fill(0, count($filters['transaction_types']), '?')) . ')'];
		$types = str_repeat('i', count($filters['transaction_types']));
		$params = $filters['transaction_types'];
		if ($filters['supplier_id'] !== null) {
			$clauses[] = 'st.supplierno = ?'; $types .= 's'; $params[] = $filters['supplier_id'];
		}
		if ($filters['from_date'] !== null) {
			$clauses[] = 'st.trandate >= ?'; $types .= 's'; $params[] = $filters['from_date'];
		}
		if ($filters['to_date'] !== null) {
			$clauses[] = 'st.trandate <= ?'; $types .= 's'; $params[] = $filters['to_date'];
		}
		if (!$filters['include_void']) {
			$clauses[] = 'st.void = 0';
		}
		if (!$filters['include_settled']) {
			$clauses[] = 'st.settled = 0';
		}
		return [implode(' AND ', $clauses), $types, $params];
	}

	private function taxes(array $ids): array
	{
		[$in, $types] = $this->in($ids);
		$rows = $this->select("SELECT tx.supptransid, tx.taxauthid, ta.description, tx.taxamount
			FROM supptranstaxes tx INNER JOIN taxauthorities ta ON ta.taxid = tx.taxauthid
			WHERE tx.supptransid IN ($in) ORDER BY tx.supptransid, tx.taxauthid", $types, $ids);
		$out = [];
		foreach ($rows as $row) {
			$out[(int)$row['supptransid']][] = [
				'external_id' => 'zerp-supptrans-tax-' . $row['supptransid'] . '-' . $row['taxauthid'],
				'tax_authority_id' => (int)$row['taxauthid'],
				'description' => $row['description'],
				'amount' => (float)$row['taxamount'],
			];
		}
		return $out;
	}

	private function accountingLines(array $transactions): array
	{
		$conditions = []; $params = []; $types = '';
		$keyToId = [];
		foreach ($transactions as $row) {
			$keyToId[$row['type'] . ':' . $row['transno']] = (int)$row['id'];
			$conditions[] = '(g.type = ? AND g.typeno = ?)';
			$types .= 'ii'; $params[] = (int)$row['type']; $params[] = (int)$row['transno'];
		}
		$rows = $this->select('SELECT g.counterindex, g.type, g.typeno, g.account, g.narrative, g.amount, g.periodno, g.jobref FROM gltrans g WHERE ' . implode(' OR ', $conditions) . ' ORDER BY g.counterindex', $types, $params);
		$out = [];
		foreach ($rows as $row) {
			$id = $keyToId[$row['type'] . ':' . $row['typeno']];
			$out[$id][] = ['entry_id' => (int)$row['counterindex'], 'account_code' => $row['account'], 'narrative' => $row['narrative'], 'amount' => (float)$row['amount'], 'period_number' => (int)$row['periodno'], 'job_reference' => $row['jobref'] !== '' ? $row['jobref'] : null, 'tag' => null];
		}
		return $out;
	}

	private function allocations(array $ids): array
	{
		[$in, $types] = $this->in($ids);
		$sql = "SELECT a.id, a.amt, a.datealloc,
			f.id source_id, f.transno source_no, f.type source_type, fs.typename source_name, f.suppreference source_ref,
			t.id target_id, t.transno target_no, t.type target_type, ts.typename target_name, t.suppreference target_ref
			FROM suppallocs a
			INNER JOIN supptrans f ON f.id = a.transid_allocfrom INNER JOIN systypes fs ON fs.typeid = f.type
			INNER JOIN supptrans t ON t.id = a.transid_allocto INNER JOIN systypes ts ON ts.typeid = t.type
			WHERE a.transid_allocfrom IN ($in) OR a.transid_allocto IN ($in)
			ORDER BY a.id";
		$rows = $this->select($sql, $types . $types, array_merge($ids, $ids));
		$out = [];
		foreach ($rows as $row) {
			$allocation = [
				'external_id' => 'zerp-suppalloc-' . $row['id'], 'allocation_id' => (int)$row['id'],
				'allocation_date' => $this->validDate($row['datealloc']), 'allocated_amount' => (float)$row['amt'],
				'source_transaction' => $this->allocationTransaction($row, 'source'),
				'target_transaction' => $this->allocationTransaction($row, 'target'),
			];
			if (in_array((int)$row['source_id'], $ids, true)) $out[(int)$row['source_id']][] = $allocation;
			if ((int)$row['target_id'] !== (int)$row['source_id'] && in_array((int)$row['target_id'], $ids, true)) $out[(int)$row['target_id']][] = $allocation;
		}
		return $out;
	}

	private function contacts(array $supplierIds): array
	{
		[$in, $types] = $this->in($supplierIds, 's');
		$rows = $this->select("SELECT supplierid, contact, position, tel, fax, mobile, email, ordercontact FROM suppliercontacts WHERE supplierid IN ($in) ORDER BY supplierid, ordercontact DESC, contact", $types, $supplierIds);
		$out = [];
		foreach ($rows as $row) {
			$id = $row['supplierid']; unset($row['supplierid']); $row['order_contact'] = (bool)$row['ordercontact']; unset($row['ordercontact']); $out[$id][] = $row;
		}
		return $out;
	}

	private function supplier(array $row, array $contacts): array
	{
		return [
			'external_id' => 'zerp-supplier-' . $row['supplierno'], 'supplier_id' => $row['supplierno'], 'name' => $row['suppname'],
			'currency' => $row['currcode'], 'payment_terms' => $row['terms'], 'tax_group_id' => (int)$row['taxgroupid'], 'tax_reference' => $row['taxref'],
			'address' => ['line1' => $row['address1'], 'line2' => $row['address2'], 'line3' => $row['address3'], 'line4' => $row['address4'], 'line5' => $row['address5'], 'line6' => $row['address6']],
			'contact' => ['phone' => $row['telephone'], 'alternate_phone' => $row['phn'], 'email' => $row['email'], 'fax' => $row['fax'], 'website' => $row['url'], 'port' => $row['port'], 'people' => $contacts],
		];
	}

	private function allocationTransaction(array $row, string $prefix): array
	{
		$id = (int)$row[$prefix . '_id'];
		return ['external_id' => 'zerp-supptrans-' . $id, 'transaction_id' => $id, 'transaction_number' => (int)$row[$prefix . '_no'], 'transaction_type' => (int)$row[$prefix . '_type'], 'transaction_type_name' => $row[$prefix . '_name'], 'reference' => $row[$prefix . '_ref']];
	}

	private function selectOne(string $sql, string $types = '', array $params = []): array
	{
		$rows = $this->select($sql, $types, $params);
		if ($rows === []) throw new RuntimeException('Expected one database row');
		return $rows[0];
	}

	private function select(string $sql, string $types = '', array $params = []): array
	{
		$stmt = $this->db->prepare($sql);
		if ($stmt === false) throw new RuntimeException('Unable to prepare database query');
		try {
			if ($params !== [] && !$stmt->bind_param($types, ...$params)) throw new RuntimeException('Unable to bind database query');
			if (!$stmt->execute()) throw new RuntimeException('Unable to execute database query');
			$result = $stmt->get_result();
			if ($result === false) throw new RuntimeException('Unable to retrieve database result');
			return $result->fetch_all(MYSQLI_ASSOC);
		} finally {
			$stmt->close();
		}
	}

	private function in(array $values, string $type = 'i'): array
	{
		return [implode(',', array_fill(0, count($values), '?')), str_repeat($type, count($values))];
	}

	private function validDate(?string $value): ?string
	{
		return $value === null || $value === '1000-01-01' ? null : $value;
	}

	private function dateTime(?string $value): ?string
	{
		if ($value === null || str_starts_with($value, '1000-01-01')) return null;
		return (new DateTimeImmutable($value))->format(DateTimeInterface::ATOM);
	}
}
