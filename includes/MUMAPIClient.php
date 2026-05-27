<?php

class MUMAPIClient {
    private $baseUrl;
    private $clientId;
    private $clientSecret;
    private $token = null;
    private $tokenExpiresAt = 0;
    private $timeout = 30; // 30 seconds default timeout

    public function __construct($clientId, $clientSecret) {
        $SARIS_API_BASE_URL = '';
        $configFile = $this->getConfigFile();
        if ($configFile !== null) {
            include($configFile);
        }

        $this->baseUrl = rtrim($SARIS_API_BASE_URL, '/');
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    private function getConfigFile() {
        $configFile = __DIR__ . '/../config.php';
        if (file_exists($configFile)) {
            return $configFile;
        }

        $configFile = __DIR__ . '/../config.php';
        if (file_exists($configFile)) {
            return $configFile;
        }

        return null;
    }

    private function encodeAuthQueryValue($value) {
        return str_replace(
            ['%', '#', '&'],
            ['%25', '%23', '%26'],
            $value
        );
    }

    private function authenticate() {
        $SARIS_API_CLIENT_ID = '';
        $SARIS_API_CLIENT_SECRET = '';
        $configFile = $this->getConfigFile();
        if ($configFile !== null) {
            include($configFile);
        }

        if ($SARIS_API_CLIENT_ID !== '') {
            $this->clientId = $SARIS_API_CLIENT_ID;
        }
        if ($SARIS_API_CLIENT_SECRET !== '') {
            $this->clientSecret = $SARIS_API_CLIENT_SECRET;
        }

        $url = $this->baseUrl . '/api_erp/v1/auth'
            . '?clientId=' . $this->encodeAuthQueryValue($this->clientId)
            . '&clientSecret=' . $this->encodeAuthQueryValue($this->clientSecret);

        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false) {
            $this->logError("Authentication cURL Error: $error");
            throw new Exception("Error communicating with MUM API for authentication. URL: ".$url);
        }

        $response = json_decode($result, true);

        if (isset($response['statusCode']) && $response['statusCode'] == 200 && isset($response['token'])) {
            $this->token = $response['token'];
            $this->tokenExpiresAt = time() + (isset($response['expires_in']) ? $response['expires_in'] : 900) - 30;
        } else {
            $this->logError("Authentication failed HTTP $httpCode: " . ($response === null ? $result : json_encode($response)));
            throw new Exception("Authentication failed. Please check api_migration_error.log for the API response.");
        }
    }

    private function getToken() {
        if ($this->token === null || time() >= $this->tokenExpiresAt) {
            $this->authenticate();
        }
        return $this->token;
    }

    private function makeRequest($endpoint, $params = []) {
        $token = $this->getToken();
        $url = $this->baseUrl . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($result === false) {
            $this->logError("Endpoint $endpoint cURL Error: $error");
            throw new Exception("Error communicating with MUM API endpoint: $endpoint");
        }
        
        $decoded = json_decode($result, true);
        if ($httpCode !== 200 && (!isset($decoded['statusCode']) || $decoded['statusCode'] != 200)) {
            $this->logError("Endpoint $endpoint returned HTTP $httpCode: " . json_encode($decoded));
        }

        return $decoded;
    }

    public function getStudentInfo($regNumber) {
        return $this->makeRequest('/api_erp/v1/get_student_info', ['reg_number' => $regNumber]);
    }

    public function getAllInvoices($params = []) {
        return $this->makeRequest('/api_erp/v1/get_all_invoices', $params);
    }

    public function logError($message) {
        $logFile = __DIR__ . '/../api_migration_error.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
}
?>
