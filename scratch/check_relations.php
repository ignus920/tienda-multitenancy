use App\Models\Tenant\Sales\VntReturn;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;

// Inicializar tenant para pruebas (asumiendo el que el usuario está usando)
$tenant = Tenant::where('id', '8fb35c7f-b3b6-4e6b-b240-a4acefb1ab9a')->first();
$tenantManager = app(TenantManager::class);
$tenantManager->setConnection($tenant);
tenancy()->initialize($tenant);

try {
    $return = VntReturn::with(['remission.quote.customer', 'item'])->first();
    
    if ($return) {
        dd([
            'item_attributes' => $return->item ? $return->item->getAttributes() : 'null',
            'customer_name' => $return->remission && $return->remission->quote ? $return->remission->quote->customer_name : 'no quote',
        ]);
    } else {
        echo "No returns found";
    }
} catch (\Exception $e) {
        \Log::info("📊 RESULTADOS DIAGNOSTICO: No returns found");
        echo "No returns found";
    }
} catch (\Exception $e) {
    \Log::error("📊 ERROR DIAGNOSTICO: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
