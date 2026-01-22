<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auth\User;
use App\Services\Company\CompanyDataValidator;
use App\Services\Configuration\CompanyConfigurationService;
use App\Models\Central\VntContact;
use App\Models\Central\VntCompany;

class DebugLoginFlow extends Command
{
    protected $signature = 'debug:login-flow {email}';
    protected $description = 'Debug the complete login flow for a specific user';

    public function handle()
    {
        $email = $this->argument('email');

        $this->info('🔍 Debuggeando flujo de login completo');
        $this->line('');

        try {
            // 1. Buscar el usuario
            $this->info("1️⃣ Buscando usuario: {$email}");
            $user = User::where('email', $email)->first();

            if (!$user) {
                $this->error("❌ Usuario no encontrado: {$email}");
                return;
            }

            $this->displayUserData($user);
            $this->line('');

            // 2. Obtener tenants del usuario
            $this->info("2️⃣ Obteniendo tenants del usuario");
            $this->displayUserTenants($user);
            $this->line('');

            // 3. Obtener company usando CompanyDataValidator
            $this->info("3️⃣ Obteniendo company usando CompanyDataValidator");
            $this->displayCompanyData($user);
            $this->line('');

            // 4. Obtener contacto del usuario
            $this->info("4️⃣ Obteniendo contacto del usuario");
            $this->displayContactData($user);
            $this->line('');

            // 5. Simular login y ver configuración completa
            $this->info("5️⃣ Simulando configuración completa");
            $this->simulateConfiguration($user);

        } catch (\Exception $e) {
            $this->error('❌ Error durante el debug: ' . $e->getMessage());
            $this->line('Trace: ' . $e->getTraceAsString());
        }
    }

    private function displayUserData($user)
    {
        $this->info("✅ Usuario encontrado:");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $user->id],
                ['Nombre', $user->name],
                ['Email', $user->email],
                ['Profile ID', $user->profile_id],
                ['Contact ID', $user->contact_id ?? 'NULL'],
                ['Creado', $user->created_at],
            ]
        );

        // Mostrar perfil si existe
        if ($user->profile) {
            $this->line("   📋 Perfil: {$user->profile->name} (alias: {$user->profile->alias})");
        }
    }

    private function displayUserTenants($user)
    {
        $tenants = $user->tenants;

        if ($tenants->isEmpty()) {
            $this->warn("⚠️  No se encontraron tenants para este usuario");
            return;
        }

        $this->info("📊 Tenants encontrados: " . $tenants->count());

        foreach ($tenants as $tenant) {
            $this->line("   🏢 Tenant: {$tenant->name}");
            $this->line("      ID: {$tenant->id}");
            $this->line("      DB Name: {$tenant->db_name}");
            $this->line("      Role: " . ($tenant->pivot->role ?? 'N/A'));
            $this->line("      Is Active: " . ($tenant->pivot->is_active ? 'YES' : 'NO'));
            $this->line('');

            // Extraer company_id del db_name
            if (preg_match('/company_(\d+)_/', $tenant->db_name, $matches)) {
                $this->line("      🔍 Company ID extraído: {$matches[1]}");
            } else {
                $this->line("      ❌ No se pudo extraer company_id del db_name");
            }
            $this->line('');
        }
    }

    private function displayCompanyData($user)
    {
        $validator = new CompanyDataValidator();
        $company = $validator->getUserCompany($user);

        if (!$company) {
            $this->error("❌ No se pudo obtener company para el usuario");
            return;
        }

        $this->info("✅ Company encontrada:");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $company->id],
                ['Business Name', $company->businessName ?? 'NULL'],
                ['First Name', $company->firstName ?? 'NULL'],
                ['Last Name', $company->lastName ?? 'NULL'],
                ['Identification', $company->identification ?? 'NULL'],
                ['Type Person', $company->typePerson ?? 'NULL'],
                ['Type Identification ID', $company->typeIdentificationId ?? 'NULL'],
                ['Regime ID', $company->regimeId ?? 'NULL'],
                ['Fiscal Responsability ID', $company->fiscalResponsabilityId ?? 'NULL'],
                ['Code CIIU', $company->code_ciiu ?? 'NULL'],
                ['Status', $company->status ?? 'NULL'],
            ]
        );
    }

    private function displayContactData($user)
    {
        // Buscar contacto por contact_id del usuario
        if ($user->contact_id) {
            $contact = VntContact::find($user->contact_id);
            if ($contact) {
                $this->info("✅ Contacto encontrado por contact_id:");
                $this->displayContact($contact);
                return;
            }
        }

        // Buscar contacto por email
        $contact = VntContact::where('email', $user->email)->first();
        if ($contact) {
            $this->info("✅ Contacto encontrado por email:");
            $this->displayContact($contact);
        } else {
            $this->warn("⚠️  No se encontró contacto para este usuario");
        }
    }

    private function displayContact($contact)
    {
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $contact->id],
                ['First Name', $contact->firstName ?? 'NULL'],
                ['Last Name', $contact->lastName ?? 'NULL'],
                ['Email', $contact->email],
                ['Phone', $contact->phone_contact ?? 'NULL'],
                ['Warehouse ID', $contact->warehouseId ?? 'NULL'],
                ['Position ID', $contact->positionId ?? 'NULL'],
                ['Status', $contact->status ? 'ACTIVE' : 'INACTIVE'],
            ]
        );

        // Si tiene warehouse, mostrar info del warehouse
        if ($contact->warehouseId && $contact->warehouse) {
            $warehouse = $contact->warehouse;
            $this->line('');
            $this->info("🏪 Información del Warehouse:");
            $this->line("   ID: {$warehouse->id}");
            $this->line("   Name: {$warehouse->name}");
            $address = $warehouse->address ? $warehouse->address : 'NULL';
            $this->line("   Address: {$address}");
            $companyId = $warehouse->companyId ? $warehouse->companyId : 'NULL';
            $this->line("   Company ID: {$companyId}");
        }
    }

    private function simulateConfiguration($user)
    {
        // Simular el mismo proceso que hace HasCompanyConfiguration
        $validator = new CompanyDataValidator();
        $company = $validator->getUserCompany($user);

        if (!$company) {
            $this->error("❌ No se puede simular configuración sin company");
            return;
        }

        $this->info("🔧 Configuración que se obtendría:");
        $this->line("   Company ID: {$company->id}");
        $this->line("   Plan ID: 2 (Por defecto)");

        // Buscar contacto para warehouse
        $contact = VntContact::where('email', $user->email)->first();
        if ($contact && $contact->warehouseId) {
            $this->line("   Warehouse ID: {$contact->warehouseId}");
        } else {
            $this->line("   Warehouse ID: NULL");
        }

        $this->line('');

        // Mostrar qué opciones estarían disponibles
        $this->info("📋 Opciones que se consultarían en cnf_company_options:");
        $this->line("   SELECT * FROM cnf_company_options WHERE company_id = {$company->id}");

        $this->line('');

        // Si hay warehouse, mostrar consulta de facturación
        if ($contact && $contact->warehouseId) {
            $this->info("💳 Configuración de facturación que se consultaría:");
            $this->line("   SELECT * FROM cnf_invoices WHERE id_warehouses = {$contact->warehouseId}");
        }
    }
}