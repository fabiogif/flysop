<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Idempotente (upsert pelo código IBGE): rodar de novo só atualiza nomes/coordenadas. */
class BrazilianLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $states = array_map(fn ($r) => [
            'id' => (int) $r['codigo_uf'],
            'uf' => $r['uf'],
            'name' => $r['nome'],
            'region' => $r['regiao'],
        ], $this->readCsv('estados.csv'));
        DB::table('states')->upsert($states, ['id'], ['uf', 'name', 'region']);

        $cities = array_map(fn ($r) => [
            'id' => (int) $r['codigo_ibge'],
            'state_id' => (int) $r['codigo_uf'],
            'name' => $r['nome'],
            'latitude' => $r['latitude'],
            'longitude' => $r['longitude'],
            'capital' => $r['capital'] === '1',
        ], $this->readCsv('municipios.csv'));
        foreach (array_chunk($cities, 500) as $chunk) {
            DB::table('cities')->upsert($chunk, ['id'], ['state_id', 'name', 'latitude', 'longitude', 'capital']);
        }
    }

    /** @return array<int, array<string, string>> */
    private function readCsv(string $file): array
    {
        $handle = fopen(database_path("data/ibge/{$file}"), 'r');
        $header = fgetcsv($handle);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]); // BOM do estados.csv
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if ($row !== [null]) {
                $rows[] = array_combine($header, $row);
            }
        }
        fclose($handle);

        return $rows;
    }
}
