<?php

use PhpXmlRpc\Client;
use PhpXmlRpc\Encoder;
use PhpXmlRpc\Request;

class ZERPXMLRPCException extends RuntimeException {
	private $faultCodeValue;

	public function __construct($message, $faultCode = null) {
		parent::__construct($message);
		$this->faultCodeValue = $faultCode;
	}

	public function getFaultCodeValue() {
		return $this->faultCodeValue;
	}
}

class ZERPXMLRPCClient {
	private $client;
	private $encoder;
	private $username;
	private $password;

	private const ERROR_MESSAGES = [
		1 => 'No authorisation',
		1000 => 'Incorrect debtor number length',
		1001 => 'Debtor number already exists',
		1002 => 'Incorrect debtor name length',
		1004 => 'Currency code is not configured',
		1005 => 'Sales type is not configured',
		1007 => 'Hold reason is not configured',
		1008 => 'Payment terms are not configured',
		1025 => 'Database update failed',
		1027 => 'Customer does not exist',
		1029 => 'Branch already exists',
		1030 => 'Incorrect branch name length',
		1032 => 'Sales area is not configured',
		1033 => 'Salesperson is not configured',
		1039 => 'Location is not configured',
		1040 => 'Tax group is not configured',
		1041 => 'Shipper is not configured',
		1046 => 'Branch does not exist',
		1047 => 'Invoice stock code does not exist',
		1069 => 'Invalid transaction date',
		1071 => 'Invalid transaction reference',
		1072 => 'Invalid sales type',
		1074 => 'Invalid exchange rate',
		1075 => 'Invalid invoice amount',
		1081 => 'Invalid shipping method',
		1116 => 'Customer type is not configured',
		1129 => 'Invalid bank account',
		1165 => 'Allocation source must be a receipt or credit note',
		1166 => 'No matching transaction is available for allocation',
		1167 => 'Invalid invoice discount',
	];

	public function __construct(array $config) {
		$url = trim((string)($config['url'] ?? ''));
		if ($url === '') {
			throw new InvalidArgumentException('The ZERP XML-RPC endpoint is not configured.');
		}
		$this->username = (string)($config['username'] ?? '');
		echo 'user name:'. $this->username;
		echo 'password:'. $this->password;
		exit;
		$this->password = (string)($config['password'] ?? '');
		if ($this->username === '' || $this->password === '') {
			throw new InvalidArgumentException('The ZERP XML-RPC username and password are required.');
		}

		$this->client = new Client($url);
		$this->client->setOption(Client::OPT_TIMEOUT, max(1, (int)($config['timeout'] ?? 60)));
		$this->client->setOption(Client::OPT_ACCEPTED_COMPRESSION, []);
		$this->encoder = new Encoder();
	}

	public function insertCustomer(array $customer) {
		return $this->successfulResult('weberp.xmlrpc_InsertCustomer', [$customer]);
	}

	public function insertBranch(array $branch) {
		return $this->successfulResult('weberp.xmlrpc_InsertBranch', [$branch]);
	}

	public function insertSalesInvoice(array $invoice) {
		return $this->successfulResult('weberp.xmlrpc_InsertSalesInvoice', [$invoice]);
	}

	public function insertDebtorReceipt(array $receipt) {
		return $this->successfulResult('weberp.xmlrpc_InsertDebtorReceipt', [$receipt]);
	}

	public function allocateTransaction(array $allocation) {
		return $this->successfulResult('weberp.xmlrpc_AllocateTrans', [$allocation]);
	}

	public function customerExists($debtorNo) {
		return $this->getCustomer($debtorNo) !== null;
	}

	public function getCustomer($debtorNo) {
		$result = $this->call('weberp.xmlrpc_GetCustomer', [(string)$debtorNo]);
		$code = $this->firstCode($result);
		if ($code === 0) {
			return isset($result[1]) && is_array($result[1]) ? $result[1] : [];
		}
		if ($code === 1027) {
			return null;
		}
		$this->throwApiError('weberp.xmlrpc_GetCustomer', $result);
	}

	public function branchExists($debtorNo, $branchCode) {
		return $this->getBranch($debtorNo, $branchCode) !== null;
	}

	public function getBranch($debtorNo, $branchCode) {
		$result = $this->call('weberp.xmlrpc_GetCustomerBranch', [(string)$debtorNo, (string)$branchCode]);
		$code = $this->firstCode($result);
		if ($code === 0) {
			return $result;
		}
		if ($code === 1046) {
			return null;
		}
		$this->throwApiError('weberp.xmlrpc_GetCustomerBranch', $result);
	}

	private function successfulResult($method, array $parameters) {
		$result = $this->call($method, $parameters);
		if ($this->firstCode($result) !== 0) {
			$this->throwApiError($method, $result);
		}
		return $result;
	}

	private function call($method, array $parameters) {
		$parameters[] = $this->username;
		$parameters[] = $this->password;
		$encoded = [];
		foreach ($parameters as $parameter) {
			$encoded[] = $this->encoder->encode($parameter);
		}

		try {
			$response = $this->client->send(new Request($method, $encoded));
		} catch (Throwable $exception) {
			throw new ZERPXMLRPCException(
				'XML-RPC transport failed for ' . $method . ': ' . $exception->getMessage(),
				null
			);
		}

		if ($response->faultCode() !== 0) {
			throw new ZERPXMLRPCException(
				'XML-RPC fault from ' . $method . ': ' . $response->faultString(),
				$response->faultCode()
			);
		}

		$result = $this->encoder->decode($response->value());
		if (is_array($result) && isset($result['error'])) {
			$message = (string)$result['error'];
			if (!empty($result['err'])) {
				$message .= ': ' . $result['err'];
			}
			throw new ZERPXMLRPCException($method . ' failed: ' . $message);
		}
		if (!is_array($result)) {
			throw new ZERPXMLRPCException($method . ' returned an unexpected response.');
		}
		return $result;
	}

	private function firstCode(array $result) {
		if (!array_key_exists(0, $result) || !is_numeric($result[0])) {
			return null;
		}
		return (int)$result[0];
	}

	private function throwApiError($method, array $result) {
		$codes = [];
		foreach ($result as $value) {
			if (is_numeric($value) && (int)$value !== 0) {
				$codes[] = (int)$value;
			}
		}
		$messages = [];
		foreach ($codes as $code) {
			$messages[] = self::ERROR_MESSAGES[$code] ?? ('ZERP API error ' . $code);
		}
		$messages = array_values(array_unique($messages));
		$message = $messages ? implode('; ', $messages) : 'Unexpected ZERP API response';
		throw new ZERPXMLRPCException($method . ' failed: ' . $message, $codes[0] ?? null);
	}
}

?>
