<?php

namespace App\Services\Tenant;

/**
 * Service para calcular retenciones fiscales colombianas
 * Implementa los cálculos de retención en la fuente, ICA e IVA
 */
class RetentionCalculatorService
{
    /**
     * Calcula retención en la fuente
     * Base mínima para 2026: $524,000 si el cliente es régimen común
     *
     * @param string $regimeDescription Descripción del régimen fiscal (COMMON_REGIME, SPECIAL_REGIME)
     * @param int $fiscalResponsability ID de responsabilidad fiscal
     * @param float $subTotal Subtotal de la factura
     * @return float Valor de retención calculado
     */
    public function calculateRetentionSource(string $regimeDescription, int $fiscalResponsability, float $subTotal): float
    {
        $retention = 0.00;
        $baseRetention = config('facturacion.retentions.base_amounts.fuente', 524000);
        $percentage = config('facturacion.retentions.percentages.fuente', 0.025);

        if ($subTotal >= $baseRetention && $regimeDescription && (
            $regimeDescription === "COMMON_REGIME" ||
            ($regimeDescription === "SPECIAL_REGIME" && $fiscalResponsability === 5)
        )) {
            // Calcular sin redondeo intermedio para máxima precisión
            $retention = $subTotal * $percentage;
        }

        // Usar redondeo específico para coincidir con Alegra
        // Puede ser que Alegra use banker's rounding o truncate
        return round($retention, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Calcula retención ICA
     * Base mínima para 2026: $1,418,800 si el cliente es régimen común y está en Bogotá
     *
     * @param string $city Ciudad del cliente
     * @param string $regimeDescription Descripción del régimen fiscal
     * @param float $subTotal Subtotal de la factura
     * @return float Valor de retención ICA calculado
     */
    public function calculateRetentionIca(string $city, string $regimeDescription, float $subTotal): float
    {
        $retention = 0.00;
        $baseRetention = config('facturacion.retentions.base_amounts.ica', 1418800);
        $percentage = config('facturacion.retentions.percentages.ica', 0.001104);
        $icaCities = config('facturacion.retentions.ica_cities', ['Bogotá, D.C.']);

        if ($subTotal >= $baseRetention && in_array($city, $icaCities) && $regimeDescription && (
            $regimeDescription === "COMMON_REGIME" ||
            $regimeDescription === "SPECIAL_REGIME"
        )) {
            // Calcular sin redondeo intermedio para máxima precisión
            $retention = $subTotal * $percentage;
        }

        // Usar redondeo específico para coincidir con Alegra
        // Puede ser que Alegra use banker's rounding o truncate
        return round($retention, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Calcula retención IVA
     * Solo aplica para grandes contribuyentes (responsabilidad fiscal 5)
     *
     * @param int $fiscalResponsability ID de responsabilidad fiscal
     * @param float $subTotal Subtotal de la factura
     * @return float Valor de retención IVA calculado
     */
    public function calculateRetentionIva(int $fiscalResponsability, float $subTotal): float
    {
        $retention = 0.00;
        $baseRetention = config('facturacion.retentions.base_amounts.iva', 300000);
        $percentage = config('facturacion.retentions.percentages.iva', 0.15);
        $validResponsibilities = config('facturacion.retentions.iva_fiscal_responsibilities', [5]);

        if ($subTotal >= $baseRetention && in_array($fiscalResponsability, $validResponsibilities)) {
            // Calcular sin redondeo intermedio para máxima precisión
            $retention = $subTotal * $percentage;
        }

        // Usar redondeo específico para coincidir con Alegra
        // Puede ser que Alegra use banker's rounding o truncate
        return round($retention, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Calcula todas las retenciones para una factura
     * Utiliza las bases mínimas oficiales de Colombia para 2026
     *
     * @param array $data Array con los datos necesarios para el cálculo
     * @return array Array con los valores de retenciones calculadas
     */
    public function calculateAllRetentions(array $data): array
    {
        $regimeDescription = $data['regime_description'] ?? '';
        $fiscalResponsability = $data['fiscal_responsability'] ?? 0;
        $city = $data['city'] ?? '';
        $subTotal = $data['sub_total'] ?? 0;

        return [
            'retention_fuente' => $this->calculateRetentionSource(
                $regimeDescription,
                $fiscalResponsability,
                $subTotal
            ),
            'retention_ica' => $this->calculateRetentionIca(
                $city,
                $regimeDescription,
                $subTotal
            ),
            'retention_iva' => $this->calculateRetentionIva(
                $fiscalResponsability,
                $subTotal
            ),
        ];
    }

    /**
     * Calcula el total de la factura aplicando las retenciones
     *
     * @param float $totalWithTaxes Total con impuestos
     * @param array $retentions Array con las retenciones calculadas
     * @return float Total final después de retenciones
     */
    public function calculateFinalTotal(float $totalWithTaxes, array $retentions): float
    {
        $finalTotal = $totalWithTaxes - $retentions['retention_fuente'] - $retentions['retention_ica'] - $retentions['retention_iva'];
        return round($finalTotal, 2);
    }
}