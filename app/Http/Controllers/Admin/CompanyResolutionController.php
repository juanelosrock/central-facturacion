<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyResolution;
use App\Services\QimeraApiService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Throwable;

class CompanyResolutionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:companies.edit'),
        ];
    }

    /**
     * Vista principal: muestra habilitación + listado de producción + formulario.
     */
    public function index(Company $company)
    {
        $company->load(['habilitationResolution', 'productionResolutions', 'creditNoteResolution']);

        return view('admin.companies.resolutions.index', compact('company'));
    }

    /**
     * Crea (o reenvía) la resolución de habilitación con los datos fijos del DIAN.
     */
    public function storeHabilitation(Company $company, QimeraApiService $api)
    {
        if (empty($company->api_token)) {
            return back()->with('error', 'La empresa no tiene token del API. Completá el paso 1.');
        }

        $payload = CompanyResolution::habilitationPayload();

        $resolution = $company->resolutions()->updateOrCreate(
            [
                'company_id' => $company->id,
                'is_habilitation' => true,
            ],
            $payload
        );

        try {
            $response = $api->configureResolution($company->api_token, $resolution->toApiPayload());

            $resolution->update([
                'api_resolution_id' => data_get($response, 'resolution.id'),
                'api_response' => $response,
                'last_synced_at' => now(),
            ]);

            $this->flashDebug();

            return redirect()->route('admin.companies.resolutions.index', $company)
                ->with('success', 'Resolución de habilitación creada/actualizada correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al crear resolución de habilitación', [
                'error' => $e->getMessage(),
                'debug' => QimeraApiService::$lastDebug,
            ]);

            $this->flashDebug();

            return redirect()->route('admin.companies.resolutions.index', $company)
                ->with('error', 'Datos guardados, pero falló el envío al API: ' . $e->getMessage());
        }
    }

    /**
     * Crea (o reenvía) la resolución de nota crédito con los datos fijos.
     */
    public function storeCreditNoteResolution(Company $company, QimeraApiService $api)
    {
        if (empty($company->api_token)) {
            return back()->with('error', 'La empresa no tiene token del API. Completá el paso 1.');
        }

        $payload = CompanyResolution::creditNoteResolutionPayload();

        $resolution = $company->resolutions()->updateOrCreate(
            ['company_id' => $company->id, 'type_document_id' => 4, 'is_habilitation' => false],
            array_merge($payload, ['is_habilitation' => false])
        );

        try {
            $response = $api->configureResolution($company->api_token, $resolution->toApiPayload());

            $resolution->update([
                'api_resolution_id' => data_get($response, 'resolution.id'),
                'api_response'      => $response,
                'last_synced_at'    => now(),
            ]);

            $this->flashDebug();

            return redirect()->route('admin.companies.resolutions.index', $company)
                ->with('success', 'Resolución de nota crédito creada/actualizada correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al crear resolución de nota crédito', [
                'error' => $e->getMessage(),
                'debug' => QimeraApiService::$lastDebug,
            ]);

            $this->flashDebug();

            return redirect()->route('admin.companies.resolutions.index', $company)
                ->with('error', 'Datos guardados, pero falló el envío al API: ' . $e->getMessage());
        }
    }

    /**
     * Marca (o desmarca) la empresa como habilitada manualmente.
     */
    public function toggleHabilitation(Company $company)
    {
        if ($company->habilitation_passed) {
            $company->update([
                'habilitation_passed' => false,
                'habilitation_passed_at' => null,
            ]);
            $msg = 'Habilitación revertida.';
        } else {
            if (!$company->habilitationResolution) {
                return back()->with('error', 'Primero debés crear la resolución de habilitación.');
            }

            $company->update([
                'habilitation_passed' => true,
                'habilitation_passed_at' => now(),
            ]);
            $msg = 'Empresa marcada como habilitada. Ya podés agregar resoluciones de producción.';
        }

        return redirect()->route('admin.companies.resolutions.index', $company)
            ->with('success', $msg);
    }

    /**
     * Crea una resolución de producción (solo si la empresa está habilitada).
     */
    public function store(Request $request, Company $company, QimeraApiService $api)
    {
        if (!$company->habilitation_passed) {
            return back()->with('error', 'La empresa aún no completó las pruebas de habilitación.');
        }

        if (empty($company->api_token)) {
            return back()->with('error', 'La empresa no tiene token del API.');
        }

        $data = $this->validateData($request);

        $resolution = $company->resolutions()->create(array_merge($data, [
            'is_habilitation' => false,
        ]));

        try {
            $response = $api->configureResolution($company->api_token, $resolution->toApiPayload());

            $resolution->update([
                'api_resolution_id' => data_get($response, 'resolution.id'),
                'api_response' => $response,
                'last_synced_at' => now(),
            ]);

            $this->flashDebug();

            return redirect()->route('admin.companies.resolutions.index', $company)
                ->with('success', 'Resolución de producción creada correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al crear resolución de producción', [
                'error' => $e->getMessage(),
                'debug' => QimeraApiService::$lastDebug,
            ]);

            $this->flashDebug();

            return redirect()->route('admin.companies.resolutions.index', $company)
                ->with('error', 'Datos guardados, pero falló el envío al API: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una resolución (solo local, no toca el API).
     */
    public function destroy(Company $company, CompanyResolution $resolution)
    {
        if ($resolution->company_id !== $company->id) {
            abort(404);
        }

        if ($resolution->is_habilitation) {
            return back()->with('error', 'No se puede eliminar la resolución de habilitación desde acá.');
        }

        $resolution->delete();

        return redirect()->route('admin.companies.resolutions.index', $company)
            ->with('success', 'Resolución eliminada localmente.');
    }

	/**
	 * Muestra el editor tipo Postman con el payload de nota crédito de prueba.
	 */
	public function showTestCreditNote(Company $company)
	{
		if (empty($company->api_token)) {
			return redirect()->route('admin.companies.resolutions.index', $company)
				->with('error', 'La empresa no tiene token del API.');
		}

		if (!$company->habilitationResolution) {
			return redirect()->route('admin.companies.resolutions.index', $company)
				->with('error', 'Primero debés crear la resolución de habilitación.');
		}

		$payload = CompanyResolution::habilitationTestCreditNotePayload($company);
		$payloadJson = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

		return view('admin.companies.resolutions.test-credit-note', compact('company', 'payloadJson'));
	}

	/**
	 * Envía la nota crédito de prueba al API.
	 */
	public function sendTestCreditNote(Request $request, Company $company, QimeraApiService $api)
	{
		if (empty($company->api_token)) {
			return back()->with('error', 'La empresa no tiene token del API.');
		}

		$raw = $request->input('payload_json', '');
		$payload = json_decode($raw, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return back()->withInput()->with('error', 'El JSON es inválido: ' . json_last_error_msg());
		}

		try {
			$response = $api->sendCreditNote($company->api_token, $payload);

			$this->flashDebug();

			return redirect()->route('admin.companies.resolutions.index', $company)
				->with('success', 'Nota crédito de prueba enviada. Revisá la respuesta del API abajo.');
		} catch (Throwable $e) {
			Log::error('Error al enviar nota crédito de prueba', [
				'error' => $e->getMessage(),
				'debug' => QimeraApiService::$lastDebug,
			]);

			$this->flashDebug();

			return back()->withInput()->with('error', 'Error al enviar nota crédito de prueba: ' . $e->getMessage());
		}
	}

    protected function validateData(Request $request): array
    {
        $tipoDoc = (int) $request->input('type_document_id');
        $sinResolucion = in_array($tipoDoc, \App\Models\CompanyResolution::TYPES_WITHOUT_RESOLUTION);

        return $request->validate([
            'type_document_id'  => 'required|integer',
            'prefix'            => 'nullable|string|max:10',
            'from'              => 'required|integer|min:1',
            'to'                => 'required|integer|gt:from',
            'resolution'        => $sinResolucion ? 'nullable|string|max:50'  : 'required|string|max:50',
            'resolution_date'   => $sinResolucion ? 'nullable|date'           : 'required|date',
            'technical_key'     => 'nullable|string|max:255',
            'generated_to_date' => 'nullable|integer|min:0',
            'date_from'         => $sinResolucion ? 'nullable|date'           : 'required|date',
            'date_to'           => $sinResolucion ? 'nullable|date'           : 'required|date|after_or_equal:date_from',
        ]);
    }
	
	/**
	 * Muestra el editor tipo Postman con el payload precargado para revisión.
	 */
	public function showTestInvoice(Company $company)
	{
		if (empty($company->api_token)) {
			return redirect()->route('admin.companies.resolutions.index', $company)
				->with('error', 'La empresa no tiene token del API.');
		}

		if (!$company->habilitationResolution) {
			return redirect()->route('admin.companies.resolutions.index', $company)
				->with('error', 'Primero debés crear la resolución de habilitación.');
		}

		$payload = CompanyResolution::habilitationTestInvoicePayload($company);
		$payloadJson = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

		return view('admin.companies.resolutions.test-invoice', compact('company', 'payloadJson'));
	}

	/**
	 * Envía la factura de prueba al ambiente de habilitación DIAN.
	 */
	public function sendTestInvoice(Request $request, Company $company, QimeraApiService $api)
	{
		if (empty($company->api_token)) {
			return back()->with('error', 'La empresa no tiene token del API.');
		}

		if (!$company->habilitationResolution) {
			return back()->with('error', 'Primero debés crear la resolución de habilitación.');
		}

		$raw = $request->input('payload_json', '');
		$payload = json_decode($raw, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return back()->withInput()->with('error', 'El JSON es inválido: ' . json_last_error_msg());
		}

		try {
			$response = $api->sendInvoice($company->api_token, $payload);

			$company->update([
				'test_invoice_response' => $response,
				'test_invoice_sent_at' => now(),
				'test_invoice_success' => data_get($response, 'is_valid', false) || data_get($response, 'success', false),
			]);

			$this->flashDebug();

			return redirect()->route('admin.companies.resolutions.index', $company)
				->with('success', 'Factura de prueba enviada. Revisá la respuesta del API abajo.');
		} catch (Throwable $e) {
			Log::error('Error al enviar factura de prueba', [
				'error' => $e->getMessage(),
				'debug' => QimeraApiService::$lastDebug,
			]);

			$company->update([
				'test_invoice_response' => QimeraApiService::$lastDebug['response_body'] ?? null,
				'test_invoice_sent_at' => now(),
				'test_invoice_success' => false,
			]);

			$this->flashDebug();

			return back()->withInput()->with('error', 'Error al enviar factura de prueba: ' . $e->getMessage());
		}
	}

    protected function flashDebug(): void
    {
        if (!empty(QimeraApiService::$lastDebug)) {
            session()->flash('api_debug', QimeraApiService::$lastDebug);
        }
    }
}