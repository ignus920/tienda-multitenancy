<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Auth\Tenant;
use App\Models\Auth\User;
use App\Services\Tenant\TenantManager;

abstract class TenantTestCase extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure 'tenant' connection to use in-memory SQLite for testing
        config(['database.connections.tenant' => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]]);

        // Purge cached connections so the new config is used
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Create tenant tables in the in-memory SQLite
        $this->setUpTenantSchema();

        // Create a fake Tenant record in the central DB (SQLite :memory:)
        $this->tenant = Tenant::factory()->create([
            'is_active' => true,
        ]);

        // Create a user and associate with tenant
        $this->user = User::factory()->create();
        $this->tenant->users()->attach($this->user->id, ['role' => 'admin', 'is_active' => true]);

        // Set session so components/services know which tenant to use
        session(['tenant_id' => $this->tenant->id]);

        // Act as the user
        $this->actingAs($this->user);

        // Mock TenantManager::setConnection to just set the 'tenant' connection config
        // (already done above - no real MySQL needed)
        $this->mockTenantManager();
    }

    protected function tearDown(): void
    {
        DB::purge('tenant');
        parent::tearDown();
    }

    /**
     * Creates the tenant database schema in SQLite :memory:
     */
    protected function setUpTenantSchema(): void
    {
        Schema::connection('tenant')->create('inv_item_brand', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('name', 100);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('tenant')->create('inv_categories', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('name', 255);
            $table->tinyInteger('status')->default(1)->nullable();
            $table->string('api_data_id', 60)->default('0');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('tenant')->create('inv_unit_measurements', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('description', 100)->nullable();
            $table->integer('quantity')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
        });

        Schema::connection('tenant')->create('inv_item_house', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('name', 100);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('tenant')->create('inv_command', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('name', 100);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('inv_items', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->integer('api_data_id')->nullable();
            $table->integer('categoryId');
            $table->string('name', 255);
            $table->string('internal_code', 100);
            $table->string('sku', 255);
            $table->text('description')->nullable();
            $table->enum('type', ['COMBO', 'COMPRA NACIONAL', 'IMPORTADO', 'PRODUCIDO']);
            $table->integer('taxId')->default(2);
            $table->integer('commandId')->nullable();
            $table->integer('brandId')->nullable();
            $table->integer('houseId')->nullable();
            $table->tinyInteger('inventoriable')->default(1);
            $table->integer('purchasing_unit')->nullable()->default(0);
            $table->integer('consumption_unit')->nullable()->default(0);
            $table->tinyInteger('handles_serial')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('generic')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('tenant')->create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('type')->default('natural');
            $table->boolean('active')->default(true);
            $table->string('api_data_id')->nullable();
            $table->string('business_name')->nullable();
            $table->string('identification_number')->nullable();
            $table->string('identification_type')->nullable();
            $table->string('dv')->nullable();
            $table->string('kind_of_person')->nullable();
            $table->string('regime')->nullable();
            $table->json('fiscal_responsibilities')->nullable();
            $table->string('postcode')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Mock TenantManager so ensureTenantConnection() doesn't try real MySQL
     */
    protected function mockTenantManager(): void
    {
        $this->instance(TenantManager::class, new class {
            public function setConnection($tenant): void
            {
                // Already configured as SQLite :memory: in setUp - nothing to do
            }
        });
    }
}
