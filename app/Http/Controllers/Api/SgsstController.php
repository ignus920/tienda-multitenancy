<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Central\VntCompany;
use App\Models\Central\VntContact;
use App\Models\Central\VntWarehouse;
use App\Services\Tenant\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SgsstController extends Controller
{
    public function __construct(private TenantManager $tenantManager) {}

    public function register(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'email'         => 'required|email|unique:central.users,email',
            'password'      => 'required|string|min:8|confirmed',
            'code_ciiu'     => 'nullable|string|max:50',
            'phone'         => 'nullable|string|max:50',
        ]);

        DB::connection('central')->beginTransaction();
        try {
            $company = VntCompany::create([
                'businessName' => $validated['business_name'],
                'billingEmail' => $validated['email'],
                'code_ciiu'    => $validated['code_ciiu'] ?? null,
                'status'       => 1,
                'typePerson'   => 'Juridica',
            ]);

            $warehouse = VntWarehouse::create([
                'companyId' => $company->id,
                'name'      => 'Principal',
                'address'   => 'Dirección principal',
                'status'    => true,
                'main'      => 1,
            ]);

            $contact = VntContact::create([
                'firstName'     => $validated['business_name'],
                'email'         => $validated['email'],
                'phone_contact' => $validated['phone'] ?? null,
                'warehouseId'   => $warehouse->id,
                'status'        => true,
            ]);

            $user = User::create([
                'name'       => $validated['business_name'],
                'email'      => $validated['email'],
                'phone'      => $validated['phone'] ?? null,
                'password'   => Hash::make($validated['password']),
                'contact_id' => $contact->id,
            ]);

            $tenant = $this->tenantManager->createTenantRecord([
                'name'       => $validated['business_name'],
                'email'      => $validated['email'],
                'company_id' => $company->id,
            ], $user);

            // Marcar setup completo para no interferir con el flujo de tienda-multitenancy
            $tenant->update(['database_setup' => true]);

            $token = $user->createToken('sgsst-app')->plainTextToken;

            DB::connection('central')->commit();

            return response()->json([
                'token'        => $token,
                'tenant_id'    => $tenant->id,
                'company_id'   => $company->id,
                'warehouse_id' => $warehouse->id,
                'user'         => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::connection('central')->rollBack();
            throw $e;
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        $tenant = $user->tenants()->wherePivot('is_active', true)->latest('last_accessed_at')->first();

        $token = $user->createToken('sgsst-app')->plainTextToken;

        return response()->json([
            'token'      => $token,
            'tenant_id'  => $tenant?->id,
            'company_id' => $tenant?->company_id,
            'user'       => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
