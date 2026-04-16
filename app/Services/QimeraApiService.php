<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class QimeraApiService
{
    protected string $baseUrl;
    protected ?string $token;

    public static array $lastDebug = [];

    public function __construct()
    {
        $this->baseUrl = rtrim(Setting::get('qimera_api_url', ''), '/');
        $this->token = Setting::get('qimera_api_token', null);

        if (empty($this->baseUrl)) {
            throw new RuntimeException('La URL base del API no está configurada. Revisá Settings.');
        }
    }

    /**
     * POST /ubl2.1/config/{nit}/{dv}
     * Endpoint público (no requiere token).
     */
    public function createOrUpdateCompany(string $nit, string $dv, array $payload): array
    {
        $url = "{$this->baseUrl}/ubl2.1/config/{$nit}/{$dv}";

        return $this->request('POST', $url, $payload, false);
    }
	
	/**
	 * PUT /ubl2.1/config/software
	 * Requiere el token de la empresa.
	 */
	public function configureSoftware(string $companyToken, array $payload): array
	{
		$url = "{$this->baseUrl}/ubl2.1/config/software";

		return $this->request('PUT', $url, $payload, true, $companyToken);
	}
	
	/**
	 * PUT /ubl2.1/config/certificate
	 * Requiere el token de la empresa.
	 */
	public function configureCertificate(string $companyToken, array $payload): array
	{
		$url = "{$this->baseUrl}/ubl2.1/config/certificate";

		return $this->request('PUT', $url, $payload, true, $companyToken);
	}
	
	/**
	 * PUT /ubl2.1/config/resolution
	 */
	public function configureResolution(string $companyToken, array $payload): array
	{
		$url = "{$this->baseUrl}/ubl2.1/config/resolution";

		return $this->request('PUT', $url, $payload, true, $companyToken);
	}
	
	/**
	 * POST /ubl2.1/invoice
	 * Envía una factura electrónica (o factura de prueba para habilitación).
	 */
	public function sendInvoice(string $companyToken, array $payload): array
	{
		$url = "{$this->baseUrl}/ubl2.1/invoice";

		return $this->request('POST', $url, $payload, true, $companyToken);
	}

	/**
	 * POST /ubl2.1/credit-note
	 * Envía una nota crédito (o nota crédito de prueba para habilitación).
	 */
	public function sendCreditNote(string $companyToken, array $payload): array
	{
		$url = "{$this->baseUrl}/ubl2.1/credit-note";

		return $this->request('POST', $url, $payload, true, $companyToken);
	}

    /**
     * Ejecuta un request HTTP con cURL nativo.
     *
     * @param  string  $method  GET, POST, PUT, DELETE, etc.
     * @param  string  $url     URL completa
     * @param  array   $payload Body (se envía como JSON)
     * @param  bool    $authenticated  Si true, agrega Authorization Bearer
     * @param  string|null $tokenOverride Token específico (para endpoints por empresa)
     */
    public function request(
        string $method,
        string $url,
        array $payload = [],
        bool $authenticated = true,
        ?string $tokenOverride = null
    ): array {
        $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $jsonBodyPretty = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $tokenToUse = $tokenOverride ?? $this->token;
        if ($authenticated && $tokenToUse) {
            $headers[] = 'Authorization: Bearer ' . $tokenToUse;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false, // útil en desarrollo local
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $rawResponse = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        $curlErrno = curl_errno($curl);
        curl_close($curl);

        // Intentamos decodificar la respuesta como JSON
        $decoded = json_decode($rawResponse, true);
        $responseBody = json_last_error() === JSON_ERROR_NONE ? $decoded : $rawResponse;

        $this->captureDebug(
            method: $method,
            url: $url,
            payload: $payload,
            jsonBodyPretty: $jsonBodyPretty,
            headers: $headers,
            authenticated: $authenticated,
            tokenUsed: $tokenToUse,
            httpStatus: $httpStatus,
            responseBody: $responseBody,
            rawResponse: $rawResponse,
            curlError: $curlError,
            curlErrno: $curlErrno
        );

        // Errores de cURL (red, DNS, timeout, etc.)
        if ($curlErrno !== 0) {
            throw new RuntimeException("Error de cURL ({$curlErrno}): {$curlError}");
        }

        // Errores HTTP
        if ($httpStatus < 200 || $httpStatus >= 300) {
            $msg = is_array($responseBody) ? ($responseBody['message'] ?? 'Error desconocido') : $rawResponse;

            if (is_array($responseBody) && isset($responseBody['errors']) && is_array($responseBody['errors'])) {
                $errors = [];
                foreach ($responseBody['errors'] as $field => $messages) {
                    $errors[] = $field . ': ' . (is_array($messages) ? implode(', ', $messages) : $messages);
                }
                $msg .= ' | ' . implode(' | ', $errors);
            }

            throw new RuntimeException('Error del API: ' . $msg, $httpStatus);
        }

        return is_array($responseBody) ? $responseBody : [];
    }

    protected function captureDebug(
        string $method,
        string $url,
        array $payload,
        string $jsonBodyPretty,
        array $headers,
        bool $authenticated,
        ?string $tokenUsed,
        int $httpStatus,
        mixed $responseBody,
        string $rawResponse,
        string $curlError,
        int $curlErrno
    ): void {
        // cURL reproducible para terminal
        $curlCmd = "curl --location --request {$method} '{$url}' \\\n";
        foreach ($headers as $h) {
            $curlCmd .= "  --header '{$h}' \\\n";
        }
        $curlCmd .= "  --data-raw '" . $jsonBodyPretty . "'";

        // Versión "segura" de los headers para mostrar en UI (token recortado)
        $headersDisplay = [];
        foreach ($headers as $h) {
            if (str_starts_with($h, 'Authorization:') && $tokenUsed) {
                $headersDisplay[] = 'Authorization: Bearer ' . substr($tokenUsed, 0, 10) . '...';
            } else {
                $headersDisplay[] = $h;
            }
        }

        self::$lastDebug = [
            'method' => $method,
            'url' => $url,
            'authenticated' => $authenticated,
            'headers' => $headersDisplay,
            'payload' => $payload,
            'payload_json' => $jsonBodyPretty,
            'curl' => $curlCmd,
            'status' => $httpStatus,
            'response_body' => $responseBody,
            'response_raw' => $rawResponse,
            'curl_error' => $curlError,
            'curl_errno' => $curlErrno,
        ];

        Log::channel('single')->info('QimeraApi call (cURL)', self::$lastDebug);
    }
}