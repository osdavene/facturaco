<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra el Plan Único de Cuentas (PUC) estándar colombiano.
 * empresa_id = NULL → cuentas compartidas por todas las empresas.
 * Solo inserta si la tabla está vacía (idempotente).
 */
class PucSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('plan_cuentas')->whereNull('empresa_id')->exists()) {
            $this->command->info('PUC ya sembrado, omitiendo.');
            return;
        }

        $ahora = now();

        // [codigo, nombre, tipo, naturaleza, nivel, padre_codigo]
        $cuentas = [
            // ── CLASE 1 — ACTIVO ──────────────────────────────────
            ['1',       'ACTIVO',                                   'activo',     'debito', 1, null],
            ['11',      'DISPONIBLE',                               'activo',     'debito', 2, '1'],
            ['1105',    'Caja',                                     'activo',     'debito', 3, '11'],
            ['110505',  'Caja general',                             'activo',     'debito', 4, '1105'],
            ['1110',    'Bancos',                                    'activo',     'debito', 3, '11'],
            ['111005',  'Bancos nacionales',                        'activo',     'debito', 4, '1110'],
            ['13',      'DEUDORES',                                 'activo',     'debito', 2, '1'],
            ['1305',    'Clientes',                                 'activo',     'debito', 3, '13'],
            ['130505',  'Clientes nacionales',                      'activo',     'debito', 4, '1305'],
            ['1330',    'Anticipos y avances',                      'activo',     'debito', 3, '13'],
            ['133005',  'A proveedores',                            'activo',     'debito', 4, '1330'],
            ['1355',    'Anticipo de impuestos y contribuciones',   'activo',     'debito', 3, '13'],
            ['135515',  'Retención en la fuente',                   'activo',     'debito', 4, '1355'],
            ['135517',  'Impuesto a las ventas retenido',           'activo',     'debito', 4, '1355'],
            ['14',      'INVENTARIOS',                              'activo',     'debito', 2, '1'],
            ['1435',    'Mercancías no fabricadas por la empresa',   'activo',     'debito', 3, '14'],
            ['143505',  'Costo de mercancías',                      'activo',     'debito', 4, '1435'],
            ['15',      'PROPIEDADES PLANTA Y EQUIPO',              'activo',     'debito', 2, '1'],
            ['1524',    'Equipo de oficina',                        'activo',     'debito', 3, '15'],
            ['152405',  'Muebles y enseres',                        'activo',     'debito', 4, '1524'],
            ['1528',    'Equipo de cómputo y comunicación',         'activo',     'debito', 3, '15'],
            ['152805',  'Equipo de procesamiento de datos',         'activo',     'debito', 4, '1528'],

            // ── CLASE 2 — PASIVO ──────────────────────────────────
            ['2',       'PASIVO',                                   'pasivo',     'credito', 1, null],
            ['21',      'OBLIGACIONES FINANCIERAS',                 'pasivo',     'credito', 2, '2'],
            ['2105',    'Bancos nacionales',                        'pasivo',     'credito', 3, '21'],
            ['210505',  'Sobregiros',                               'pasivo',     'credito', 4, '2105'],
            ['22',      'PROVEEDORES',                              'pasivo',     'credito', 2, '2'],
            ['2205',    'Proveedores nacionales',                   'pasivo',     'credito', 3, '22'],
            ['220505',  'Proveedores nacionales',                   'pasivo',     'credito', 4, '2205'],
            ['23',      'CUENTAS POR PAGAR',                        'pasivo',     'credito', 2, '2'],
            ['2335',    'Costos y gastos por pagar',                'pasivo',     'credito', 3, '23'],
            ['233505',  'Costos y gastos por pagar',                'pasivo',     'credito', 4, '2335'],
            ['24',      'IMPUESTOS GRAVÁMENES Y TASAS',             'pasivo',     'credito', 2, '2'],
            ['2404',    'De renta y complementarios',               'pasivo',     'credito', 3, '24'],
            ['240405',  'Vigencia fiscal corriente',                'pasivo',     'credito', 4, '2404'],
            ['2408',    'IVA por pagar',                            'pasivo',     'credito', 3, '24'],
            ['240805',  'IVA generado',                             'pasivo',     'credito', 4, '2408'],
            ['240810',  'IVA descontable',                          'activo',     'debito',  4, '2408'],
            ['25',      'OBLIGACIONES LABORALES',                   'pasivo',     'credito', 2, '2'],
            ['2510',    'Cesantías consolidadas',                   'pasivo',     'credito', 3, '25'],
            ['251005',  'Cesantías',                                'pasivo',     'credito', 4, '2510'],
            ['2515',    'Intereses sobre cesantías',                'pasivo',     'credito', 3, '25'],
            ['251505',  'Intereses sobre cesantías',                'pasivo',     'credito', 4, '2515'],
            ['2525',    'Prima de servicios',                       'pasivo',     'credito', 3, '25'],
            ['252505',  'Prima de servicios',                       'pasivo',     'credito', 4, '2525'],
            ['2530',    'Vacaciones consolidadas',                  'pasivo',     'credito', 3, '25'],
            ['253005',  'Vacaciones',                               'pasivo',     'credito', 4, '2530'],
            ['26',      'PASIVOS ESTIMADOS Y PROVISIONES',          'pasivo',     'credito', 2, '2'],
            ['2610',    'Para obligaciones laborales',              'pasivo',     'credito', 3, '26'],
            ['261005',  'Provisión prestaciones sociales',          'pasivo',     'credito', 4, '2610'],
            ['27',      'RETENCIONES Y APORTES NÓMINA',            'pasivo',     'credito', 2, '2'],
            ['2365',    'Retención en la fuente',                   'pasivo',     'credito', 3, '23'],
            ['236505',  'Retención en la fuente',                   'pasivo',     'credito', 4, '2365'],
            ['2368',    'Impuesto de industria y comercio ret.',    'pasivo',     'credito', 3, '23'],
            ['236805',  'ReteICA',                                  'pasivo',     'credito', 4, '2368'],
            ['2369',    'Impuesto a las ventas retenido (ReteIVA)', 'pasivo',     'credito', 3, '23'],
            ['236905',  'ReteIVA',                                  'pasivo',     'credito', 4, '2369'],

            // ── CLASE 3 — PATRIMONIO ──────────────────────────────
            ['3',       'PATRIMONIO',                               'patrimonio', 'credito', 1, null],
            ['31',      'CAPITAL SOCIAL',                           'patrimonio', 'credito', 2, '3'],
            ['3105',    'Capital suscrito y pagado',                'patrimonio', 'credito', 3, '31'],
            ['310505',  'Capital suscrito',                         'patrimonio', 'credito', 4, '3105'],
            ['36',      'RESULTADOS DEL EJERCICIO',                 'patrimonio', 'credito', 2, '3'],
            ['3605',    'Utilidad del ejercicio',                   'patrimonio', 'credito', 3, '36'],
            ['360505',  'Utilidad del ejercicio',                   'patrimonio', 'credito', 4, '3605'],
            ['3610',    'Pérdida del ejercicio',                    'patrimonio', 'debito',  3, '36'],
            ['361005',  'Pérdida del ejercicio',                    'patrimonio', 'debito',  4, '3610'],
            ['37',      'RESULTADOS DE EJERCICIOS ANTERIORES',      'patrimonio', 'credito', 2, '3'],
            ['3705',    'Utilidades acumuladas',                    'patrimonio', 'credito', 3, '37'],
            ['370505',  'Utilidades acumuladas',                    'patrimonio', 'credito', 4, '3705'],

            // ── CLASE 4 — INGRESOS ────────────────────────────────
            ['4',       'INGRESOS',                                 'ingreso',   'credito', 1, null],
            ['41',      'INGRESOS OPERACIONALES',                   'ingreso',   'credito', 2, '4'],
            ['4135',    'Comercio al por mayor y al por menor',     'ingreso',   'credito', 3, '41'],
            ['413505',  'Ingresos por ventas de mercancías',        'ingreso',   'credito', 4, '4135'],
            ['4155',    'Servicios',                                'ingreso',   'credito', 3, '41'],
            ['415505',  'Ingresos por servicios',                   'ingreso',   'credito', 4, '4155'],
            ['4175',    'Honorarios',                               'ingreso',   'credito', 3, '41'],
            ['417505',  'Honorarios',                               'ingreso',   'credito', 4, '4175'],
            ['42',      'INGRESOS NO OPERACIONALES',                'ingreso',   'credito', 2, '4'],
            ['4210',    'Financieros',                              'ingreso',   'credito', 3, '42'],
            ['421005',  'Intereses',                                'ingreso',   'credito', 4, '4210'],
            ['4295',    'Diversos',                                 'ingreso',   'credito', 3, '42'],
            ['429505',  'Otros ingresos',                           'ingreso',   'credito', 4, '4295'],

            // ── CLASE 5 — GASTOS ──────────────────────────────────
            ['5',       'GASTOS',                                   'gasto',     'debito',  1, null],
            ['51',      'GASTOS OPERACIONALES DE ADMINISTRACIÓN',   'gasto',     'debito',  2, '5'],
            ['5105',    'Gastos de personal',                       'gasto',     'debito',  3, '51'],
            ['510506',  'Sueldos',                                  'gasto',     'debito',  4, '5105'],
            ['510527',  'Auxilio de transporte',                    'gasto',     'debito',  4, '5105'],
            ['510530',  'Cesantías',                                'gasto',     'debito',  4, '5105'],
            ['510533',  'Intereses sobre cesantías',                'gasto',     'debito',  4, '5105'],
            ['510536',  'Prima de servicios',                       'gasto',     'debito',  4, '5105'],
            ['510539',  'Vacaciones',                               'gasto',     'debito',  4, '5105'],
            ['510545',  'Aportes a EPS (empleador)',                'gasto',     'debito',  4, '5105'],
            ['510548',  'Aportes a pensión (empleador)',            'gasto',     'debito',  4, '5105'],
            ['510551',  'ARL',                                      'gasto',     'debito',  4, '5105'],
            ['510554',  'Caja de compensación',                     'gasto',     'debito',  4, '5105'],
            ['5120',    'Arrendamientos',                           'gasto',     'debito',  3, '51'],
            ['512005',  'Arrendamiento de inmuebles',               'gasto',     'debito',  4, '5120'],
            ['5135',    'Servicios',                                'gasto',     'debito',  3, '51'],
            ['513505',  'Aseo y vigilancia',                        'gasto',     'debito',  4, '5135'],
            ['513510',  'Temporales',                               'gasto',     'debito',  4, '5135'],
            ['5140',    'Gastos legales',                           'gasto',     'debito',  3, '51'],
            ['514005',  'Notariales',                               'gasto',     'debito',  4, '5140'],
            ['5145',    'Mantenimiento y reparaciones',             'gasto',     'debito',  3, '51'],
            ['514505',  'Construcciones y edificaciones',           'gasto',     'debito',  4, '5145'],
            ['5155',    'Depreciaciones',                           'gasto',     'debito',  3, '51'],
            ['515510',  'Depreciación equipo de oficina',           'gasto',     'debito',  4, '5155'],
            ['5160',    'Amortizaciones',                           'gasto',     'debito',  3, '51'],
            ['516005',  'De intangibles',                           'gasto',     'debito',  4, '5160'],
            ['5195',    'Diversos',                                 'gasto',     'debito',  3, '51'],
            ['519505',  'Elementos de aseo y cafetería',            'gasto',     'debito',  4, '5195'],
            ['519510',  'Útiles y papelería',                       'gasto',     'debito',  4, '5195'],
            ['519520',  'Publicidad',                               'gasto',     'debito',  4, '5195'],
            ['52',      'GASTOS OPERACIONALES DE VENTAS',           'gasto',     'debito',  2, '5'],
            ['5205',    'Gastos de personal ventas',                'gasto',     'debito',  3, '52'],
            ['520506',  'Sueldos ventas',                           'gasto',     'debito',  4, '5205'],
            ['5230',    'Publicidad y propaganda',                  'gasto',     'debito',  3, '52'],
            ['523005',  'Publicidad',                               'gasto',     'debito',  4, '5230'],
            ['53',      'GASTOS NO OPERACIONALES',                  'gasto',     'debito',  2, '5'],
            ['5305',    'Financieros',                              'gasto',     'debito',  3, '53'],
            ['530505',  'Gastos bancarios',                         'gasto',     'debito',  4, '5305'],
            ['530510',  'Intereses',                                'gasto',     'debito',  4, '5305'],

            // ── CLASE 6 — COSTOS DE VENTAS ────────────────────────
            ['6',       'COSTOS DE VENTAS',                         'costo',     'debito',  1, null],
            ['61',      'COSTOS DE VENTAS Y DE PRESTACIÓN DE SERV.','costo',     'debito',  2, '6'],
            ['6135',    'Comercio al por mayor y al por menor',     'costo',     'debito',  3, '61'],
            ['613505',  'Costo de mercancías vendidas',             'costo',     'debito',  4, '6135'],
            ['6155',    'Servicios',                                'costo',     'debito',  3, '61'],
            ['615505',  'Costo de servicios prestados',             'costo',     'debito',  4, '6155'],
        ];

        // Construir mapa codigo → id para referencias de padres
        $mapa = [];
        $rows = [];

        foreach ($cuentas as [$codigo, $nombre, $tipo, $naturaleza, $nivel, $padreCodigo]) {
            $rows[] = [
                'empresa_id'          => null,
                'codigo'              => $codigo,
                'nombre'              => $nombre,
                'tipo'                => $tipo,
                'naturaleza'          => $naturaleza,
                'nivel'               => $nivel,
                'cuenta_padre_id'     => null, // se actualiza en segundo paso
                'acepta_movimientos'  => ($nivel >= 3), // solo cuentas y subcuentas
                'activo'              => true,
                'created_at'          => $ahora,
                'updated_at'          => $ahora,
            ];
        }

        // Insertar en lotes (sin padre aún)
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('plan_cuentas')->insert($chunk);
        }

        // Actualizar cuenta_padre_id
        $todas = DB::table('plan_cuentas')->whereNull('empresa_id')->get()->keyBy('codigo');

        foreach ($cuentas as [$codigo, , , , , $padreCodigo]) {
            if ($padreCodigo && isset($todas[$padreCodigo])) {
                DB::table('plan_cuentas')
                    ->where('codigo', $codigo)
                    ->whereNull('empresa_id')
                    ->update(['cuenta_padre_id' => $todas[$padreCodigo]->id]);
            }
        }

        // Marcar cuentas padre como no movibles (tienen hijas)
        DB::table('plan_cuentas')
            ->whereNull('empresa_id')
            ->whereIn('codigo', array_column(
                array_filter($cuentas, fn($c) => $c[5] !== null),
                4  // índice del padreCodigo en cada sub-array
            ))
            ->update(['acepta_movimientos' => false]);

        $this->command->info('✅ PUC Colombia sembrado: ' . count($cuentas) . ' cuentas.');
    }
}
