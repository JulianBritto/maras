@php
    $user = auth()->user();

    $productos = $productos ?? collect();
    $catalogo = $catalogo ?? collect();
    $ventas = $ventas ?? collect();
    $kpis = $kpis ?? [
        'total_productos' => 0,
        'stock_total' => 0,
        'total_facturas' => 0,
        'ingresos_totales' => 0,
    ];
    $ultimosVendidos = $ultimosVendidos ?? collect();
    $ultimasFacturas = $ultimasFacturas ?? collect();
    $bajoStock = $bajoStock ?? collect();

    $catalogoJson = $catalogo
        ->map(function ($p) {
            return [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'price' => (int) $p->price,
                'stock' => (int) $p->stock,
            ];
        })
        ->values();
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <style>
        :root {
            color-scheme: light;

            --bg: #f6eee6;
            --paper: #ffffff;
            --border: #e5e7eb;
            --text: #111827;
            --muted: #6b7280;

            --brand-0: #b45309;
            --brand-1: #dc2626;

            --tab: #6b7280;
            --tab-active: #2563eb;
            --tab-line: rgba(37, 99, 235, 0.75);

            --kpi-0: #1e3a8a;
            --kpi-1: #1d4ed8;
            --kpi-2: #0b2a7a;

            --radius: 12px;
            --shadow: 0 10px 18px rgba(17, 24, 39, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .topbar {
            background: linear-gradient(90deg, var(--brand-0), var(--brand-1));
            color: #fff;
            box-shadow: var(--shadow);
        }

        .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 18px;
        }

        .topbar-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            letter-spacing: 0.2px;
        }

        .brand-title {
            font-size: 18px;
            line-height: 1.1;
        }

        .brand-sub {
            display: block;
            font-size: 12px;
            opacity: 0.9;
            font-weight: 700;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: right;
        }

        .user-meta {
            font-size: 12px;
            line-height: 1.1;
            opacity: 0.95;
        }

        .btn-logout {
            appearance: none;
            border: 1px solid rgba(255,255,255,0.25);
            background: rgba(255,255,255,0.18);
            color: #fff;
            font-weight: 800;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
        }

        .tabs {
            background: var(--paper);
            border-bottom: 1px solid var(--border);
        }

        .tabs-row {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 10px 0 0;
            overflow-x: auto;
        }

        .tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 6px;
            color: var(--tab);
            text-decoration: none;
            font-weight: 800;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
        }

        .tab svg { width: 16px; height: 16px; opacity: 0.95; }

        .tab.active {
            color: var(--tab-active);
            border-bottom-color: var(--tab-line);
        }

        .main {
            padding: 18px 0 36px;
        }

        .panel {
            background: var(--paper);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 12px;
        }

        /* Secciones por hash */
        .hash-section[hidden] { display: none; }

        /* Catálogo (como la referencia) */
        .products {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            padding: 6px;
        }

        .product {
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 10px 18px rgba(17, 24, 39, 0.06);
            display: flex;
            flex-direction: column;
        }

        .product-media {
            padding: 10px;
            background: rgba(17, 24, 39, 0.04);
        }

        .product img {
            display: block;
            width: 100%;
            height: 140px;
            object-fit: contain;
            background: rgba(17, 24, 39, 0.06);
            border-radius: 10px;
        }

        .product-body {
            padding: 10px 10px 0;
        }

        .product-name {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
            color: #111827;
            line-height: 1.15;
            min-height: 34px;
        }

        .product-badge-row {
            display: flex;
            justify-content: flex-end;
            margin: 10px 10px 0;
        }

        .product-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-weight: 900;
            font-size: 11px;
            background: #fbbf24;
            color: #b91c1c;
        }

        .product-footer {
            margin-top: auto;
            padding: 12px 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .product-price {
            font-size: 16px;
            font-weight: 900;
            color: #111827;
        }

        .product-add {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 0;
            background: #9d174d;
            color: #fff;
            font-weight: 900;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 14px;
            z-index: 60;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal {
            width: min(560px, 92vw);
            background: var(--paper);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
            overflow: hidden;
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
        }

        .modal-title {
            font-weight: 900;
            font-size: 14px;
            margin: 0;
        }

        .modal-close {
            appearance: none;
            border: 1px solid var(--border);
            background: rgba(17, 24, 39, 0.04);
            width: 34px;
            height: 34px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 900;
            color: #111827;
        }

        .modal-body {
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            align-items: center;
        }

        .modal-img {
            width: 100%;
            max-width: 360px;
            height: 150px;
            border-radius: 14px;
            background: rgba(17, 24, 39, 0.06);
            border: 1px solid var(--border);
            object-fit: contain;
        }

        .modal-name {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            line-height: 1.15;
            text-align: center;
        }

        .modal-price {
            font-size: 20px;
            font-weight: 900;
            color: #111827;
            text-align: center;
        }

        .qty {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(17, 24, 39, 0.02);
            justify-content: center;
        }

        .modal-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
            width: 100%;
        }

        .qty-btn {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #fff;
            font-weight: 900;
            font-size: 18px;
            cursor: pointer;
        }

        .qty-value {
            width: 56px;
            text-align: center;
            font-weight: 900;
            font-size: 14px;
            color: #111827;
        }

        .panel-title {
            font-weight: 900;
            font-size: 13px;
            margin: 6px 6px 10px;
        }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 6px;
        }

        .section-title {
            margin: 0;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.2px;
        }

        .btn-primary {
            appearance: none;
            border: 0;
            border-radius: 8px;
            background: #4f46e5;
            color: #fff;
            font-weight: 900;
            padding: 10px 12px;
            cursor: pointer;
            font-size: 12px;
            white-space: nowrap;
        }

        .searchbar {
            margin: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #fff;
        }

        .searchbar input {
            border: 0;
            outline: 0;
            width: 100%;
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            background: transparent;
        }

        .table-wrap {
            margin: 6px;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #fff;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            min-width: 720px;
        }

        .table thead th {
            text-align: left;
            background: rgba(17, 24, 39, 0.04);
            color: #111827;
            padding: 12px 12px;
            border-bottom: 1px solid var(--border);
            font-weight: 900;
        }

        .table tbody td {
            padding: 12px 12px;
            border-bottom: 1px solid rgba(229, 231, 235, 0.9);
            vertical-align: middle;
        }

        .table tbody tr:last-child td {
            border-bottom: 0;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            font-weight: 900;
            font-size: 11px;
            background: rgba(34, 197, 94, 0.14);
            color: #166534;
            border: 1px solid rgba(34, 197, 94, 0.22);
            white-space: nowrap;
        }

        .pill.nequi {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
            border-color: rgba(37, 99, 235, 0.18);
        }

        .alert {
            margin: 6px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(220, 38, 38, 0.22);
            background: rgba(220, 38, 38, 0.08);
            color: #991b1b;
            font-weight: 800;
            font-size: 12px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 90px 110px 44px;
            gap: 10px;
            align-items: center;
            padding: 10px;
            border: 1px solid rgba(229, 231, 235, 0.9);
            border-radius: 12px;
            background: rgba(17, 24, 39, 0.02);
        }

        .form-row select,
        .form-row input {
            width: 100%;
            border: 1px solid rgba(229, 231, 235, 0.9);
            border-radius: 10px;
            padding: 10px 10px;
            font-weight: 800;
            font-size: 12px;
            background: #fff;
            outline: none;
        }

        .line-total {
            text-align: right;
            font-weight: 900;
            font-size: 12px;
            color: #111827;
            white-space: nowrap;
            padding-right: 6px;
        }

        .btn-danger {
            appearance: none;
            border: 0;
            border-radius: 10px;
            background: rgba(220, 38, 38, 0.9);
            color: #fff;
            font-weight: 900;
            height: 40px;
            cursor: pointer;
        }

        .modal-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .total-box {
            flex: 1;
            min-width: 220px;
            border-radius: 12px;
            background: rgba(17, 24, 39, 0.04);
            padding: 10px 12px;
            font-weight: 900;
        }

        .actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-secondary {
            appearance: none;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: rgba(17, 24, 39, 0.04);
            color: #111827;
            font-weight: 900;
            padding: 10px 12px;
            cursor: pointer;
            font-size: 12px;
            white-space: nowrap;
        }

        .pay-toggle {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .dash-title {
            margin: 0 6px 8px;
            font-size: 18px;
            font-weight: 900;
        }

        .kpi-grid {
            margin: 6px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .kpi {
            border-radius: 12px;
            padding: 14px 14px;
            color: #fff;
            box-shadow: var(--shadow);
            background: linear-gradient(135deg, var(--kpi-0), var(--kpi-1));
        }

        .kpi .label {
            font-weight: 900;
            font-size: 11px;
            opacity: 0.9;
            letter-spacing: 0.2px;
        }

        .kpi .value {
            margin-top: 6px;
            font-weight: 900;
            font-size: 22px;
        }

        .dash-grid {
            margin: 6px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .dash-card {
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #fff;
            overflow: hidden;
        }

        .dash-card-head {
            padding: 12px 12px;
            font-weight: 900;
            border-bottom: 1px solid var(--border);
            background: rgba(17, 24, 39, 0.02);
        }

        .dash-table {
            min-width: 0;
        }

        .btn-small {
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 11px;
        }

        .full {
            margin-top: 16px;
        }

        @media (max-width: 980px) {
            .user-meta { display: none; }
            .products { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .product img { height: 150px; }

            .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .dash-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 520px) {
            .products { grid-template-columns: 1fr; }

            .kpi-grid { grid-template-columns: 1fr; }

            .modal { width: 100%; }
            .modal-body { padding: 12px; }
            .modal-img { max-width: 100%; height: 140px; }
            .modal-name { font-size: 18px; }
            .modal-price { font-size: 16px; }
            .qty-btn { width: 34px; height: 34px; border-radius: 10px; }
            .qty-value { width: 48px; }

            .form-row {
                grid-template-columns: 1fr 90px;
                grid-auto-rows: auto;
            }

            .line-total { text-align: left; padding-right: 0; }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="wrap">
        <div class="topbar-row">
            <div class="brand">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M7 10c0-2.8 2.2-5 5-5s5 2.2 5 5" stroke="white" stroke-width="2" stroke-linecap="round" />
                    <path d="M6 10h12l-1 10H7L6 10Z" fill="rgba(255,255,255,0.22)" stroke="rgba(255,255,255,0.7)" stroke-width="1.5" />
                    <path d="M9 12.5h6" stroke="rgba(255,255,255,0.85)" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <div>
                    <div class="brand-title">Inventario y ventas — Pastelería</div>
                    <span class="brand-sub">Panel administrativo</span>
                </div>
            </div>

            <div class="top-actions">
                <div class="user-meta">
                    <div><strong>{{ $user?->name ?? $user?->email ?? 'Usuario' }}</strong></div>
                    <div>Sesión: activa</div>
                </div>

                <form method="POST" action="/admin/logout">
                    @csrf
                    <button class="btn-logout" type="submit">Salir</button>
                </form>
            </div>
        </div>
    </div>
</header>

<nav class="tabs">
    <div class="wrap">
        <div class="tabs-row" role="navigation" aria-label="Secciones">
            <a class="tab active" href="#inicio">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                Inicio
            </a>
            <a class="tab" href="#inventario">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16M7 7v14m10-14v14M6 21h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Inventario
            </a>
            <a class="tab" href="#ventas">
                <svg viewBox="0 0 24 24" fill="none"><path d="M6 7h12l-1 12H7L6 7Z" stroke="currentColor" stroke-width="2"/><path d="M9 10h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Ventas
            </a>
            <a class="tab" href="#estadisticas">
                <svg viewBox="0 0 24 24" fill="none"><path d="M5 19V9m7 10V5m7 14v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Estadisticas
            </a>
        </div>
    </div>
</nav>

<main class="main">
    <div class="wrap">
        <section id="inicio" class="panel full hash-section">
            <h2 class="dash-title">Dashboard</h2>

            <div class="kpi-grid" aria-label="Indicadores">
                <div class="kpi">
                    <div class="label">Total de Productos</div>
                    <div class="value">{{ number_format((int) ($kpis['total_productos'] ?? 0), 0, '.', ',') }}</div>
                </div>
                <div class="kpi">
                    <div class="label">Stock Total</div>
                    <div class="value">{{ number_format((int) ($kpis['stock_total'] ?? 0), 0, '.', ',') }}</div>
                </div>
                <div class="kpi">
                    <div class="label">Total de Facturas</div>
                    <div class="value">{{ number_format((int) ($kpis['total_facturas'] ?? 0), 0, '.', ',') }}</div>
                </div>
                <div class="kpi">
                    <div class="label">Ingresos Totales</div>
                    <div class="value">${{ number_format((int) ($kpis['ingresos_totales'] ?? 0), 0, '.', ',') }}</div>
                </div>
            </div>

            <div class="dash-grid">
                <div class="dash-card">
                    <div class="dash-card-head">Últimos 18 productos vendidos</div>
                    <div class="table-wrap" style="margin:0; border:0; border-radius:0;">
                        <table class="table dash-table" aria-label="Últimos productos vendidos">
                            <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="width: 80px;">Cant.</th>
                                <th style="width: 120px;">Total</th>
                                <th style="width: 90px;">Hora</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($ultimosVendidos as $it)
                                <tr>
                                    <td>{{ $it->product?->name ?? 'Producto' }}</td>
                                    <td>{{ (int) $it->quantity }}</td>
                                    <td>${{ number_format((int) $it->line_total, 0, '.', ',') }}</td>
                                    <td>{{ $it->sale?->created_at?->format('h:i a') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="color:#6b7280; font-weight:800;">Sin ventas todavía.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="dash-card">
                    <div class="dash-card-head">Últimas Facturas</div>
                    <div class="table-wrap" style="margin:0; border:0; border-radius:0;">
                        <table class="table dash-table" aria-label="Últimas facturas">
                            <thead>
                            <tr>
                                <th>Factura</th>
                                <th style="width: 150px;">Fecha</th>
                                <th style="width: 120px;">Total</th>
                                <th style="width: 110px;">Estado</th>
                                <th style="width: 80px;">Ver</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($ultimasFacturas as $f)
                                @php($code = 'VTA-' . str_pad((string) $f->id, 6, '0', STR_PAD_LEFT))
                                <tr>
                                    <td><strong>{{ $code }}</strong></td>
                                    <td>{{ $f->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>${{ number_format((int) $f->total, 0, '.', ',') }}</td>
                                    <td><span class="pill">completed</span></td>
                                    <td>
                                        <button class="btn-secondary btn-small view-sale" type="button" data-sale-code="{{ $code }}">Ver</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="color:#6b7280; font-weight:800;">Sin facturas todavía.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="dash-card" style="margin: 6px;">
                <div class="dash-card-head">Productos con Bajo Stock</div>
                <div class="table-wrap" style="margin:0; border:0; border-radius:0;">
                    <table class="table dash-table" aria-label="Productos con bajo stock">
                        <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="width: 120px;">Stock</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($bajoStock as $p)
                            <tr>
                                <td><strong>{{ $p->name }}</strong></td>
                                <td><span class="product-badge" style="display:inline-flex;">{{ (int) $p->stock }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="color:#6b7280; font-weight:800;">No hay productos en bajo stock.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="inventario" class="panel full hash-section" hidden>
            <div class="panel-title">Inventario</div>
            <div class="products" aria-label="Productos">
                @foreach($productos as $p)
                    <article class="product">
                        @php($imgUrl = $p->image ? asset('img/' . $p->image) : '')
                        <div class="product-media">
                            <img src="{{ $imgUrl }}" alt="Imagen de {{ $p->name }}">
                        </div>

                        <div class="product-body">
                            <p class="product-name">{{ $p->name }}</p>
                        </div>

                        <div class="product-badge-row">
                            <span class="product-badge">Stock: {{ $p->stock }}</span>
                        </div>

                        <div class="product-footer">
                            <div class="product-price">${{ number_format((int) $p->price, 0, '.', ',') }}</div>
                            <button
                                class="product-add"
                                type="button"
                                aria-label="Agregar {{ $p->name }}"
                                data-name="{{ e($p->name) }}"
                                data-price="{{ (int) $p->price }}"
                                data-image="{{ $imgUrl }}"
                            >+</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="ventas" class="panel full hash-section" hidden>
            <div class="section-head">
                <h2 class="section-title">Gestión de Ventas</h2>
                <button id="openSaleModal" class="btn-primary" type="button">+ Nueva Venta</button>
            </div>

            @if(isset($errors) && $errors->any())
                <div class="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="searchbar" role="search">
                <span style="font-weight:900; color:#6b7280; font-size:12px;">🔎</span>
                <input id="salesSearch" type="text" placeholder="Buscar venta..." aria-label="Buscar venta" />
            </div>

            <div class="table-wrap">
                <table class="table" aria-label="Ventas realizadas">
                    <thead>
                    <tr>
                        <th style="width: 170px;">Venta</th>
                        <th>Fecha</th>
                        <th style="width: 120px;">Método</th>
                        <th style="width: 90px;">Items</th>
                        <th style="width: 140px;">Total</th>
                    </tr>
                    </thead>
                    <tbody id="salesTbody">
                    @forelse($ventas as $v)
                        @php($itemsCount = (int) $v->items->sum('quantity'))
                        <tr>
                            <td><strong>VTA-{{ str_pad((string) $v->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>{{ $v->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="pill {{ $v->payment_method === 'nequi' ? 'nequi' : '' }}">
                                    {{ $v->payment_method === 'nequi' ? 'Nequi' : 'Efectivo' }}
                                </span>
                            </td>
                            <td>{{ $itemsCount }}</td>
                            <td><strong>${{ number_format((int) $v->total, 0, '.', ',') }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="color:#6b7280; font-weight:800;">Sin ventas todavía.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <div id="estadisticas"></div>
    </div>
</main>

<div id="productModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal" role="document">
        <div class="modal-head">
            <p class="modal-title">Producto</p>
            <button id="productModalClose" class="modal-close" type="button" aria-label="Cerrar">×</button>
        </div>
        <div class="modal-body">
            <img id="productModalImg" class="modal-img" src="" alt="">
            <div class="modal-stack">
                <p id="productModalName" class="modal-name"></p>
                <div id="productModalPrice" class="modal-price"></div>
                <div class="qty" aria-label="Cantidad">
                    <button id="qtyMinus" class="qty-btn" type="button" aria-label="Quitar uno">−</button>
                    <div id="qtyValue" class="qty-value">1</div>
                    <button id="qtyPlus" class="qty-btn" type="button" aria-label="Agregar uno">+</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="saleModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal" role="document">
        <div class="modal-head">
            <p class="modal-title">Nueva Venta</p>
            <button id="saleModalClose" class="modal-close" type="button" aria-label="Cerrar">×</button>
        </div>

        <form id="saleForm" method="POST" action="/sales">
            @csrf
            <div class="modal-body" style="align-items: stretch;">
                <div style="font-weight:900; font-size:13px; padding: 0 2px;">Seleccionar Productos</div>
                <div id="saleItems"></div>

                <div style="display:flex; gap:10px; justify-content:flex-start;">
                    <button id="addSaleItem" class="btn-secondary" type="button">+ Agregar Producto</button>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                    <div style="font-weight:900; font-size:12px;">Método de pago</div>
                    @php($pm = old('payment_method', 'efectivo'))
                    <input id="paymentMethod" type="hidden" name="payment_method" value="{{ $pm }}" />
                    <div class="pay-toggle" role="radiogroup" aria-label="Método de pago">
                        <button
                            type="button"
                            class="pay-option {{ $pm === 'efectivo' ? 'btn-primary' : 'btn-secondary' }}"
                            data-value="efectivo"
                            aria-pressed="{{ $pm === 'efectivo' ? 'true' : 'false' }}"
                        >Efectivo</button>
                        <button
                            type="button"
                            class="pay-option {{ $pm === 'nequi' ? 'btn-primary' : 'btn-secondary' }}"
                            data-value="nequi"
                            aria-pressed="{{ $pm === 'nequi' ? 'true' : 'false' }}"
                        >Nequi</button>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="total-box">Total: <span id="saleTotal">$0</span></div>
                <div class="actions">
                    <button id="cancelSale" class="btn-secondary" type="button">Cancelar</button>
                    <button class="btn-primary" type="submit">Crear Venta</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Marca el tab activo según el hash (simple, sin dependencias).
    const tabs = document.querySelectorAll('.tab');
    const sections = {
        inicio: document.getElementById('inicio'),
        inventario: document.getElementById('inventario'),
        ventas: document.getElementById('ventas'),
    };
    function setActive() {
        const requested = window.location.hash || '#inicio';
        const hash = (requested === '#inventario' || requested === '#ventas' || requested === '#inicio') ? requested : '#inicio';
        tabs.forEach(t => t.classList.toggle('active', t.getAttribute('href') === hash));

        if (sections.inicio) sections.inicio.hidden = hash !== '#inicio';
        if (sections.inventario) sections.inventario.hidden = hash !== '#inventario';
        if (sections.ventas) sections.ventas.hidden = hash !== '#ventas';
    }
    window.addEventListener('hashchange', setActive);
    setActive();

    // Modal de producto
    const modalBackdrop = document.getElementById('productModal');
    const modalClose = document.getElementById('productModalClose');
    const modalImg = document.getElementById('productModalImg');
    const modalName = document.getElementById('productModalName');
    const modalPrice = document.getElementById('productModalPrice');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    const qtyValue = document.getElementById('qtyValue');

    let currentQty = 1;

    const money = (value) => {
        try {
            return new Intl.NumberFormat('es-CO').format(value);
        } catch (e) {
            return String(value);
        }
    };

    const renderModal = () => {
        qtyValue.textContent = String(currentQty);
        qtyMinus.disabled = currentQty <= 1;
        qtyMinus.style.opacity = qtyMinus.disabled ? '0.45' : '1';
        qtyMinus.style.cursor = qtyMinus.disabled ? 'not-allowed' : 'pointer';
    };

    const openModal = ({ name, price, image }) => {
        const unitPrice = Number(price || 0);
        currentQty = 1;

        modalName.textContent = name || '';
        modalImg.src = image || '';
        modalImg.alt = name ? ('Imagen de ' + name) : '';
        modalPrice.textContent = 'Valor: $' + money(unitPrice);

        modalBackdrop.classList.add('open');
        modalBackdrop.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        renderModal();
    };

    const closeModal = () => {
        modalBackdrop.classList.remove('open');
        modalBackdrop.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('.product-add').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal({
                name: btn.dataset.name,
                price: btn.dataset.price,
                image: btn.dataset.image,
            });
        });
    });

    qtyPlus.addEventListener('click', () => {
        currentQty += 1;
        renderModal();
    });

    qtyMinus.addEventListener('click', () => {
        if (currentQty <= 1) return;
        currentQty -= 1;
        renderModal();
    });

    modalClose.addEventListener('click', closeModal);
    modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) closeModal();
    });
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalBackdrop.classList.contains('open')) closeModal();
    });

    // Ventas (modal + crear venta)
    const saleBackdrop = document.getElementById('saleModal');
    const openSaleModalBtn = document.getElementById('openSaleModal');
    const closeSaleModalBtn = document.getElementById('saleModalClose');
    const cancelSaleBtn = document.getElementById('cancelSale');
    const saleItemsEl = document.getElementById('saleItems');
    const addSaleItemBtn = document.getElementById('addSaleItem');
    const saleTotalEl = document.getElementById('saleTotal');
    const salesSearch = document.getElementById('salesSearch');
    const salesTbody = document.getElementById('salesTbody');

    const PRODUCTS = @json($catalogoJson);

    // Método de pago (toggle)
    const paymentInput = document.getElementById('paymentMethod');
    const payOptions = Array.from(document.querySelectorAll('.pay-option'));
    const setPayment = (value) => {
        if (!paymentInput) return;
        paymentInput.value = value;
        payOptions.forEach(btn => {
            const isActive = btn.dataset.value === value;
            btn.classList.toggle('btn-primary', isActive);
            btn.classList.toggle('btn-secondary', !isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    };

    if (paymentInput && payOptions.length) {
        payOptions.forEach(btn => {
            btn.addEventListener('click', () => setPayment(btn.dataset.value));
        });
        setPayment(paymentInput.value || 'efectivo');
    }

    const fmt = (value) => {
        try { return new Intl.NumberFormat('es-CO').format(value); } catch (e) { return String(value); }
    };

    const openSaleModal = () => {
        saleBackdrop.classList.add('open');
        saleBackdrop.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeSaleModal = () => {
        saleBackdrop.classList.remove('open');
        saleBackdrop.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const buildOptions = () => {
        const placeholder = '<option value="">Seleccionar producto...</option>';
        const opts = PRODUCTS.map(p => {
            const label = `${p.name} (Stock: ${p.stock}) - $${fmt(p.price)}`;
            return `<option value="${p.id}" data-price="${p.price}">${label}</option>`;
        }).join('');
        return placeholder + opts;
    };

    const recalcSaleTotal = () => {
        let total = 0;
        saleItemsEl.querySelectorAll('.form-row').forEach(row => {
            const sel = row.querySelector('select');
            const qty = Number(row.querySelector('input').value || 0);
            const price = Number(sel?.selectedOptions?.[0]?.dataset?.price || 0);
            const line = Math.max(0, qty) * Math.max(0, price);
            const lineEl = row.querySelector('.line-total');
            if (lineEl) lineEl.textContent = '$' + fmt(line);
            total += line;
        });
        saleTotalEl.textContent = '$' + fmt(total);
    };

    const renumberInputs = () => {
        const rows = Array.from(saleItemsEl.querySelectorAll('.form-row'));
        rows.forEach((row, i) => {
            row.querySelector('select').name = `items[${i}][product_id]`;
            row.querySelector('input').name = `items[${i}][quantity]`;
        });
    };

    const addRow = () => {
        const row = document.createElement('div');
        row.className = 'form-row';
        row.innerHTML = `
            <select required>${buildOptions()}</select>
            <input type="number" min="1" value="1" required />
            <div class="line-total">$0</div>
            <button class="btn-danger" type="button" aria-label="Quitar">×</button>
        `;

        const sel = row.querySelector('select');
        const qty = row.querySelector('input');
        const del = row.querySelector('.btn-danger');

        sel.addEventListener('change', recalcSaleTotal);
        qty.addEventListener('input', recalcSaleTotal);
        del.addEventListener('click', () => {
            row.remove();
            renumberInputs();
            recalcSaleTotal();
        });

        saleItemsEl.appendChild(row);
        renumberInputs();
        recalcSaleTotal();
    };

    if (openSaleModalBtn) {
        openSaleModalBtn.addEventListener('click', () => {
            // Reset
            saleItemsEl.innerHTML = '';
            addRow();
            openSaleModal();
        });
    }

    if (addSaleItemBtn) addSaleItemBtn.addEventListener('click', addRow);
    if (closeSaleModalBtn) closeSaleModalBtn.addEventListener('click', closeSaleModal);
    if (cancelSaleBtn) cancelSaleBtn.addEventListener('click', closeSaleModal);
    if (saleBackdrop) {
        saleBackdrop.addEventListener('click', (e) => {
            if (e.target === saleBackdrop) closeSaleModal();
        });
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && saleBackdrop?.classList.contains('open')) closeSaleModal();
    });

    if (salesSearch && salesTbody) {
        salesSearch.addEventListener('input', () => {
            const q = salesSearch.value.toLowerCase().trim();
            Array.from(salesTbody.querySelectorAll('tr')).forEach(tr => {
                const text = tr.textContent.toLowerCase();
                tr.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    // Inicio: botón "Ver" en últimas facturas
    document.querySelectorAll('.view-sale').forEach(btn => {
        btn.addEventListener('click', () => {
            const code = btn.dataset.saleCode || '';
            window.location.hash = '#ventas';

            // Espera al repaint y al setActive
            window.setTimeout(() => {
                if (!salesSearch) return;
                salesSearch.value = code;
                salesSearch.dispatchEvent(new Event('input'));
                salesSearch.focus();
            }, 60);
        });
    });
</script>
</body>
</html>
