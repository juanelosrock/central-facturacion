<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\QimeraApiService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Throwable;

class CompanyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
	{
		return [
			new Middleware('permission:companies.view', only: ['index', 'show']),
			new Middleware('permission:companies.create', only: ['create', 'store']),
			new Middleware('permission:companies.edit', only: [
				'edit', 'update',
				'editSoftware', 'updateSoftware',
				'editCertificate', 'updateCertificate',
			]),
			new Middleware('permission:companies.delete', only: ['destroy']),
			new Middleware('permission:companies.sync', only: ['sync']),
		];
	}

    public function index()
	{
		$companies = Company::with(['software', 'certificate'])
			->orderByDesc('id')
			->paginate(15);

		return view('admin.companies.index', compact('companies'));
	}

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request, QimeraApiService $api)
	{
		$data = $this->validateData($request);

		$company = Company::create($data);

		try {
			$response = $api->createOrUpdateCompany(
				$company->identification_number,
				$company->dv,
				$company->toApiPayload()
			);

			$this->saveApiResponse($company, $response);
			$this->flashDebug();

			return redirect()->route('admin.companies.edit', $company)
				->with('success', 'Empresa creada y sincronizada con el API.');
		} catch (Throwable $e) {
			Log::error('Error al crear empresa en API', [
				'error' => $e->getMessage(),
				'debug' => QimeraApiService::$lastDebug,
			]);

			$this->flashDebug();

			return redirect()->route('admin.companies.edit', $company)
				->with('error', 'Empresa guardada localmente, pero falló la sincronización: ' . $e->getMessage());
		}
	}

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company, QimeraApiService $api)
	{
		$data = $this->validateData($request, $company->id);

		$company->update($data);

		try {
			$response = $api->createOrUpdateCompany(
				$company->identification_number,
				$company->dv,
				$company->toApiPayload()
			);

			$this->saveApiResponse($company, $response);
			$this->flashDebug();

			return redirect()->route('admin.companies.edit', $company)
				->with('success', 'Empresa actualizada y sincronizada con el API.');
		} catch (Throwable $e) {
			Log::error('Error al actualizar empresa en API', [
				'error' => $e->getMessage(),
				'debug' => QimeraApiService::$lastDebug,
			]);

			$this->flashDebug();

			return redirect()->route('admin.companies.edit', $company)
				->with('error', 'Empresa guardada localmente, pero falló la sincronización: ' . $e->getMessage());
		}
	}

    public function sync(Company $company, QimeraApiService $api)
	{
		try {
			$response = $api->createOrUpdateCompany(
				$company->identification_number,
				$company->dv,
				$company->toApiPayload()
			);

			$this->saveApiResponse($company, $response);
			$this->flashDebug();

			return redirect()->route('admin.companies.edit', $company)
				->with('success', 'Empresa sincronizada con el API.');
		} catch (Throwable $e) {
			Log::error('Error al sincronizar empresa', [
				'error' => $e->getMessage(),
				'debug' => QimeraApiService::$lastDebug,
			]);

			$this->flashDebug();

			return redirect()->route('admin.companies.edit', $company)
				->with('error', 'Error al sincronizar: ' . $e->getMessage());
		}
	}

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('admin.companies.index')
            ->with('success', 'Empresa eliminada localmente.');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        $unique = 'unique:companies,identification_number' . ($id ? ",{$id}" : '');

        return $request->validate([
            'identification_number' => "required|string|max:20|{$unique}",
            'dv' => 'required|string|max:2',
            'type_document_identification_id' => 'required|integer',
            'type_organization_id' => 'required|integer',
            'type_regime_id' => 'required|integer',
            'type_liability_id' => 'required|integer',
            'business_name' => 'required|string|max:255',
            'merchant_registration' => 'nullable|string|max:50',
            'municipality_id' => 'required|integer',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'required|email|max:255',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
        ]);
    }

    protected function saveApiResponse(Company $company, array $response): void
    {
        $company->update([
            'api_company_id' => data_get($response, 'company.id'),
            'api_token' => data_get($response, 'token'),
            'api_password' => data_get($response, 'password'),
            'api_response' => $response,
            'last_synced_at' => now(),
        ]);
    }
	
	public function editSoftware(Company $company)
	{
		$software = $company->software;
		return view('admin.companies.software', compact('company', 'software'));
	}

	public function updateSoftware(Request $request, Company $company, QimeraApiService $api)
	{
		if (empty($company->api_token)) {
			return back()->with('error', 'Esta empresa todavía no tiene un token generado. Sincronizá primero el paso 1.');
		}

		$data = $request->validate([
			'identifier' => 'required|string|max:100',
			'pin' => 'required|numeric',
		]);

		$software = $company->software()->updateOrCreate(
			['company_id' => $company->id],
			$data
		);

		try {
			$response = $api->configureSoftware(
				$company->api_token,
				$software->toApiPayload()
			);

			$apiSoftware = data_get($response, 'software', []);

			$software->update([
				'api_software_id' => data_get($apiSoftware, 'id'),
				'identifier' => data_get($apiSoftware, 'identifier', $software->identifier),
				'pin' => data_get($apiSoftware, 'pin', $software->pin),
				'identifier_payroll' => data_get($apiSoftware, 'identifier_payroll'),
				'pin_payroll' => data_get($apiSoftware, 'pin_payroll'),
				'identifier_eqdocs' => data_get($apiSoftware, 'identifier_eqdocs'),
				'pin_eqdocs' => data_get($apiSoftware, 'pin_eqdocs'),
				'url' => data_get($apiSoftware, 'url'),
				'url_payroll' => data_get($apiSoftware, 'url_payroll'),
				'url_eqdocs' => data_get($apiSoftware, 'url_eqdocs'),
				'api_response' => $response,
				'last_synced_at' => now(),
			]);

			$this->flashDebug();

			return redirect()->route('admin.companies.software.edit', $company)
				->with('success', 'Software configurado y sincronizado correctamente.');
		} catch (Throwable $e) {
			Log::error('Error al configurar software', [
				'error' => $e->getMessage(),
				'debug' => QimeraApiService::$lastDebug,
			]);

			$this->flashDebug();

			return redirect()->route('admin.companies.software.edit', $company)
				->with('error', 'Datos guardados localmente, pero falló la configuración: ' . $e->getMessage());
		}
	}
	
	public function editCertificate(Company $company)
	{
		$certificate = $company->certificate;
		return view('admin.companies.certificate', compact('company', 'certificate'));
	}

	public function updateCertificate(Request $request, Company $company, QimeraApiService $api)
	{
		if (empty($company->api_token)) {
			return back()->with('error', 'Esta empresa todavía no tiene un token generado. Sincronizá primero el paso 1.');
		}

		$request->validate([
			'certificate_file' => [
				'required',
				'file',
				'max:10240',
				function ($attribute, $value, $fail) {
					$ext = strtolower($value->getClientOriginalExtension());
					if (!in_array($ext, ['p12', 'pfx'])) {
						$fail('El archivo debe tener extensión .p12 o .pfx.');
					}
				},
			],
			'password' => 'required|string|max:255',
		], [
			'certificate_file.required' => 'Debés subir el archivo .p12 del certificado.',
			'certificate_file.max' => 'El archivo no puede superar los 10 MB.',
		]);

		// Leer el archivo y convertirlo a base64
		$file = $request->file('certificate_file');
		$base64 = base64_encode(file_get_contents($file->getRealPath()));
		$originalName = $file->getClientOriginalName();

		$certificate = $company->certificate()->updateOrCreate(
			['company_id' => $company->id],
			[
				'certificate' => $base64,
				'password' => $request->input('password'),
				'name' => $originalName, // se sobrescribe si el API devuelve otro
			]
		);

		try {
			$response = $api->configureCertificate(
				$company->api_token,
				$certificate->toApiPayload()
			);

			$apiCert = data_get($response, 'certificado', []);

			$certificate->update([
				'api_certificate_id' => data_get($apiCert, 'id'),
				'name' => data_get($apiCert, 'name', $originalName),
				'expiration_date' => $this->parseExpirationDate(data_get($apiCert, 'expiration_date')),
				'api_response' => $response,
				'last_synced_at' => now(),
			]);

			$this->flashDebug();

			return redirect()->route('admin.companies.certificate.edit', $company)
				->with('success', 'Certificado configurado correctamente.');
		} catch (Throwable $e) {
			Log::error('Error al configurar certificado', [
				'error' => $e->getMessage(),
				'debug' => QimeraApiService::$lastDebug,
			]);

			$this->flashDebug();

			return redirect()->route('admin.companies.certificate.edit', $company)
				->with('error', 'Datos guardados localmente, pero falló la configuración: ' . $e->getMessage());
		}
	}

	/**
	 * El API devuelve la fecha como "2027/02/17 08:42:37".
	 */
	protected function parseExpirationDate(?string $date): ?\Carbon\Carbon
	{
		if (empty($date)) {
			return null;
		}

		try {
			return \Carbon\Carbon::createFromFormat('Y/m/d H:i:s', $date);
		} catch (\Exception $e) {
			return \Carbon\Carbon::parse($date);
		}
	}
	
	protected function flashDebug(): void
	{
		if (!empty(QimeraApiService::$lastDebug)) {
			session()->flash('api_debug', QimeraApiService::$lastDebug);
		}
	}
}