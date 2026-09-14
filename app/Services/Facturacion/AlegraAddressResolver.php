<?php

namespace App\Services\Facturacion;

use App\Models\Central\CnfCity;

/**
 * Resuelve el nombre de ciudad/departamento que se envía a Alegra dentro del
 * objeto "address" de un contacto, aplicando el ajuste que pidió soporte de
 * Alegra para que el selector Municipio/Departamento quede vinculado en su
 * interfaz (de lo contrario el campo queda vacío aunque la API responda 200).
 *
 * Para Bogotá, Alegra exige la coma en el campo "city" ("Bogotá, D.C."),
 * NO en "department" (ese va tal cual está en nuestra BD: "Bogotá D.C.").
 *
 * Fuente única: antes esta lógica estaba duplicada en VntCompanyForm y
 * CustomerForm (Livewire); se centraliza aquí para que ambos formularios y
 * cualquier script de sincronización usen siempre el mismo criterio.
 */
class AlegraAddressResolver
{
    public static function resolve(?int $cityId): array
    {
        $cityName = 'Bogotá';
        $departmentName = 'Cundinamarca';

        if ($cityId) {
            try {
                $city = CnfCity::find($cityId);
                if ($city) {
                    $cityName = $city->name ?? $cityName;
                    $departmentName = $city->state->name ?? $departmentName;
                }
            } catch (\Exception $e) {
                // Se conservan los valores por defecto (Bogotá/Cundinamarca)
            }
        }

        return [
            'cityName' => self::normalizeCityNameForAlegra($cityName),
            'departmentName' => $departmentName,
        ];
    }

    public static function normalizeCityNameForAlegra(string $cityName): string
    {
        $map = [
            'Bogotá D.C.' => 'Bogotá, D.C.',
            'Bogota D.C.' => 'Bogotá, D.C.',
            'Bogotá' => 'Bogotá, D.C.',
        ];

        return $map[$cityName] ?? $cityName;
    }
}
