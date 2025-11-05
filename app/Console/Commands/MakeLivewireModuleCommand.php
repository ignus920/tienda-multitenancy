<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeLivewireModuleCommand extends Command
{
    protected $signature = 'make:livewire-module {name} {--model=} {--migration} {--tenant} {--module=}';

    protected $description = 'Crea un módulo completo con Livewire, modelo, migración y vista en subcarpetas por módulo';

    public function handle()
    {
        $name = $this->argument('name');
        $modelName = $this->option('model') ?: Str::studly(Str::singular($name));
        $isTenant = $this->option('tenant');
        $moduleName = $this->option('module');

        // Si no se especifica módulo, inferirlo del nombre
        if (!$moduleName) {
            $moduleName = $this->inferModuleFromName($name);
        }

        $this->info("Creando módulo: {$name} en carpeta: {$moduleName}");

        // 1. Crear el modelo
        $this->createModel($modelName, $isTenant);

        // 2. Crear la migración si se solicita
        if ($this->option('migration')) {
            $this->createMigration($modelName);
        }

        // 3. Crear el componente Livewire
        $this->createLivewireComponent($name, $modelName, $isTenant, $moduleName);

        // 4. Crear la vista
        $this->createView($name, $isTenant, $moduleName);

        // 5. Mostrar las rutas sugeridas
        $this->showRoutes($name, $isTenant, $moduleName);

        $this->info("\n✅ Módulo {$name} creado exitosamente en {$moduleName}!");
    }

    private function inferModuleFromName($name)
    {
        // Inferir módulo basado en palabras clave comunes
        $name = strtolower($name);

        if (str_contains($name, 'product') || str_contains($name, 'catalog')) {
            return 'Products';
        } elseif (str_contains($name, 'sale') || str_contains($name, 'order') || str_contains($name, 'invoice')) {
            return 'Sales';
        } elseif (str_contains($name, 'customer') || str_contains($name, 'client')) {
            return 'Customers';
        } elseif (str_contains($name, 'inventory') || str_contains($name, 'stock')) {
            return 'Inventory';
        } elseif (str_contains($name, 'user') || str_contains($name, 'auth')) {
            return 'Users';
        } elseif (str_contains($name, 'config') || str_contains($name, 'setting')) {
            return 'Configuration';
        } elseif (str_contains($name, 'report') || str_contains($name, 'analytics')) {
            return 'Reports';
        } else {
            // Por defecto, usar la primera palabra en PascalCase
            $words = explode('-', $name);
            return Str::studly($words[0]);
        }
    }

    private function createModel($modelName, $isTenant)
    {
        $namespace = $isTenant ? 'App\\Models\\Tenant' : 'App\\Models\\Central';
        $directory = $isTenant ? 'app/Models/Tenant' : 'app/Models/Central';
        $connection = $isTenant ? 'tenant' : 'central';

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $modelContent = $this->getModelStub($modelName, $namespace, $connection);

        File::put("{$directory}/{$modelName}.php", $modelContent);

        $this->line("✓ Modelo creado: {$directory}/{$modelName}.php");
    }

    private function createMigration($modelName)
    {
        $tableName = Str::snake(Str::plural($modelName));
        $migrationName = "create_{$tableName}_table";

        $this->call('make:migration', [
            'name' => $migrationName,
            '--create' => $tableName
        ]);

        $this->line("✓ Migración creada para tabla: {$tableName}");
    }

    private function createLivewireComponent($name, $modelName, $isTenant, $moduleName)
    {
        // Determinar carpeta según tipo y módulo
        $subfolder = $isTenant ? 'Tenant' : 'Central';
        $livewireDir = "app/Livewire/{$subfolder}/{$moduleName}";

        if (!File::exists($livewireDir)) {
            File::makeDirectory($livewireDir, 0755, true);
        }

        $className = Str::studly($name);
        $namespace = "App\\Livewire\\{$subfolder}\\{$moduleName}";
        $componentContent = $this->getLivewireStub($className, $modelName, $namespace, $isTenant, $moduleName);

        File::put("{$livewireDir}/{$className}.php", $componentContent);

        $this->line("✓ Componente Livewire creado: {$livewireDir}/{$className}.php");
    }

    private function createView($name, $isTenant, $moduleName)
    {
        // Determinar carpeta según tipo y módulo
        $subfolder = $isTenant ? 'tenant' : 'central';
        $moduleFolder = Str::kebab($moduleName);
        $viewDir = "resources/views/livewire/{$subfolder}/{$moduleFolder}";
        $viewName = Str::kebab($name);

        if (!File::exists($viewDir)) {
            File::makeDirectory($viewDir, 0755, true);
        }

        $viewContent = $this->getViewStub($name);

        File::put("{$viewDir}/{$viewName}.blade.php", $viewContent);

        $this->line("✓ Vista creada: {$viewDir}/{$viewName}.blade.php");
    }

    private function showRoutes($name, $isTenant, $moduleName)
    {
        $routeName = Str::kebab($name);
        $componentName = Str::studly($name);
        $subfolder = $isTenant ? 'Tenant' : 'Central';

        $this->info("\n📋 Rutas sugeridas para routes/web.php:");
        $this->line("Route::get('/{$routeName}', App\\Livewire\\{$subfolder}\\{$moduleName}\\{$componentName}::class)->name('{$routeName}');");
    }

    private function getModelStub($modelName, $namespace, $connection)
    {
        return "<?php

namespace {$namespace};

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$modelName} extends Model
{
    use HasFactory;

    protected \$connection = '{$connection}';
    protected \$table = '" . Str::snake(Str::plural($modelName)) . "';

    protected \$fillable = [
        'name',
        'description',
        // Agregar campos según necesidad
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
";
    }

    private function getLivewireStub($className, $modelName, $namespace, $isTenant, $moduleName)
    {
        $modelType = $isTenant ? 'Tenant' : 'Central';
        $modelNamespace = "App\\Models\\{$modelType}\\{$modelName}";
        $subfolder = $isTenant ? 'tenant' : 'central';
        $moduleFolder = Str::kebab($moduleName);
        $viewPath = "livewire.{$subfolder}.{$moduleFolder}.";

        return "<?php

namespace {$namespace};

use Livewire\\Component;
use Livewire\\WithPagination;
use {$modelNamespace};

class {$className} extends Component
{
    use WithPagination;

    public \$search = '';
    public \$showModal = false;
    public \$editingId = null;
    public \$perPage = 10;
    public \$sortField = 'id';
    public \$sortDirection = 'desc';

    // Propiedades del formulario
    public \$name = '';
    public \$description = '';

    protected \$rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
    ];

    protected \$queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        \$this->resetPage();
    }

    public function updatingPerPage()
    {
        \$this->resetPage();
    }

    public function sortBy(\$field)
    {
        if (\$this->sortField === \$field) {
            \$this->sortDirection = \$this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            \$this->sortDirection = 'asc';
        }

        \$this->sortField = \$field;
        \$this->resetPage();
    }

    public function render()
    {
        \$items = {$modelName}::query()
            ->when(\$this->search, function (\$query) {
                \$query->where('name', 'like', '%' . \$this->search . '%')
                      ->orWhere('description', 'like', '%' . \$this->search . '%');
            })
            ->orderBy(\$this->sortField, \$this->sortDirection)
            ->paginate(\$this->perPage);

        return view('{$viewPath}" . Str::kebab($className) . "', [
            'items' => \$items
        ]);
    }

    public function create()
    {
        \$this->resetForm();
        \$this->showModal = true;
    }

    public function edit(\$id)
    {
        \$item = {$modelName}::findOrFail(\$id);
        \$this->editingId = \$id;
        \$this->name = \$item->name;
        \$this->description = \$item->description;
        \$this->showModal = true;
    }

    public function save()
    {
        \$this->validate();

        if (\$this->editingId) {
            \$item = {$modelName}::findOrFail(\$this->editingId);
            \$item->update([
                'name' => \$this->name,
                'description' => \$this->description,
            ]);

            session()->flash('message', 'Registro actualizado exitosamente.');
        } else {
            {$modelName}::create([
                'name' => \$this->name,
                'description' => \$this->description,
            ]);

            session()->flash('message', 'Registro creado exitosamente.');
        }

        \$this->resetForm();
        \$this->showModal = false;
    }

    public function delete(\$id)
    {
        {$modelName}::findOrFail(\$id)->delete();
        session()->flash('message', 'Registro eliminado exitosamente.');
    }

    public function exportExcel()
    {
        // TODO: Implementar exportación a Excel
        \$this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Exportación a Excel - En desarrollo'
        ]);
    }

    public function exportPdf()
    {
        // TODO: Implementar exportación a PDF
        \$this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Exportación a PDF - En desarrollo'
        ]);
    }

    public function exportCsv()
    {
        // TODO: Implementar exportación a CSV
        \$this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Exportación a CSV - En desarrollo'
        ]);
    }

    private function resetForm()
    {
        \$this->editingId = null;
        \$this->name = '';
        \$this->description = '';
        \$this->resetErrorBag();
    }
}
";
    }

    private function getViewStub($name)
    {
        return "<div class=\"min-h-screen bg-gray-50 dark:bg-gray-900 p-6\">
    <div class=\"max-w-7xl mx-auto\">
        <!-- Header -->
        <div class=\"bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6\">
            <div class=\"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4\">
                <div>
                    <h1 class=\"text-2xl font-bold text-gray-900 dark:text-white\">" . Str::title($name) . "</h1>
                    <p class=\"text-gray-600 dark:text-gray-400 mt-1\">Gestiona los registros de " . strtolower(Str::title($name)) . "</p>
                </div>
                <button wire:click=\"create\"
                        class=\"inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150\">
                    <svg class=\"w-4 h-4 mr-2\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 4v16m8-8H4\"></path>
                    </svg>
                    Crear Nuevo
                </button>
            </div>
        </div>

        <!-- Mensajes -->
        @if (session()->has('message'))
            <div class=\"bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg mb-6\">
                <div class=\"flex items-center\">
                    <svg class=\"w-5 h-5 mr-3\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\"></path>
                    </svg>
                    {{ session('message') }}
                </div>
            </div>
        @endif

        <!-- DataTable Card -->
        <div class=\"bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700\">
            <!-- Toolbar -->
            <div class=\"p-6 border-b border-gray-200 dark:border-gray-700\">
                <div class=\"flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4\">
                    <!-- Búsqueda -->
                    <div class=\"flex-1 max-w-md\">
                        <div class=\"relative\">
                            <div class=\"absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none\">
                                <svg class=\"h-5 w-5 text-gray-400 dark:text-gray-500\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z\"></path>
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms=\"search\"
                                   type=\"text\"
                                   placeholder=\"Buscar registros...\"
                                   class=\"block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500\">
                        </div>
                    </div>

                    <!-- Controles -->
                    <div class=\"flex items-center gap-3\">
                        <!-- Registros por página -->
                        <div class=\"flex items-center gap-2\">
                            <label class=\"text-sm text-gray-700 dark:text-gray-300\">Mostrar:</label>
                            <select wire:model.live=\"perPage\"
                                    class=\"border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500\">
                                <option value=\"5\">5</option>
                                <option value=\"10\">10</option>
                                <option value=\"25\">25</option>
                                <option value=\"50\">50</option>
                                <option value=\"100\">100</option>
                            </select>
                        </div>

                        <!-- Botones de exportar -->
                        <div class=\"flex items-center gap-2\">
                            <button wire:click=\"exportExcel\"
                                    class=\"inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm transition-colors\">
                                <svg class=\"w-4 h-4 mr-1\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                                    <path d=\"M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm6 10V6h6v8h-6z\"></path>
                                </svg>
                                Excel
                            </button>
                            <button wire:click=\"exportPdf\"
                                    class=\"inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm transition-colors\">
                                <svg class=\"w-4 h-4 mr-1\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                                    <path d=\"M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z\"></path>
                                </svg>
                                PDF
                            </button>
                            <button wire:click=\"exportCsv\"
                                    class=\"inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm transition-colors\">
                                <svg class=\"w-4 h-4 mr-1\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                                    <path d=\"M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L4.414 10H17a1 1 0 100-2H4.414l1.879-1.293z\"></path>
                                </svg>
                                CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class=\"overflow-x-auto\">
                <table class=\"min-w-full divide-y divide-gray-200 dark:divide-gray-700\">
                    <thead class=\"bg-gray-50 dark:bg-gray-900\">
                        <tr>
                            <th wire:click=\"sortBy('id')\"
                                class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 select-none\">
                                <div class=\"flex items-center gap-1\">
                                    ID
                                    @if(\$sortField === 'id')
                                        @if(\$sortDirection === 'asc')
                                            <svg class=\"w-3 h-3\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                                                <path d=\"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\"></path>
                                            </svg>
                                        @else
                                            <svg class=\"w-3 h-3\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                                                <path d=\"M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z\"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </div>
                            </th>
                            <th wire:click=\"sortBy('name')\"
                                class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 select-none\">
                                <div class=\"flex items-center gap-1\">
                                    Nombre
                                    @if(\$sortField === 'name')
                                        @if(\$sortDirection === 'asc')
                                            <svg class=\"w-3 h-3\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                                                <path d=\"M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z\"></path>
                                            </svg>
                                        @else
                                            <svg class=\"w-3 h-3\" fill=\"currentColor\" viewBox=\"0 0 20 20\">
                                                <path d=\"M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z\"></path>
                                            </svg>
                                        @endif
                                    @endif
                                </div>
                            </th>
                            <th class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider\">Descripción</th>
                            <th class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider\">Fecha</th>
                            <th class=\"px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider\">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class=\"bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700\">
                        @forelse(\$items as \$item)
                            <tr class=\"hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors\">
                                <td class=\"px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white\">
                                    #{{ \$item->id }}
                                </td>
                                <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white\">
                                    {{ \$item->name }}
                                </td>
                                <td class=\"px-6 py-4 text-sm text-gray-500 dark:text-gray-400\">
                                    {{ \$item->description ?? 'Sin descripción' }}
                                </td>
                                <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400\">
                                    {{ \$item->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class=\"px-6 py-4 whitespace-nowrap text-center text-sm font-medium\">
                                    <div class=\"flex items-center justify-center gap-2\">
                                        <button wire:click=\"edit({{ \$item->id }})\"
                                                class=\"inline-flex items-center px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 text-xs font-medium rounded-full hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-colors\">
                                            <svg class=\"w-3 h-3 mr-1\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z\"></path>
                                            </svg>
                                            Editar
                                        </button>
                                        <button wire:click=\"delete({{ \$item->id }})\"
                                                wire:confirm=\"¿Estás seguro de eliminar este registro?\"
                                                class=\"inline-flex items-center px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 text-xs font-medium rounded-full hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors\">
                                            <svg class=\"w-3 h-3 mr-1\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16\"></path>
                                            </svg>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan=\"5\" class=\"px-6 py-12 text-center text-gray-500 dark:text-gray-400\">
                                    <div class=\"flex flex-col items-center\">
                                        <svg class=\"w-12 h-12 mb-4 text-gray-400 dark:text-gray-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4\"></path>
                                        </svg>
                                        <p class=\"text-lg font-medium\">No se encontraron registros</p>
                                        <p class=\"text-sm\">{{ \$search ? 'Intenta ajustar tu búsqueda' : 'Comienza creando un nuevo registro' }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if(\$items->hasPages())
                <div class=\"bg-white dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700 rounded-b-lg\">
                    <div class=\"flex items-center justify-between\">
                        <div class=\"text-sm text-gray-700 dark:text-gray-300\">
                            Mostrando {{ \$items->firstItem() }} a {{ \$items->lastItem() }} de {{ \$items->total() }} resultados
                        </div>
                        <div>
                            {{ \$items->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal -->
    @if(\$showModal)
        <div class=\"fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50\"
             x-data=\"{ show: true }\"
             x-show=\"show\"
             x-transition:enter=\"ease-out duration-300\"
             x-transition:enter-start=\"opacity-0\"
             x-transition:enter-end=\"opacity-100\"
             x-transition:leave=\"ease-in duration-200\"
             x-transition:leave-start=\"opacity-100\"
             x-transition:leave-end=\"opacity-0\">
            <div class=\"relative min-h-screen flex items-center justify-center p-4\">
                <div class=\"relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full\"
                     x-transition:enter=\"ease-out duration-300\"
                     x-transition:enter-start=\"opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95\"
                     x-transition:enter-end=\"opacity-100 translate-y-0 sm:scale-100\"
                     x-transition:leave=\"ease-in duration-200\"
                     x-transition:leave-start=\"opacity-100 translate-y-0 sm:scale-100\"
                     x-transition:leave-end=\"opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95\">

                    <!-- Header -->
                    <div class=\"border-b border-gray-200 dark:border-gray-700 px-6 py-4\">
                        <h3 class=\"text-lg font-semibold text-gray-900 dark:text-white\">
                            {{ \$editingId ? 'Editar' : 'Crear' }} Registro
                        </h3>
                    </div>

                    <!-- Form -->
                    <form wire:submit=\"save\" class=\"p-6 space-y-4\">
                        <div>
                            <label class=\"block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2\">
                                Nombre <span class=\"text-red-500\">*</span>
                            </label>
                            <input wire:model=\"name\"
                                   type=\"text\"
                                   class=\"w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500\"
                                   placeholder=\"Ingresa el nombre\">
                            @error('name')
                                <p class=\"mt-1 text-sm text-red-600 dark:text-red-400\">{{ \$message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class=\"block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2\">
                                Descripción
                            </label>
                            <textarea wire:model=\"description\"
                                      rows=\"3\"
                                      class=\"w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500\"
                                      placeholder=\"Descripción opcional\"></textarea>
                            @error('description')
                                <p class=\"mt-1 text-sm text-red-600 dark:text-red-400\">{{ \$message }}</p>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class=\"flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700\">
                            <button type=\"button\"
                                    wire:click=\"\$set('showModal', false)\"
                                    class=\"inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-sm transition-colors\">
                                Cancelar
                            </button>
                            <button type=\"submit\"
                                    wire:loading.attr=\"disabled\"
                                    class=\"inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed border border-transparent rounded-lg font-medium text-sm text-white transition-colors\">
                                <span wire:loading.remove>{{ \$editingId ? 'Actualizar' : 'Crear' }}</span>
                                <span wire:loading class=\"flex items-center\">
                                    <svg class=\"animate-spin -ml-1 mr-2 h-4 w-4 text-white\" xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\">
                                        <circle class=\"opacity-25\" cx=\"12\" cy=\"12\" r=\"10\" stroke=\"currentColor\" stroke-width=\"4\"></circle>
                                        <path class=\"opacity-75\" fill=\"currentColor\" d=\"M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z\"></path>
                                    </svg>
                                    Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
";
    }
}