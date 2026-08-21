<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\CotizacionItem;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\ReciboCaja;
use App\Models\Remision;
use App\Models\RemisionItem;
use App\Models\UnidadMedida;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::first();
        if (!$empresa) {
            $empresa = Empresa::create([
                'razon_social'       => 'MUNDO VIRTUAL PC S.A.S.',
                'nombre_comercial'   => 'Mundo Virtual PC',
                'nit'                => '901458720',
                'digito_verificacion'=> '1',
                'tipo_persona'       => 'juridica',
                'regimen'            => 'responsable_iva',
                'email'              => 'contacto@mundovirtualpc.com',
                'telefono'           => '6047811056',
                'pais'               => 'Colombia',
                'departamento'       => 'Córdoba',
                'municipio'          => 'Montería',
                'direccion'          => 'Cra 4 # 28-45 Centro',
                'prefijo_factura'    => 'FE',
                'consecutivo_desde'  => 1,
                'consecutivo_hasta'  => 99999,
                'consecutivo_actual' => 0,
                'moneda'             => 'COP',
                'iva_defecto'        => 19.00,
            ]);
        }

        // Usuario principal
        $user = User::where('is_superadmin', false)->first() ?? User::first();
        if (!$user) {
            $user = User::create([
                'name'          => 'Oscar Vega',
                'email'         => 'oscar@facturaco.com',
                'password'      => Hash::make('Password2026*'),
                'is_superadmin' => false,
            ]);
        }

        if (!$empresa->usuarios()->where('users.id', $user->id)->exists()) {
            $empresa->usuarios()->attach($user->id, ['rol' => 'propietario', 'activo' => true]);
        }

        $userId = $user->id;
        $empresaId = $empresa->id;

        // Limpiar datos transaccionales previos para evitar duplicados
        DB::table('factura_items')->whereIn('factura_id', Factura::where('empresa_id', $empresaId)->pluck('id'))->delete();
        DB::table('facturas')->where('empresa_id', $empresaId)->delete();
        DB::table('recibos_caja')->where('empresa_id', $empresaId)->delete();
        DB::table('cotizacion_items')->whereIn('cotizacion_id', Cotizacion::where('empresa_id', $empresaId)->pluck('id'))->delete();
        DB::table('cotizaciones')->where('empresa_id', $empresaId)->delete();
        DB::table('remision_items')->whereIn('remision_id', Remision::where('empresa_id', $empresaId)->pluck('id'))->delete();
        DB::table('remisiones')->where('empresa_id', $empresaId)->delete();
        DB::table('orden_compra_items')->whereIn('orden_compra_id', OrdenCompra::where('empresa_id', $empresaId)->pluck('id'))->delete();
        DB::table('ordenes_compra')->where('empresa_id', $empresaId)->delete();

        // ── 1. UNIDADES DE MEDIDA ─────────────────────────────────────────
        $unidadesData = [
            ['abreviatura' => 'UN',  'nombre' => 'Unidad'],
            ['abreviatura' => 'CJ',  'nombre' => 'Caja'],
            ['abreviatura' => 'SRV', 'nombre' => 'Servicio'],
            ['abreviatura' => 'MTR', 'nombre' => 'Metro'],
            ['abreviatura' => 'HRA', 'nombre' => 'Hora'],
            ['abreviatura' => 'KIT', 'nombre' => 'Kit'],
        ];
        $unidadesMap = [];
        foreach ($unidadesData as $u) {
            $unidadesMap[$u['abreviatura']] = UnidadMedida::firstOrCreate(
                ['empresa_id' => $empresaId, 'abreviatura' => $u['abreviatura']],
                ['nombre' => $u['nombre'], 'activo' => true]
            );
        }

        // ── 2. CATEGORÍAS ─────────────────────────────────────────────────
        $catsData = [
            ['nombre' => 'Portátiles y Computadores'],
            ['nombre' => 'Accesorios y Periféricos'],
            ['nombre' => 'Almacenamiento y Memorias'],
            ['nombre' => 'Redes y Conectividad'],
            ['nombre' => 'Servicios y Mantenimiento'],
            ['nombre' => 'Software y Licencias'],
        ];
        $cats = [];
        foreach ($catsData as $c) {
            $cats[$c['nombre']] = Categoria::firstOrCreate(
                ['empresa_id' => $empresaId, 'nombre' => $c['nombre']],
                ['activo' => true]
            );
        }

        // ── 3. PRODUCTOS ──────────────────────────────────────────────────
        $prodsData = [
            [
                'codigo' => 'LAP-001', 'codigo_barras' => '770100100001', 'nombre' => 'Portátil Lenovo IdeaPad 3 Core i5 16GB 512GB SSD',
                'categoria' => 'Portátiles y Computadores', 'unidad' => 'UN', 'precio_compra' => 2100000, 'precio_venta' => 2650000,
                'precio_venta2' => 2580000, 'stock_minimo' => 2, 'stock_actual' => 8, 'iva_pct' => 19, 'es_servicio' => false,
            ],
            [
                'codigo' => 'LAP-002', 'codigo_barras' => '770100100002', 'nombre' => 'Portátil HP Pavilion 15 Ryzen 7 16GB 1TB SSD',
                'categoria' => 'Portátiles y Computadores', 'unidad' => 'UN', 'precio_compra' => 2800000, 'precio_venta' => 3450000,
                'precio_venta2' => 3350000, 'stock_minimo' => 2, 'stock_actual' => 5, 'iva_pct' => 19, 'es_servicio' => false,
            ],
            [
                'codigo' => 'MON-001', 'codigo_barras' => '770100100003', 'nombre' => 'Monitor LG 24" IPS Full HD 75Hz HDMI/VGA',
                'categoria' => 'Accesorios y Periféricos', 'unidad' => 'UN', 'precio_compra' => 450000, 'precio_venta' => 590000,
                'precio_venta2' => 560000, 'stock_minimo' => 4, 'stock_actual' => 14, 'iva_pct' => 19, 'es_servicio' => false,
            ],
            [
                'codigo' => 'MON-002', 'codigo_barras' => '770100100004', 'nombre' => 'Monitor Samsung 27" Curvo FHD 100Hz',
                'categoria' => 'Accesorios y Periféricos', 'unidad' => 'UN', 'precio_compra' => 620000, 'precio_venta' => 799000,
                'precio_venta2' => 760000, 'stock_minimo' => 3, 'stock_actual' => 7, 'iva_pct' => 19, 'es_servicio' => false,
            ],
            [
                'codigo' => 'TEC-001', 'codigo_barras' => '770100100005', 'nombre' => 'Teclado Mecánico Redragon Kumara K552 RGB',
                'categoria' => 'Accesorios y Periféricos', 'unidad' => 'UN', 'precio_compra' => 125000, 'precio_venta' => 185000,
                'precio_venta2' => 175000, 'stock_minimo' => 5, 'stock_actual' => 22, 'iva_pct' => 19, 'es_servicio' => false,
            ],
            [
                'codigo' => 'MOU-001', 'codigo_barras' => '770100100006', 'nombre' => 'Mouse Inalámbrico Logitech M185 USB Óptico',
                'categoria' => 'Accesorios y Periféricos', 'unidad' => 'UN', 'precio_compra' => 38000, 'precio_venta' => 58000,
                'precio_venta2' => 52000, 'stock_minimo' => 10, 'stock_actual' => 35, 'iva_pct' => 19, 'es_servicio' => false,
            ],
            [
                'codigo' => 'SSD-001', 'codigo_barras' => '770100100007', 'nombre' => 'Disco Sólido Kingston NV2 1TB PCIe 4.0 NVMe M.2',
                'categoria' => 'Almacenamiento y Memorias', 'unidad' => 'UN', 'precio_compra' => 240000, 'precio_venta' => 325000,
                'precio_venta2' => 310000, 'stock_minimo' => 5, 'stock_actual' => 19, 'iva_pct' => 19, 'es_servicio' => false,
            ],
            [
                'codigo' => 'RAM-001', 'codigo_barras' => '770100100008', 'nombre' => 'Memoria RAM DDR4 16GB Kingston Fury Beast 3200MHz',
                'categoria' => 'Almacenamiento y Memorias', 'unidad' => 'UN', 'precio_compra' => 150000, 'precio_venta' => 210000,
                'precio_venta2' => 198000, 'stock_minimo' => 6, 'stock_actual' => 18, 'iva_pct' => 19, 'es_servicio' => false,
            ],
            [
                'codigo' => 'RED-001', 'codigo_barras' => '770100100009', 'nombre' => 'Router TP-Link Archer AX12 Wi-Fi 6 Dual Band Gigabit',
                'categoria' => 'Redes y Conectividad', 'unidad' => 'UN', 'precio_compra' => 165000, 'precio_venta' => 235000,
                'precio_venta2' => 220000, 'stock_minimo' => 4, 'stock_actual' => 11, 'iva_pct' => 19, 'es_servicio' => false,
            ],
            [
                'codigo' => 'RED-002', 'codigo_barras' => '770100100010', 'nombre' => 'Bobina Cable UTP Cat6 305m Nexxt Interior 100% Cobre',
                'categoria' => 'Redes y Conectividad', 'unidad' => 'CJ', 'precio_compra' => 380000, 'precio_venta' => 510000,
                'precio_venta2' => 485000, 'stock_minimo' => 2, 'stock_actual' => 4, 'iva_pct' => 19, 'es_servicio' => false,
            ],
            [
                'codigo' => 'SRV-001', 'codigo_barras' => null, 'nombre' => 'Mantenimiento Preventivo y Limpieza de Computador / Portátil',
                'categoria' => 'Servicios y Mantenimiento', 'unidad' => 'SRV', 'precio_compra' => 0, 'precio_venta' => 85000,
                'precio_venta2' => 75000, 'stock_minimo' => 0, 'stock_actual' => 999, 'iva_pct' => 19, 'es_servicio' => true,
            ],
            [
                'codigo' => 'SRV-002', 'codigo_barras' => null, 'nombre' => 'Instalación y Configuración de Redes LAN y Cámaras de Seguridad (x Punto)',
                'categoria' => 'Servicios y Mantenimiento', 'unidad' => 'SRV', 'precio_compra' => 0, 'precio_venta' => 120000,
                'precio_venta2' => 105000, 'stock_minimo' => 0, 'stock_actual' => 999, 'iva_pct' => 19, 'es_servicio' => true,
            ],
            [
                'codigo' => 'LIC-001', 'codigo_barras' => null, 'nombre' => 'Licencia Microsoft 365 Business Standard 1 Año (x Usuario)',
                'categoria' => 'Software y Licencias', 'unidad' => 'UN', 'precio_compra' => 540000, 'precio_venta' => 690000,
                'precio_venta2' => 660000, 'stock_minimo' => 0, 'stock_actual' => 50, 'iva_pct' => 19, 'es_servicio' => true,
            ],
        ];

        $productos = [];
        foreach ($prodsData as $p) {
            $catId = $cats[$p['categoria']]->id ?? null;
            $uniId = $unidadesMap[$p['unidad']]->id ?? null;

            $productos[$p['codigo']] = Producto::updateOrCreate(
                ['empresa_id' => $empresaId, 'codigo' => $p['codigo']],
                [
                    'categoria_id'     => $catId,
                    'unidad_medida_id' => $uniId,
                    'codigo_barras'    => $p['codigo_barras'],
                    'nombre'           => $p['nombre'],
                    'descripcion'      => $p['nombre'],
                    'precio_compra'    => $p['precio_compra'],
                    'precio_venta'     => $p['precio_venta'],
                    'precio_venta2'    => $p['precio_venta2'],
                    'stock_minimo'     => $p['stock_minimo'],
                    'stock_actual'     => $p['stock_actual'],
                    'iva_pct'          => $p['iva_pct'],
                    'incluye_iva'      => true,
                    'es_servicio'      => $p['es_servicio'],
                    'activo'           => true,
                ]
            );
        }

        // ── 4. CLIENTES ───────────────────────────────────────────────────
        $clientesData = [
            [
                'tipo_persona' => 'juridica', 'tipo_documento' => 'NIT', 'numero_documento' => '900584120',
                'digito_verificacion' => '4', 'razon_social' => 'INVERSIONES AGROPECUARIAS DEL SINÚ S.A.S.',
                'email' => 'compras@agrosinu.com.co', 'telefono' => '3004589632',
                'departamento' => 'Córdoba', 'municipio' => 'Montería', 'direccion' => 'Calle 41 # 14-20',
                'plazo_pago' => 30, 'cupo_credito' => 15000000,
            ],
            [
                'tipo_persona' => 'juridica', 'tipo_documento' => 'NIT', 'numero_documento' => '812004589',
                'digito_verificacion' => '8', 'razon_social' => 'CLÍNICA ODONTOLÓGICA MONTERÍA LTDA',
                'email' => 'administracion@odontomonteria.com', 'telefono' => '3128965412',
                'departamento' => 'Córdoba', 'municipio' => 'Montería', 'direccion' => 'Cra 6 # 65-12 El Recreo',
                'plazo_pago' => 15, 'cupo_credito' => 8000000,
            ],
            [
                'tipo_persona' => 'juridica', 'tipo_documento' => 'NIT', 'numero_documento' => '901235689',
                'digito_verificacion' => '2', 'razon_social' => 'CONSTRUCTORA Y CONSULTORÍA DEL CARIBE S.A.S.',
                'email' => 'contabilidad@construcaribe.co', 'telefono' => '3157841256',
                'departamento' => 'Córdoba', 'municipio' => 'Cereté', 'direccion' => 'Av. Santander # 12-40',
                'plazo_pago' => 30, 'cupo_credito' => 20000000,
            ],
            [
                'tipo_persona' => 'natural', 'tipo_documento' => 'CC', 'numero_documento' => '1067894512',
                'digito_verificacion' => null, 'razon_social' => 'CARLOS ANDRÉS MARTÍNEZ RIVERA',
                'nombres' => 'Carlos Andrés', 'apellidos' => 'Martínez Rivera', 'email' => 'carlos.martinez@gmail.com',
                'telefono' => '3012547896', 'departamento' => 'Córdoba', 'municipio' => 'Montería',
                'direccion' => 'Barrio Pasatiempo Mz 4 Lote 12', 'plazo_pago' => 0, 'cupo_credito' => 2000000,
            ],
            [
                'tipo_persona' => 'natural', 'tipo_documento' => 'CC', 'numero_documento' => '1065489723',
                'digito_verificacion' => null, 'razon_social' => 'MARÍA CAMILA ESPINOSA DUARTE',
                'nombres' => 'María Camila', 'apellidos' => 'Espinosa Duarte', 'email' => 'camila.espinosa@hotmail.com',
                'telefono' => '3205698741', 'departamento' => 'Córdoba', 'municipio' => 'Sahagún',
                'direccion' => 'Calle Central # 8-15', 'plazo_pago' => 0, 'cupo_credito' => 1500000,
            ],
            [
                'tipo_persona' => 'natural', 'tipo_documento' => 'CC', 'numero_documento' => '222222222',
                'digito_verificacion' => null, 'razon_social' => 'CONSUMIDOR FINAL',
                'nombres' => 'Consumidor', 'apellidos' => 'Final', 'email' => 'consumidor@final.co',
                'telefono' => '3000000000', 'departamento' => 'Córdoba', 'municipio' => 'Montería',
                'direccion' => 'Mostrador', 'plazo_pago' => 0, 'cupo_credito' => 0,
            ],
        ];

        $clientes = [];
        foreach ($clientesData as $c) {
            $clientes[$c['numero_documento']] = Cliente::updateOrCreate(
                ['empresa_id' => $empresaId, 'numero_documento' => $c['numero_documento']],
                array_merge($c, ['empresa_id' => $empresaId, 'activo' => true])
            );
        }

        // ── 5. PROVEEDORES ────────────────────────────────────────────────
        $provsData = [
            [
                'tipo_documento' => 'NIT', 'numero_documento' => '860012356',
                'digito_verificacion' => '7', 'razon_social' => 'MPS MAYORISTA DE COLOMBIA S.A.',
                'nombre_contacto' => 'Andrés Gómez', 'email' => 'ventas@mps.com.co', 'telefono' => '6013289000',
                'departamento' => 'Bogotá D.C.', 'municipio' => 'Bogotá', 'direccion' => 'Autopista Norte # 198-45',
            ],
            [
                'tipo_documento' => 'NIT', 'numero_documento' => '800159753',
                'digito_verificacion' => '3', 'razon_social' => 'IMPRESISTEM S.A.S.',
                'nombre_contacto' => 'Diana Morales', 'email' => 'contacto@impresistem.com', 'telefono' => '6017441000',
                'departamento' => 'Bogotá D.C.', 'municipio' => 'Bogotá', 'direccion' => 'Calle 26 # 69D-91',
            ],
            [
                'tipo_documento' => 'NIT', 'numero_documento' => '890901234',
                'digito_verificacion' => '9', 'razon_social' => 'MAKROCOMPUTO DE LA COSTA S.A.S.',
                'nombre_contacto' => 'Javier Herrera', 'email' => 'pedidos@makrocomputo.com', 'telefono' => '6053852000',
                'departamento' => 'Atlántico', 'municipio' => 'Barranquilla', 'direccion' => 'Vía 40 # 73-290',
            ],
        ];
        $proveedores = [];
        foreach ($provsData as $pr) {
            $proveedores[$pr['numero_documento']] = Proveedor::updateOrCreate(
                ['empresa_id' => $empresaId, 'numero_documento' => $pr['numero_documento']],
                array_merge($pr, ['empresa_id' => $empresaId, 'activo' => true])
            );
        }

        // ── 6. FACTURAS HISTÓRICAS Y DEL MES ACTUAL ───────────────────────
        $facturasData = [
            ['cli' => '900584120', 'dias_atras' => 140, 'items' => [['LAP-001', 2], ['MON-001', 2], ['MOU-001', 2]], 'estado' => 'pagada', 'forma' => 'transferencia'],
            ['cli' => '1067894512', 'dias_atras' => 135, 'items' => [['TEC-001', 1], ['MOU-001', 1]], 'estado' => 'pagada', 'forma' => 'efectivo'],
            ['cli' => '812004589', 'dias_atras' => 110, 'items' => [['LAP-002', 1], ['MON-002', 1], ['SRV-001', 1]], 'estado' => 'pagada', 'forma' => 'transferencia'],
            ['cli' => '901235689', 'dias_atras' => 105, 'items' => [['RED-002', 2], ['SRV-002', 4], ['RED-001', 1]], 'estado' => 'pagada', 'forma' => 'credito'],
            ['cli' => '1065489723', 'dias_atras' => 80, 'items' => [['SSD-001', 1], ['RAM-001', 2], ['SRV-001', 1]], 'estado' => 'pagada', 'forma' => 'nequi'],
            ['cli' => '900584120', 'dias_atras' => 75, 'items' => [['LIC-001', 5], ['SRV-002', 2]], 'estado' => 'pagada', 'forma' => 'transferencia'],
            ['cli' => '812004589', 'dias_atras' => 50, 'items' => [['MON-001', 3], ['TEC-001', 3], ['MOU-001', 3]], 'estado' => 'pagada', 'forma' => 'credito'],
            ['cli' => '222222222', 'dias_atras' => 45, 'items' => [['MOU-001', 2], ['TEC-001', 1]], 'estado' => 'pagada', 'forma' => 'efectivo'],
            ['cli' => '901235689', 'dias_atras' => 25, 'items' => [['LAP-001', 3], ['MON-002', 3], ['LIC-001', 3]], 'estado' => 'pagada', 'forma' => 'credito'],
            ['cli' => '1067894512', 'dias_atras' => 20, 'items' => [['SSD-001', 1], ['RAM-001', 1]], 'estado' => 'pagada', 'forma' => 'nequi'],
            ['cli' => '900584120', 'dias_atras' => 8, 'items' => [['LAP-002', 2], ['MON-002', 2], ['RED-001', 2]], 'estado' => 'emitida', 'forma' => 'credito'],
            ['cli' => '812004589', 'dias_atras' => 4, 'items' => [['SRV-001', 5], ['RAM-001', 4]], 'estado' => 'emitida', 'forma' => 'credito'],
            ['cli' => '1065489723', 'dias_atras' => 2, 'items' => [['TEC-001', 1], ['MOU-001', 1], ['SSD-001', 1]], 'estado' => 'pagada', 'forma' => 'efectivo'],
            ['cli' => '222222222', 'dias_atras' => 1, 'items' => [['MOU-001', 1], ['SRV-001', 1]], 'estado' => 'pagada', 'forma' => 'efectivo'],
            ['cli' => '901235689', 'dias_atras' => 35, 'items' => [['RED-002', 1], ['SRV-002', 2]], 'estado' => 'vencida', 'forma' => 'credito'],
        ];

        $consecutivoNum = 1001;
        foreach ($facturasData as $fData) {
            $cliente = $clientes[$fData['cli']];
            $fecha = Carbon::today()->subDays($fData['dias_atras']);
            $vence = $fData['forma'] === 'credito' ? $fecha->copy()->addDays(30) : $fecha->copy();

            $subtotalFactura = 0;
            $ivaFactura = 0;
            $itemsPreparados = [];

            foreach ($fData['items'] as [$codProd, $cant]) {
                $prod = $productos[$codProd];
                $precio = (float) $prod->precio_venta;
                $ivaPct = (float) $prod->iva_pct;

                $baseUnit = round($precio / (1 + ($ivaPct / 100)), 2);
                $subtotalItem = round($baseUnit * $cant, 2);
                $ivaItem = round($subtotalItem * ($ivaPct / 100), 2);
                $totalItem = $subtotalItem + $ivaItem;

                $subtotalFactura += $subtotalItem;
                $ivaFactura += $ivaItem;

                $itemsPreparados[] = [
                    'producto_id'     => $prod->id,
                    'codigo'          => $prod->codigo,
                    'descripcion'     => $prod->nombre,
                    'unidad'          => $unidadesData[0]['abreviatura'],
                    'cantidad'        => $cant,
                    'precio_unitario' => $baseUnit,
                    'descuento_pct'   => 0,
                    'descuento'       => 0,
                    'subtotal'        => $subtotalItem,
                    'iva_pct'         => $ivaPct,
                    'iva'             => $ivaItem,
                    'total'           => $totalItem,
                ];
            }

            $totalFactura = $subtotalFactura + $ivaFactura;
            $totalPagado = $fData['estado'] === 'pagada' ? $totalFactura : 0;

            $factura = Factura::create([
                'empresa_id'        => $empresaId,
                'numero'            => 'FE-' . $consecutivoNum,
                'prefijo'           => 'FE',
                'consecutivo'       => $consecutivoNum,
                'tipo'              => 'factura',
                'cliente_id'        => $cliente->id,
                'cliente_nombre'    => $cliente->razon_social ?: ($cliente->nombres . ' ' . $cliente->apellidos),
                'cliente_documento' => $cliente->tipo_documento . ': ' . $cliente->numero_documento,
                'cliente_email'     => $cliente->email,
                'cliente_direccion' => $cliente->direccion,
                'fecha_emision'     => $fecha,
                'hora_emision'      => '10:30:00',
                'fecha_vencimiento' => $vence,
                'subtotal'          => $subtotalFactura,
                'descuento'         => 0,
                'base_iva'          => $subtotalFactura,
                'iva'               => $ivaFactura,
                'retefuente'        => 0,
                'reteiva'           => 0,
                'reteica'           => 0,
                'total'             => $totalFactura,
                'total_pagado'      => $totalPagado,
                'estado'            => $fData['estado'],
                'forma_pago'        => $fData['forma'],
                'plazo_pago'        => $fData['forma'] === 'credito' ? 30 : 0,
                'observaciones'     => 'Venta comercial demostrativa',
                'user_id'           => $userId,
                'created_at'        => $fecha,
                'updated_at'        => $fecha,
            ]);

            foreach ($itemsPreparados as $it) {
                FacturaItem::create(array_merge($it, [
                    'factura_id' => $factura->id,
                    'orden'      => 1,
                ]));
            }

            if ($fData['estado'] === 'pagada') {
                ReciboCaja::create([
                    'empresa_id'        => $empresaId,
                    'numero'            => 'RC-' . date('Y') . '-' . str_pad($consecutivoNum, 4, '0', STR_PAD_LEFT),
                    'consecutivo'       => $consecutivoNum,
                    'factura_id'        => $factura->id,
                    'cliente_id'        => $cliente->id,
                    'cliente_nombre'    => $factura->cliente_nombre,
                    'cliente_documento' => $factura->cliente_documento,
                    'fecha'             => $fecha,
                    'valor'             => $totalFactura,
                    'forma_pago'        => $fData['forma'],
                    'num_referencia'    => 'TR-' . rand(100000, 999999),
                    'concepto'          => 'Pago de Factura ' . $factura->numero,
                    'observaciones'     => 'Comprobante de ingreso registrado',
                    'estado'            => 'activo',
                    'user_id'           => $userId,
                    'created_at'        => $fecha,
                ]);
            }

            $consecutivoNum++;
        }

        $empresa->update(['consecutivo_actual' => $consecutivoNum - 1]);

        // ── 7. COTIZACIONES ───────────────────────────────────────────────
        $cotizacionesData = [
            [
                'cli' => '900584120', 'estado' => 'enviada', 'items' => [['LAP-001', 5], ['MON-001', 5], ['MOU-001', 5]],
                'dias_atras' => 3, 'validez' => 15,
            ],
            [
                'cli' => '812004589', 'estado' => 'aceptada', 'items' => [['SSD-001', 4], ['RAM-001', 4], ['SRV-001', 4]],
                'dias_atras' => 6, 'validez' => 30,
            ],
            [
                'cli' => '901235689', 'estado' => 'borrador', 'items' => [['RED-002', 3], ['RED-001', 2], ['SRV-002', 6]],
                'dias_atras' => 1, 'validez' => 15,
            ],
        ];

        $cotConsecutivo = 101;
        foreach ($cotizacionesData as $cData) {
            $cliente = $clientes[$cData['cli']];
            $fecha = Carbon::today()->subDays($cData['dias_atras']);
            $vence = $fecha->copy()->addDays($cData['validez']);

            $subtotalCot = 0;
            $ivaCot = 0;
            $itemsCot = [];

            foreach ($cData['items'] as [$codProd, $cant]) {
                $prod = $productos[$codProd];
                $precio = (float) $prod->precio_venta;
                $ivaPct = (float) $prod->iva_pct;
                $baseUnit = round($precio / (1 + ($ivaPct / 100)), 2);
                $subtotalItem = round($baseUnit * $cant, 2);
                $ivaItem = round($subtotalItem * ($ivaPct / 100), 2);

                $subtotalCot += $subtotalItem;
                $ivaCot += $ivaItem;

                $itemsCot[] = [
                    'producto_id'     => $prod->id,
                    'codigo'          => $prod->codigo,
                    'descripcion'     => $prod->nombre,
                    'unidad'          => 'UN',
                    'cantidad'        => $cant,
                    'precio_unitario' => $baseUnit,
                    'descuento_pct'   => 0,
                    'descuento'       => 0,
                    'subtotal'        => $subtotalItem,
                    'iva_pct'         => $ivaPct,
                    'iva'             => $ivaItem,
                    'total'           => $subtotalItem + $ivaItem,
                ];
            }

            $cot = Cotizacion::create([
                'empresa_id'        => $empresaId,
                'numero'            => 'COT-' . $cotConsecutivo,
                'consecutivo'       => $cotConsecutivo,
                'cliente_id'        => $cliente->id,
                'cliente_nombre'    => $cliente->razon_social ?: ($cliente->nombres . ' ' . $cliente->apellidos),
                'cliente_documento' => $cliente->tipo_documento . ': ' . $cliente->numero_documento,
                'cliente_email'     => $cliente->email,
                'cliente_direccion' => $cliente->direccion,
                'fecha_emision'     => $fecha,
                'fecha_vencimiento' => $vence,
                'subtotal'          => $subtotalCot,
                'descuento'         => 0,
                'iva'               => $ivaCot,
                'total'             => $subtotalCot + $ivaCot,
                'forma_pago'        => 'credito',
                'plazo_pago'        => 30,
                'estado'            => $cData['estado'],
                'observaciones'     => 'Cotización de renovación tecnológica',
                'user_id'           => $userId,
                'created_at'        => $fecha,
            ]);

            foreach ($itemsCot as $it) {
                CotizacionItem::create(array_merge($it, ['cotizacion_id' => $cot->id]));
            }
            $cotConsecutivo++;
        }

        // ── 8. REMISIONES ─────────────────────────────────────────────────
        $remData = [
            [
                'cli' => '900584120', 'estado' => 'entregada', 'items' => [['MON-001', 2], ['TEC-001', 2]],
                'dias_atras' => 5,
            ],
            [
                'cli' => '812004589', 'estado' => 'enviada', 'items' => [['RAM-001', 2], ['SSD-001', 1]],
                'dias_atras' => 2,
            ],
        ];

        $remConsecutivo = 501;
        foreach ($remData as $rData) {
            $cliente = $clientes[$rData['cli']];
            $fecha = Carbon::today()->subDays($rData['dias_atras']);

            $rem = Remision::create([
                'empresa_id'        => $empresaId,
                'numero'            => 'REM-' . $remConsecutivo,
                'consecutivo'       => $remConsecutivo,
                'cliente_id'        => $cliente->id,
                'cliente_nombre'    => $cliente->razon_social ?: ($cliente->nombres . ' ' . $cliente->apellidos),
                'cliente_documento' => $cliente->tipo_documento . ': ' . $cliente->numero_documento,
                'cliente_email'     => $cliente->email,
                'cliente_direccion' => $cliente->direccion,
                'fecha_emision'     => $fecha,
                'fecha_entrega'     => $fecha->copy()->addDays(1),
                'estado'            => $rData['estado'],
                'observaciones'     => 'Entrega para instalación técnica en sede principal',
                'user_id'           => $userId,
                'created_at'        => $fecha,
            ]);

            foreach ($rData['items'] as [$codProd, $cant]) {
                $prod = $productos[$codProd];
                RemisionItem::create([
                    'remision_id'     => $rem->id,
                    'producto_id'     => $prod->id,
                    'codigo'          => $prod->codigo,
                    'descripcion'     => $prod->nombre,
                    'unidad'          => 'UN',
                    'cantidad'        => $cant,
                    'precio_unitario' => $prod->precio_venta,
                    'total'           => $prod->precio_venta * $cant,
                ]);
            }
            $remConsecutivo++;
        }

        // ── 9. ÓRDENES DE COMPRA A PROVEEDORES ────────────────────────────
        $ordenesData = [
            [
                'prov' => '860012356', 'estado' => 'recibida', 'dias_atras' => 20,
                'items' => [['LAP-001', 5], ['MON-001', 10], ['TEC-001', 15]],
            ],
            [
                'prov' => '800159753', 'estado' => 'aprobada', 'dias_atras' => 5,
                'items' => [['SSD-001', 10], ['RAM-001', 10]],
            ],
        ];

        $ocConsecutivo = 301;
        foreach ($ordenesData as $oData) {
            $prov = $proveedores[$oData['prov']];
            $fecha = Carbon::today()->subDays($oData['dias_atras']);

            $subtotalOc = 0;
            $itemsOc = [];
            foreach ($oData['items'] as [$codProd, $cant]) {
                $prod = $productos[$codProd];
                $costo = (float) $prod->precio_compra;
                $tot = $costo * $cant;
                $subtotalOc += $tot;

                $itemsOc[] = [
                    'producto_id'     => $prod->id,
                    'codigo'          => $prod->codigo,
                    'descripcion'     => $prod->nombre,
                    'unidad'          => 'UN',
                    'cantidad'        => $cant,
                    'precio_unitario' => $costo,
                    'subtotal'        => $tot,
                    'iva_pct'         => 19,
                    'iva'             => $tot * 0.19,
                    'total'           => $tot * 1.19,
                ];
            }
            $ivaOc = $subtotalOc * 0.19;

            $oc = OrdenCompra::create([
                'empresa_id'       => $empresaId,
                'numero'           => 'OC-' . $ocConsecutivo,
                'consecutivo'      => $ocConsecutivo,
                'proveedor_id'     => $prov->id,
                'proveedor_nombre' => $prov->razon_social,
                'proveedor_documento' => $prov->tipo_documento . ': ' . $prov->numero_documento,
                'fecha_emision'    => $fecha,
                'fecha_esperada'   => $fecha->copy()->addDays(5),
                'subtotal'         => $subtotalOc,
                'iva'              => $ivaOc,
                'total'            => $subtotalOc + $ivaOc,
                'estado'           => $oData['estado'],
                'observaciones'    => 'Reposición de stock para alta rotación',
                'user_id'          => $userId,
                'created_at'       => $fecha,
            ]);

            foreach ($itemsOc as $it) {
                OrdenCompraItem::create(array_merge($it, ['orden_compra_id' => $oc->id]));
            }
            $ocConsecutivo++;
        }

        // ── 10. EMPLEADOS ─────────────────────────────────────────────────
        $empleadosData = [
            [
                'tipo_documento' => 'CC', 'numero_documento' => '1067852369', 'nombres' => 'Andrés Felipe',
                'apellidos' => 'Suárez Polo', 'cargo' => 'Ingeniero de Sistemas / Soporte', 'salario_base' => 2800000,
                'tipo_contrato' => 'indefinido', 'fecha_ingreso' => '2025-01-15',
                'eps' => 'SURA', 'afp' => 'Porvenir', 'nivel_riesgo_arl' => 1,
            ],
            [
                'tipo_documento' => 'CC', 'numero_documento' => '1068963258', 'nombres' => 'Laura Vanessa',
                'apellidos' => 'Gómez Cogollo', 'cargo' => 'Asesora Comercial / Cajera', 'salario_base' => 1423500,
                'tipo_contrato' => 'indefinido', 'fecha_ingreso' => '2025-03-01',
                'eps' => 'Sanitas', 'afp' => 'Protección', 'nivel_riesgo_arl' => 1,
            ],
            [
                'tipo_documento' => 'CC', 'numero_documento' => '1064789521', 'nombres' => 'Jhonatan José',
                'apellidos' => 'Díaz Hoyos', 'cargo' => 'Técnico de Mantenimiento y Redes', 'salario_base' => 1600000,
                'tipo_contrato' => 'fijo', 'fecha_ingreso' => '2025-06-01',
                'eps' => 'Nueva EPS', 'afp' => 'Colfondos', 'nivel_riesgo_arl' => 2,
            ],
        ];

        foreach ($empleadosData as $emp) {
            Empleado::updateOrCreate(
                ['empresa_id' => $empresaId, 'numero_documento' => $emp['numero_documento']],
                array_merge($emp, ['empresa_id' => $empresaId, 'activo' => true])
            );
        }

        $this->command->info("¡Base de datos cargada con éxito para {$empresa->razon_social}!");
    }
}
