<?php

declare(strict_types=1);

final class ReceivablesApiException extends RuntimeException
{
	public function __construct(
		public readonly int $status,
		public readonly string $errorCode,
		string $message
	) {
		parent::__construct($message);
	}
}

final class ReceivablesRequest
{
	private const RESOURCES = ['customers', 'items', 'invoices', 'invoice_lines', 'payments', 'payment_allocations'];

	public static function authenticate(?string $authorization, mixed $configuredToken): void
	{
		if (!is_string($configuredToken) || $configuredToken === '') {
			throw new ReceivablesApiException(500, 'API_NOT_CONFIGURED', 'The receivables API is not configured');
		}
		if (!is_string($authorization)
			|| !preg_match('/^Bearer[ ]+(.+)$/i', trim($authorization), $matches)
			|| !hash_equals($configuredToken, trim($matches[1]))) {
			throw new ReceivablesApiException(401, 'UNAUTHORIZED', 'A valid bearer token is required');
		}
	}

	public static function parse(array $query): array
	{
		$allowed = [
			'resource', 'updated_since', 'updated_until', 'page', 'page_size', 'customer_id',
			'invoice_id', 'item_id', 'stock_id', 'branch_id', 'status', 'from_date', 'to_date',
			'include_voided', 'include_lines',
		];
		foreach (array_keys($query) as $key) {
			if (!in_array($key, $allowed, true)) {
				throw new ReceivablesApiException(400, 'UNKNOWN_PARAMETER', 'Unknown query parameter: ' . $key);
			}
			if (is_array($query[$key])) {
				throw new ReceivablesApiException(400, 'INVALID_PARAMETER', $key . ' must have one value');
			}
		}

		$resource = trim((string)($query['resource'] ?? ''));
		if (!in_array($resource, self::RESOURCES, true)) {
			throw new ReceivablesApiException(400, 'INVALID_RESOURCE', 'resource must be one of: ' . implode(', ', self::RESOURCES));
		}
		$customerId = self::identifier($query, 'customer_id', 10);
		$branchId = self::identifier($query, 'branch_id', 10);
		$itemId = self::identifier($query, isset($query['item_id']) ? 'item_id' : 'stock_id', 64);
		$invoiceId = self::positiveInteger($query['invoice_id'] ?? null, 'invoice_id', true);
		$page = self::positiveInteger($query['page'] ?? '1', 'page');
		$pageSize = self::positiveInteger($query['page_size'] ?? '100', 'page_size');
		if ($pageSize > 500) {
			throw new ReceivablesApiException(400, 'INVALID_PAGE_SIZE', 'page_size must not exceed 500');
		}
		if ($page > intdiv(PHP_INT_MAX, $pageSize)) {
			throw new ReceivablesApiException(400, 'INVALID_PAGINATION', 'page is too large');
		}

		$fromDate = self::date($query, 'from_date');
		$toDate = self::date($query, 'to_date');
		if ($fromDate !== null && $toDate !== null && $fromDate > $toDate) {
			throw new ReceivablesApiException(400, 'INVALID_DATE_RANGE', 'from_date must not be later than to_date');
		}
		$updatedSince = self::dateTime($query, 'updated_since');
		$updatedUntil = self::dateTime($query, 'updated_until');
		if ($updatedSince !== null && $updatedUntil !== null && $updatedSince > $updatedUntil) {
			throw new ReceivablesApiException(400, 'INVALID_DATE_RANGE', 'updated_since must be earlier than updated_until');
		}
		$status = isset($query['status']) ? trim((string)$query['status']) : null;
		if ($status !== null && !in_array($status, ['open', 'partially_paid', 'paid', 'voided'], true)) {
			throw new ReceivablesApiException(400, 'INVALID_STATUS', 'status must be open, partially_paid, paid or voided');
		}

		return [
			'resource' => $resource, 'customer_id' => $customerId, 'branch_id' => $branchId,
			'item_id' => $itemId, 'invoice_id' => $invoiceId, 'status' => $status,
			'from_date' => $fromDate, 'to_date' => $toDate,
			'updated_since' => $updatedSince?->format('Y-m-d H:i:s'),
			'updated_until' => $updatedUntil?->format('Y-m-d H:i:s'),
			'include_voided' => self::boolean($query['include_voided'] ?? null, false, 'include_voided'),
			'include_lines' => self::boolean($query['include_lines'] ?? null, false, 'include_lines'),
			'page' => $page, 'page_size' => $pageSize,
		];
	}

	private static function identifier(array $query, string $name, int $maximum): ?string
	{
		if (!isset($query[$name])) return null;
		$value = trim((string)$query[$name]);
		if ($value === '' || strlen($value) > $maximum || !preg_match('/^[A-Za-z0-9_.\/-]+$/', $value)) {
			throw new ReceivablesApiException(400, 'INVALID_IDENTIFIER', $name . ' contains invalid characters or is too long');
		}
		return $value;
	}

	private static function date(array $query, string $name): ?string
	{
		if (!isset($query[$name])) return null;
		$value = (string)$query[$name];
		$date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
		$errors = DateTimeImmutable::getLastErrors();
		if ($date === false || ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) || $date->format('Y-m-d') !== $value) {
			throw new ReceivablesApiException(400, 'INVALID_DATE', $name . ' must use YYYY-MM-DD format');
		}
		return $value;
	}

	private static function dateTime(array $query, string $name): ?DateTimeImmutable
	{
		if (!isset($query[$name])) return null;
		try {
			$date = new DateTimeImmutable((string)$query[$name]);
		} catch (Throwable) {
			throw new ReceivablesApiException(400, 'INVALID_DATETIME', $name . ' must be an ISO 8601 timestamp');
		}
		if (!str_contains((string)$query[$name], 'T')) {
			throw new ReceivablesApiException(400, 'INVALID_DATETIME', $name . ' must be an ISO 8601 timestamp');
		}
		return $date;
	}

	private static function boolean(mixed $value, bool $default, string $name): bool
	{
		if ($value === null) return $default;
		$parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		if ($parsed === null) throw new ReceivablesApiException(400, 'INVALID_BOOLEAN', $name . ' must be true or false');
		return $parsed;
	}

	private static function positiveInteger(mixed $value, string $name, bool $nullable = false): ?int
	{
		if ($nullable && $value === null) return null;
		if ((!is_string($value) && !is_int($value)) || !preg_match('/^[1-9][0-9]*$/', (string)$value) || (float)$value > PHP_INT_MAX) {
			throw new ReceivablesApiException(400, 'INVALID_INTEGER', $name . ' must be a positive integer');
		}
		return (int)$value;
	}
}

final class ReceivablesRepository
{
	public function __construct(private readonly mysqli $db) {}

	public function customerExists(string $id): bool
	{
		return $this->select('SELECT 1 FROM debtorsmaster WHERE debtorno = ? LIMIT 1', 's', [$id]) !== [];
	}

	public function invoiceExists(int $id): bool
	{
		return $this->select('SELECT 1 FROM debtortrans WHERE id = ? AND type IN (10,11) LIMIT 1', 'i', [$id]) !== [];
	}

	public function export(array $filters): array
	{
		return match ($filters['resource']) {
			'customers' => $this->customers($filters),
			'items' => $this->items($filters),
			'invoices' => $this->invoices($filters),
			'invoice_lines' => $this->invoiceLines($filters),
			'payments' => $this->payments($filters),
			'payment_allocations' => $this->allocations($filters),
		};
	}

	private function customers(array $f): array
	{
		[$where, $types, $params] = $this->simpleFilters($f, 'd.debtorno', null, 'd.clientsince');
		$total = $this->count('debtorsmaster d', $where, $types, $params);
		$sql = "SELECT d.*, c.decimalplaces, pt.terms, hr.dissallowinvoices
			FROM debtorsmaster d INNER JOIN currencies c ON c.currabrev=d.currcode
			INNER JOIN paymentterms pt ON pt.termsindicator=d.paymentterms
			INNER JOIN holdreasons hr ON hr.reasoncode=d.holdreason
			WHERE $where ORDER BY d.debtorno LIMIT ? OFFSET ?";
		$rows = $this->page($sql, $types, $params, $f);
		$branches = $this->branches(array_column($rows, 'debtorno'));
		$data = [];
		foreach ($rows as $r) {
			$data[] = [
				'external_id' => 'zerp-customer-' . $r['debtorno'], 'customer_id' => $r['debtorno'],
				'name' => $r['name'], 'currency' => $r['currcode'], 'currency_decimal_places' => (int)$r['decimalplaces'],
				'payment_terms' => $r['terms'], 'credit_limit' => $this->decimal($r['creditlimit']),
				'tax_reference' => $r['taxref'], 'active' => !(bool)$r['dissallowinvoices'],
				'address' => $this->address($r, 'address'), 'branches' => $branches[$r['debtorno']] ?? [],
				'created_at' => $this->dateTime($r['clientsince']), 'updated_at' => null,
			];
		}
		return ['total' => $total, 'data' => $data];
	}

	private function items(array $f): array
	{
		$clauses = ['1=1']; $types = ''; $params = [];
		if ($f['item_id'] !== null) { $clauses[] = 's.stockid=?'; $types .= 's'; $params[] = $f['item_id']; }
		if ($f['updated_since'] !== null) { $clauses[] = 's.lastcostupdate>=?'; $types .= 's'; $params[] = substr($f['updated_since'], 0, 10); }
		if ($f['updated_until'] !== null) { $clauses[] = 's.lastcostupdate<=?'; $types .= 's'; $params[] = substr($f['updated_until'], 0, 10); }
		$where = implode(' AND ', $clauses);
		$total = $this->count('stockmaster s', $where, $types, $params);
		$sql = "SELECT s.stockid,s.description,s.longdescription,s.categoryid,s.units,s.mbflag,s.barcode,s.taxcatid,
			s.discontinued,s.decimalplaces,s.lastcostupdate,sc.categorydescription,sc.stocktype,tc.taxcatname
			FROM stockmaster s INNER JOIN stockcategory sc ON sc.categoryid=s.categoryid
			INNER JOIN taxcategories tc ON tc.taxcatid=s.taxcatid
			WHERE $where ORDER BY s.stockid LIMIT ? OFFSET ?";
		$data = array_map(function (array $r): array {
			return [
				'external_id' => 'zerp-item-' . $r['stockid'], 'item_id' => $r['stockid'], 'stock_code' => $r['stockid'],
				'name' => $r['description'], 'sales_description' => $r['description'], 'long_description' => $r['longdescription'],
				'category' => ['id' => $r['categoryid'], 'name' => trim($r['categorydescription'])],
				'unit_of_measure' => $r['units'], 'product_type' => $this->productType($r['mbflag'], $r['stocktype']),
				'barcode' => $r['barcode'] !== '' ? $r['barcode'] : null,
				'tax_category_id' => (int)$r['taxcatid'],
				'tax_category' => ['id' => (int)$r['taxcatid'], 'name' => $r['taxcatname']],
				'quantity_decimal_places' => (int)$r['decimalplaces'], 'active' => !(bool)$r['discontinued'],
				'created_at' => null, 'updated_at' => $this->dateOnly($r['lastcostupdate']),
			];
		}, $this->page($sql, $types, $params, $f));
		return ['total' => $total, 'data' => $data];
	}

	private function invoices(array $f): array
	{
		[$where, $types, $params] = $this->transactionFilters($f, 'dt', [10, 11]);
		$total = $this->count('debtortrans dt', $where, $types, $params);
		$sql = "SELECT dt.*,sy.typename,d.name customer_name,d.currcode,c.decimalplaces,so.customerref,
			pt.daysbeforedue,pt.dayinfollowingmonth
			FROM debtortrans dt INNER JOIN systypes sy ON sy.typeid=dt.type
			INNER JOIN debtorsmaster d ON d.debtorno=dt.debtorno INNER JOIN currencies c ON c.currabrev=d.currcode
			INNER JOIN paymentterms pt ON pt.termsindicator=d.paymentterms
			LEFT JOIN salesorders so ON so.orderno=dt.order_
			WHERE $where ORDER BY dt.inputdate,dt.id LIMIT ? OFFSET ?";
		$rows = $this->page($sql, $types, $params, $f);
		$taxMap = $this->invoiceTaxes(array_column($rows, 'id'));
		$lineMap = $f['include_lines'] ? $this->linesForTransactions($rows) : [];
		$data = [];
		foreach ($rows as $r) {
			$places = (int)$r['decimalplaces'];
			$totalAmount = (float)$r['ovamount'] + (float)$r['ovgst'] + (float)$r['ovfreight'] + (float)$r['ovdiscount'];
			$item = [
				'external_id' => 'zerp-debtortrans-' . $r['id'], 'transaction_id' => (int)$r['id'],
				'invoice_number' => (int)$r['transno'], 'transaction_type' => (int)$r['type'],
				'transaction_type_name' => trim($r['typename']), 'customer_id' => $r['debtorno'],
				'customer_name' => $r['customer_name'], 'branch_id' => $r['branchcode'],
				'invoice_date' => $this->dateOnly($r['trandate']), 'due_date' => $this->dueDate($r),
				'currency' => $r['currcode'], 'exchange_rate' => $this->decimal($r['rate']),
				'net_amount' => $this->money($r['ovamount'], $places), 'discount_amount' => $this->money($r['ovdiscount'], $places),
				'freight_amount' => $this->money($r['ovfreight'], $places), 'tax_amount' => $this->money($r['ovgst'], $places),
				'taxes' => array_map(function (array $tax) use ($places): array {
					$tax['amount'] = $this->money($tax['amount'], $places);
					return $tax;
				}, $taxMap[(int)$r['id']] ?? []),
				'total_amount' => $this->money($totalAmount, $places), 'allocated_amount' => $this->money($r['alloc'], $places),
				'outstanding_amount' => $this->money($totalAmount - (float)$r['alloc'], $places),
				'status' => $this->status($r, $totalAmount), 'customer_reference' => $r['customerref'],
				'sales_order_id' => (int)$r['order_'] ?: null, 'accounting_period' => (int)$r['prd'],
				'reference' => $r['reference'], 'narrative' => $r['invtext'], 'voided' => false,
				'created_at' => $this->dateTime($r['inputdate']), 'updated_at' => $this->dateTime($r['inputdate']),
			];
			if ($f['include_lines']) $item['lines'] = $lineMap[(int)$r['id']] ?? [];
			$data[] = $item;
		}
		return ['total' => $total, 'data' => $data];
	}

	private function invoiceLines(array $f): array
	{
		$clauses = ['sm.type IN (10,11)', 'dt.type=sm.type', 'dt.transno=sm.transno']; $types = ''; $params = [];
		if (!$f['include_voided']) $clauses[] = 'sm.hidemovt=0';
		if ($f['invoice_id'] !== null) { $clauses[]='dt.id=?'; $types.='i'; $params[]=$f['invoice_id']; }
		if ($f['customer_id'] !== null) { $clauses[]='dt.debtorno=?'; $types.='s'; $params[]=$f['customer_id']; }
		if ($f['branch_id'] !== null) { $clauses[]='dt.branchcode=?'; $types.='s'; $params[]=$f['branch_id']; }
		if ($f['item_id'] !== null) { $clauses[]='sm.stockid=?'; $types.='s'; $params[]=$f['item_id']; }
		$this->dateClauses($f, $clauses, $types, $params, 'dt.trandate', 'dt.inputdate');
		$where = implode(' AND ', $clauses);
		$total = $this->count('stockmoves sm INNER JOIN debtortrans dt ON dt.type=sm.type AND dt.transno=sm.transno', $where, $types, $params);
		$sql = "SELECT sm.*,dt.id debtortransid,dt.debtorno customer_id,dt.inputdate,dt.order_,d.currcode,c.decimalplaces,
			sy.typename,s.description item_name,s.categoryid,s.units,sc.categorydescription
			FROM stockmoves sm INNER JOIN debtortrans dt ON dt.type=sm.type AND dt.transno=sm.transno
			INNER JOIN systypes sy ON sy.typeid=dt.type INNER JOIN debtorsmaster d ON d.debtorno=dt.debtorno
			INNER JOIN currencies c ON c.currabrev=d.currcode LEFT JOIN stockmaster s ON s.stockid=sm.stockid
			LEFT JOIN stockcategory sc ON sc.categoryid=s.categoryid WHERE $where
			ORDER BY dt.inputdate,dt.id,sm.stkmoveno LIMIT ? OFFSET ?";
		$rows = $this->page($sql, $types, $params, $f);
		$taxes = $this->lineTaxes(array_column($rows, 'stkmoveno'));
		$data=[];
		foreach ($rows as $r) $data[]=$this->line($r, $taxes[(int)$r['stkmoveno']] ?? []);
		return ['total'=>$total,'data'=>$data];
	}

	private function payments(array $f): array
	{
		[$where, $types, $params] = $this->transactionFilters($f, 'dt', [12]);
		$total = $this->count('debtortrans dt', $where, $types, $params);
		$sql = "SELECT dt.*,sy.typename,d.name customer_name,d.currcode,c.decimalplaces,bt.banktransid,bt.bankact,bt.banktranstype,bt.chequeno
			FROM debtortrans dt INNER JOIN systypes sy ON sy.typeid=dt.type INNER JOIN debtorsmaster d ON d.debtorno=dt.debtorno
			INNER JOIN currencies c ON c.currabrev=d.currcode LEFT JOIN banktrans bt ON bt.type=dt.type AND bt.transno=dt.transno
			WHERE $where ORDER BY dt.inputdate,dt.id LIMIT ? OFFSET ?";
		$data=[];
		foreach ($this->page($sql,$types,$params,$f) as $r) {
			$p=(int)$r['decimalplaces']; $amount=abs((float)$r['ovamount']); $allocated=abs((float)$r['alloc']);
			$data[]=[
				'external_id'=>'zerp-debtortrans-'.$r['id'],'payment_id'=>(int)$r['id'],'receipt_number'=>(int)$r['transno'],
				'customer_id'=>$r['debtorno'],'customer_name'=>$r['customer_name'],'branch_id'=>$r['branchcode'],
				'payment_date'=>$this->dateOnly($r['trandate']),'posting_date'=>$this->dateOnly($r['trandate']),
				'currency'=>$r['currcode'],'exchange_rate'=>$this->decimal($r['rate']),'amount'=>$this->money($amount,$p),
				'allocated_amount'=>$this->money($allocated,$p),'unallocated_amount'=>$this->money($amount-$allocated,$p),
				'payment_method'=>$r['banktranstype'] ?: null,'bank_account_code'=>$r['bankact'] ?: null,
				'cheque_number'=>$r['chequeno'] ?: null,'reference'=>$r['reference'],'accounting_period'=>(int)$r['prd'],
				'voided'=>false,'created_at'=>$this->dateTime($r['inputdate']),'updated_at'=>$this->dateTime($r['inputdate']),
			];
		}
		return ['total'=>$total,'data'=>$data];
	}

	private function allocations(array $f): array
	{
		$clauses=['1=1'];$types='';$params=[];
		if($f['customer_id']!==null){$clauses[]='src.debtorno=?';$types.='s';$params[]=$f['customer_id'];}
		if($f['invoice_id']!==null){$clauses[]='dst.id=?';$types.='i';$params[]=$f['invoice_id'];}
		if($f['from_date']!==null){$clauses[]='a.datealloc>=?';$types.='s';$params[]=$f['from_date'];}
		if($f['to_date']!==null){$clauses[]='a.datealloc<=?';$types.='s';$params[]=$f['to_date'];}
		if($f['updated_since']!==null){$clauses[]='a.datealloc>=?';$types.='s';$params[]=substr($f['updated_since'],0,10);}
		if($f['updated_until']!==null){$clauses[]='a.datealloc<=?';$types.='s';$params[]=substr($f['updated_until'],0,10);}
		$where=implode(' AND ',$clauses);
		$from='custallocns a INNER JOIN debtortrans src ON src.id=a.transid_allocfrom INNER JOIN debtortrans dst ON dst.id=a.transid_allocto';
		$total=$this->count($from,$where,$types,$params);
		$sql="SELECT a.*,src.debtorno,src.id source_id,src.transno source_no,src.type source_type,src.reference source_reference,
			dst.id target_id,dst.transno target_no,dst.type target_type,dst.reference target_reference,d.currcode,c.decimalplaces
			FROM $from INNER JOIN debtorsmaster d ON d.debtorno=src.debtorno INNER JOIN currencies c ON c.currabrev=d.currcode
			WHERE $where ORDER BY a.datealloc,a.id LIMIT ? OFFSET ?";
		$data=[];
		foreach($this->page($sql,$types,$params,$f) as $r){
			$data[]=[
				'external_id'=>'zerp-custalloc-'.$r['id'],'allocation_id'=>(int)$r['id'],'customer_id'=>$r['debtorno'],
				'payment'=>['transaction_id'=>(int)$r['source_id'],'transaction_number'=>(int)$r['source_no'],'transaction_type'=>(int)$r['source_type'],'reference'=>$r['source_reference']],
				'target'=>['transaction_id'=>(int)$r['target_id'],'transaction_number'=>(int)$r['target_no'],'transaction_type'=>(int)$r['target_type'],'reference'=>$r['target_reference']],
				'allocation_date'=>$this->dateOnly($r['datealloc']),'amount'=>$this->money($r['amt'],(int)$r['decimalplaces']),
				'currency'=>$r['currcode'],'voided'=>false,'created_at'=>$this->dateOnly($r['datealloc']),'updated_at'=>null,
			];
		}
		return ['total'=>$total,'data'=>$data];
	}

	private function transactionFilters(array $f,string $alias,array $transactionTypes): array
	{
		$clauses=[$alias.'.type IN ('.implode(',',$transactionTypes).')'];$types='';$params=[];
		if($f['customer_id']!==null){$clauses[]="$alias.debtorno=?";$types.='s';$params[]=$f['customer_id'];}
		if($f['branch_id']!==null){$clauses[]="$alias.branchcode=?";$types.='s';$params[]=$f['branch_id'];}
		if($f['invoice_id']!==null && $transactionTypes!==[12]){$clauses[]="$alias.id=?";$types.='i';$params[]=$f['invoice_id'];}
		$this->dateClauses($f,$clauses,$types,$params,"$alias.trandate","$alias.inputdate");
		if($f['status']!==null){
			$total="($alias.ovamount+$alias.ovgst+$alias.ovfreight+$alias.ovdiscount)";
			$clauses[]=match($f['status']){'open'=>"$alias.alloc=0 AND $alias.settled=0",'partially_paid'=>"$alias.alloc<>0 AND ABS($alias.alloc)<ABS($total) AND $alias.settled=0",'paid'=>"$alias.settled=1",'voided'=>'1=0'};
		}
		return [implode(' AND ',$clauses),$types,$params];
	}

	private function simpleFilters(array $f,string $customerColumn,?string $dateColumn,?string $updatedColumn): array
	{
		$clauses=['1=1'];$types='';$params=[];
		if($f['customer_id']!==null){$clauses[]="$customerColumn=?";$types.='s';$params[]=$f['customer_id'];}
		if($dateColumn!==null)$this->dateClauses($f,$clauses,$types,$params,$dateColumn,$updatedColumn);
		elseif($updatedColumn!==null){
			if($f['updated_since']!==null){$clauses[]="$updatedColumn>=?";$types.='s';$params[]=$f['updated_since'];}
			if($f['updated_until']!==null){$clauses[]="$updatedColumn<=?";$types.='s';$params[]=$f['updated_until'];}
		}
		return [implode(' AND ',$clauses),$types,$params];
	}

	private function dateClauses(array $f,array &$clauses,string &$types,array &$params,string $dateColumn,string $updatedColumn): void
	{
		if($f['from_date']!==null){$clauses[]="$dateColumn>=?";$types.='s';$params[]=$f['from_date'];}
		if($f['to_date']!==null){$clauses[]="$dateColumn<=?";$types.='s';$params[]=$f['to_date'];}
		if($f['updated_since']!==null){$clauses[]="$updatedColumn>=?";$types.='s';$params[]=$f['updated_since'];}
		if($f['updated_until']!==null){$clauses[]="$updatedColumn<=?";$types.='s';$params[]=$f['updated_until'];}
	}

	private function branches(array $ids): array
	{
		if($ids===[])return [];
		[$in,$types]=$this->in($ids,'s');
		$rows=$this->select("SELECT * FROM custbranch WHERE debtorno IN ($in) ORDER BY debtorno,branchcode",$types,$ids);$out=[];
		foreach($rows as $r)$out[$r['debtorno']][]=[
			'external_id'=>'zerp-customer-branch-'.$r['debtorno'].'-'.$r['branchcode'],'branch_id'=>$r['branchcode'],'name'=>$r['brname'],
			'address'=>$this->address($r,'braddress'),'postal_address'=>$this->address($r,'brpostaddr'),
			'contact'=>['name'=>$r['contactname'],'phone'=>$r['phoneno'],'email'=>$r['email'],'fax'=>$r['faxno']],
			'tax_group_id'=>(int)$r['taxgroupid'],'location_code'=>$r['defaultlocation'],'active'=>!(bool)$r['disabletrans'],
		];
		return $out;
	}

	private function linesForTransactions(array $transactions): array
	{
		if($transactions===[])return [];$conditions=[];$params=[];$types='';$byKey=[];
		foreach($transactions as $r){$conditions[]='(sm.type=? AND sm.transno=?)';$types.='ii';$params[]=(int)$r['type'];$params[]=(int)$r['transno'];$byKey[$r['type'].':'.$r['transno']]=(int)$r['id'];}
		$sql='SELECT sm.*,dt.id debtortransid,dt.debtorno customer_id,dt.inputdate,dt.order_,d.currcode,c.decimalplaces,sy.typename,s.description item_name,s.categoryid,s.units,sc.categorydescription FROM stockmoves sm INNER JOIN debtortrans dt ON dt.type=sm.type AND dt.transno=sm.transno INNER JOIN systypes sy ON sy.typeid=dt.type INNER JOIN debtorsmaster d ON d.debtorno=dt.debtorno INNER JOIN currencies c ON c.currabrev=d.currcode LEFT JOIN stockmaster s ON s.stockid=sm.stockid LEFT JOIN stockcategory sc ON sc.categoryid=s.categoryid WHERE '.implode(' OR ',$conditions).' ORDER BY sm.stkmoveno';
		$rows=$this->select($sql,$types,$params);$taxes=$this->lineTaxes(array_column($rows,'stkmoveno'));$out=[];
		foreach($rows as $r)$out[(int)$r['debtortransid']][]=$this->line($r,$taxes[(int)$r['stkmoveno']]??[]);
		return $out;
	}

	private function line(array $r,array $taxes): array
	{
		$quantity=(float)$r['qty'];$unit=(float)$r['price'];$gross=$quantity*$unit;$discount=$gross*(float)$r['discountpercent'];$net=$gross-$discount;
		$taxAmount=0.0;$taxBase=$net;
		foreach($taxes as &$tax){$amount=($tax['tax_on_tax']?$taxBase:$net)*(float)$tax['rate'];$tax['amount']=$this->decimal($amount);$taxAmount+=$amount;$taxBase+=$amount;}unset($tax);
		$places=(int)$r['decimalplaces'];
		return [
			'external_id'=>'zerp-stockmove-'.$r['stkmoveno'],'line_id'=>(int)$r['stkmoveno'],'invoice_id'=>(int)$r['debtortransid'],
			'invoice_number'=>(int)$r['transno'],'transaction_type'=>(int)$r['type'],'transaction_type_name'=>trim($r['typename']),
			'customer_id'=>$r['customer_id'],'invoice_date'=>$this->dateOnly($r['trandate']),'item_id'=>$r['stockid']?:null,
			'stock_code'=>$r['stockid']?:null,'item_name'=>$r['item_name']??null,'description'=>$r['narrative']?:($r['reference']?:($r['item_name']??'')),
			'category'=>isset($r['categoryid'])?['id'=>$r['categoryid'],'name'=>trim((string)$r['categorydescription'])]:null,
			'unit_of_measure'=>$r['units']??null,'quantity'=>$this->decimal($quantity),'unit_price'=>$this->decimal($unit),
			'gross_amount'=>$this->money($gross,$places),'discount_percent'=>$this->decimal($r['discountpercent']),
			'discount_amount'=>$this->money($discount,$places),'net_amount'=>$this->money($net,$places),
			'taxes'=>$taxes,'tax_amount'=>$this->money($taxAmount,$places),'line_total'=>$this->money($net+$taxAmount,$places),
			'currency'=>$r['currcode'],'warehouse_code'=>$r['loccode'],'sales_order_id'=>(int)$r['order_']?:null,
			'standard_cost'=>$this->decimal($r['standardcost']),'voided'=>(bool)$r['hidemovt'],
			'created_at'=>$this->dateTime($r['inputdate']),'updated_at'=>$this->dateTime($r['inputdate']),
		];
	}

	private function lineTaxes(array $ids): array
	{
		if($ids===[])return [];[$in,$types]=$this->in($ids);
		$rows=$this->select("SELECT st.stkmoveno,st.taxauthid,ta.description,st.taxrate,st.taxontax,st.taxcalculationorder FROM stockmovestaxes st INNER JOIN taxauthorities ta ON ta.taxid=st.taxauthid WHERE st.stkmoveno IN ($in) ORDER BY st.stkmoveno,st.taxcalculationorder,st.taxauthid",$types,$ids);$out=[];
		foreach($rows as $r)$out[(int)$r['stkmoveno']][]=[
			'external_id'=>'zerp-stockmove-tax-'.$r['stkmoveno'].'-'.$r['taxauthid'],
			'tax_authority_id'=>(int)$r['taxauthid'],'description'=>$r['description'],'rate'=>$this->decimal($r['taxrate']),
			'tax_on_tax'=>(bool)$r['taxontax'],'calculation_order'=>(int)$r['taxcalculationorder'],
		];
		return $out;
	}

	private function invoiceTaxes(array $ids): array
	{
		if ($ids === []) return [];
		[$in, $types] = $this->in($ids);
		$rows = $this->select("SELECT tx.debtortransid,tx.taxauthid,ta.description,tx.taxamount
			FROM debtortranstaxes tx INNER JOIN taxauthorities ta ON ta.taxid=tx.taxauthid
			WHERE tx.debtortransid IN ($in) ORDER BY tx.debtortransid,tx.taxauthid", $types, $ids);
		$out = [];
		foreach ($rows as $row) {
			$out[(int)$row['debtortransid']][] = [
				'external_id' => 'zerp-debtortrans-tax-' . $row['debtortransid'] . '-' . $row['taxauthid'],
				'tax_authority_id' => (int)$row['taxauthid'],
				'description' => $row['description'],
				'amount' => (float)$row['taxamount'],
			];
		}
		return $out;
	}

	private function count(string $from,string $where,string $types,array $params): int
	{
		$row=$this->select("SELECT COUNT(*) total FROM $from WHERE $where",$types,$params)[0]??null;
		if($row===null)throw new RuntimeException('Unable to count records');return (int)$row['total'];
	}

	private function page(string $sql,string $types,array $params,array $f): array
	{
		$offset=($f['page']-1)*$f['page_size'];$params[]=$f['page_size'];$params[]=$offset;
		return $this->select($sql,$types.'ii',$params);
	}

	private function select(string $sql,string $types='',array $params=[]): array
	{
		$stmt=$this->db->prepare($sql);if($stmt===false)throw new RuntimeException('Unable to prepare database query');
		try{if($params!==[]&&!$stmt->bind_param($types,...$params))throw new RuntimeException('Unable to bind database query');if(!$stmt->execute())throw new RuntimeException('Unable to execute database query');$result=$stmt->get_result();if($result===false)throw new RuntimeException('Unable to retrieve database result');return $result->fetch_all(MYSQLI_ASSOC);}finally{$stmt->close();}
	}

	private function in(array $values,string $type='i'): array{return [implode(',',array_fill(0,count($values),'?')),str_repeat($type,count($values))];}
	private function decimal(mixed $value): string{return rtrim(rtrim(number_format((float)$value,10,'.',''),'0'),'.')?:'0';}
	private function money(mixed $value,int $places): string{return number_format((float)$value,$places,'.','');}
	private function dateOnly(?string $value): ?string{return $value===null||str_starts_with($value,'1000-01-01')?null:substr($value,0,10);}
	private function dateTime(?string $value): ?string{return $value===null||str_starts_with($value,'1000-01-01')?null:(new DateTimeImmutable($value))->format(DateTimeInterface::ATOM);}
	private function address(array $r,string $prefix): array{$a=[];for($i=1;$i<=6;$i++)$a['line'.$i]=$r[$prefix.$i]??'';return $a;}
	private function status(array $r,float $total): string{if((bool)$r['settled'])return 'paid';if(abs((float)$r['alloc'])>0.000001&&abs((float)$r['alloc'])<abs($total))return 'partially_paid';return 'open';}
	private function productType(string $flag,string $stockType): string{return match($flag){'D'=>'service','K'=>'kit','M','A','G'=>'manufactured',default=>$stockType==='L'?'labour':'stock'};}
	private function dueDate(array $invoice): ?string
	{
		$date=new DateTimeImmutable($invoice['trandate']);
		if((int)$invoice['dayinfollowingmonth']>0){$next=$date->modify('first day of next month');$date=$next->setDate((int)$next->format('Y'),(int)$next->format('m'),min((int)$invoice['dayinfollowingmonth'],28));}
		else $date=$date->modify('+'.(int)$invoice['daysbeforedue'].' days');return $date->format('Y-m-d');
	}
}
