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
    $topVendidos = $topVendidos ?? collect();
    $ventasDiarias = $ventasDiarias ?? collect();
    $ventasDiariasJson = $ventasDiariasJson ?? collect();

    $catalogoJson = $catalogo
        ->map(function ($p) {
            return [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'price' => (int) $p->price,
                'stock' => (int) $p->stock,
                'image_url' => $p->image ? asset('img/' . $p->image) : '',
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
            max-width: 1600px;
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

        /* Inicio (vista de bienvenida) */
        .home-hero {
            padding: 18px 18px 20px;
            border-radius: 14px;
            background: linear-gradient(120deg, #f97316, #ec4899);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .home-hero::after {
            content: "";
            position: absolute;
            inset-inline-end: -40px;
            top: -40px;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.35), transparent 60%);
            opacity: 0.9;
        }

        .home-hero-title {
            position: relative;
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.3px;
        }

        .home-hero-subtitle {
            position: relative;
            margin: 0;
            font-size: 13px;
            opacity: 0.95;
        }

        .home-hero-badges {
            position: relative;
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .home-pill {
            padding: 4px 9px;
            border-radius: 999px;
            background: rgba(17,24,39,0.16);
            font-size: 11px;
            font-weight: 800;
        }

        .home-grid {
            margin-top: 14px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .home-card {
            border-radius: 14px;
            border: 1px solid var(--border);
            background: #fff;
            padding: 12px 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .home-card-title {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .home-card-desc {
            margin: 0;
            font-size: 12px;
            color: #4b5563;
        }

        .home-card-actions {
            margin-top: auto;
            display: flex;
            gap: 8px;
        }

        .btn-home-primary,
        .btn-home-ghost {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 900;
            border-radius: 999px;
            padding: 7px 12px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-home-primary {
            border: 0;
            background: linear-gradient(120deg, #4f46e5, #7c3aed);
            color: #fff;
            box-shadow: 0 10px 18px rgba(79,70,229,0.35);
        }

        .btn-home-ghost {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            color: #4b5563;
        }

        .btn-home-primary svg,
        .btn-home-ghost svg {
            width: 14px;
            height: 14px;
        }

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
            padding: 0;
            border-radius: 0;
            font-weight: 900;
            font-size: 14px;
            background: transparent;
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
            width: min(1180px, 98vw);
            background: var(--paper);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
            overflow: hidden;
        }

        /* Modal de producto en inventario (más pequeño) */
        #productModal .modal {
            width: min(640px, 96vw);
        }

        /* Modal de Nueva Venta más grande */
        #saleModal .modal {
            width: min(1400px, 99vw);
        }

        /* Modal de resumen (comprobante más angosto) */
        #saleConfirmModal .modal {
            width: min(420px, 96vw);
        }

        /* Modal de historial de inventario un poco más angosto */
        #inventoryHistoryModal .modal {
            width: min(820px, 96vw);
        }

        /* Modal de detalle de venta más angosto */
        #saleDetailModal .modal {
            width: min(640px, 96vw);
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

        .sales-summary-grid {
            margin: 6px;
            margin-top: 0;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .sales-summary-card {
            position: relative;
            border-radius: 14px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.06), transparent 55%), #ffffff;
            padding: 12px 14px 13px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
            transition: transform 0.12s ease-out, box-shadow 0.12s ease-out, border-color 0.12s ease-out;
        }

        .sales-summary-card::before {
            content: "";
            position: absolute;
            inset-inline-start: 10px;
            top: 12px;
            width: 4px;
            height: 18px;
            border-radius: 999px;
            background: linear-gradient(180deg, #4f46e5, #6366f1);
            opacity: 0.4;
        }

        .sales-summary-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.11);
            border-color: rgba(37, 99, 235, 0.35);
        }

        .sales-summary-label {
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding-inline-start: 14px;
        }

        .sales-summary-value {
            font-size: 18px;
            font-weight: 900;
        }

        .sales-summary-sub {
            font-size: 11px;
            font-weight: 800;
            color: #6b7280;
        }

        .sales-summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .sales-summary-table th,
        .sales-summary-table td {
            padding: 4px 0;
        }

        .sales-summary-table th {
            font-weight: 800;
            color: #6b7280;
        }

        .sales-day-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .sales-day-metrics {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .sales-day-chip {
            border-radius: 999px;
            padding: 6px 10px;
            background: rgba(17, 24, 39, 0.03);
            border: 1px solid var(--border);
            font-size: 11px;
            font-weight: 800;
        }

        .sales-goal-current-low {
            color: #b91c1c;
        }

        .sales-goal-current-mid {
            color: #b45309;
        }

        .sales-goal-current-ok {
            color: #16a34a;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            min-width: 720px;
        }

        /* La tabla del historial no necesita ser tan ancha */
        #inventoryHistoryModal .table {
            min-width: 0;
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
            position: fixed;
            top: 18px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            min-width: min(520px, 96vw);
            max-width: 96vw;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid rgba(248, 113, 113, 0.6);
            background: linear-gradient(135deg, #f97316, #dc2626);
            color: #fef2f2;
            font-weight: 900;
            font-size: 13px;
            box-shadow: 0 18px 40px rgba(220, 38, 38, 0.45);
            text-align: center;
            display: none;
            opacity: 1;
            transition: opacity 0.35s ease, transform 0.35s ease;
        }

        .alert-hide {
            opacity: 0;
            transform: translate(-50%, -8px);
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

        .line-total.error {
            color: #b91c1c;
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

        .sale-product-cell {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sale-product-preview {
            display: none;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: var(--muted);
        }

        .sale-product-img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            background: rgba(17, 24, 39, 0.06);
            border: 1px solid var(--border);
        }

        .sale-product-name {
            font-weight: 800;
            font-size: 12px;
            color: #111827;
        }

        .sale-product-price {
            font-weight: 900;
            font-size: 12px;
            color: #111827;
        }

        .sale-products-grid {
            margin-top: 4px;
            margin-bottom: 12px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .sale-product-card {
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(17, 24, 39, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .sale-product-media {
            position: relative;
            padding: 6px;
            background: rgba(17, 24, 39, 0.04);
        }

        .sale-product-media img {
            display: block;
            width: 100%;
            height: 100px;
            object-fit: contain;
            background: rgba(17, 24, 39, 0.06);
            border-radius: 10px;
        }

        .sale-cart-btn {
            position: absolute;
            right: 14px;
            bottom: 14px;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 0;
            background: #9d174d;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .sale-product-body {
            padding: 8px 8px 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sale-product-body .sale-product-name {
            margin: 0;
        }

        .sale-qty-controls {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
            padding: 6px 8px;
            border-radius: 999px;
            background: rgba(17, 24, 39, 0.03);
        }

        .sale-qty-controls .qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #fff;
            font-weight: 900;
            cursor: pointer;
        }

        .sale-qty-controls .qty-value {
            min-width: 24px;
            text-align: center;
            font-weight: 900;
            font-size: 13px;
        }

        .sale-clear-btn {
            margin-top: 0;
            margin-left: 6px;
            appearance: none;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.65);
            background: #f9fafb;
            color: #6b7280;
            font-size: 12px;
            font-weight: 900;
            padding: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .sale-clear-btn:hover {
            background: #f3f4f6;
            border-color: rgba(148, 163, 184, 0.9);
            color: #374151;
        }

        .summary-list {
            margin-top: 8px;
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 260px;
            overflow-y: auto;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: #fff;
        }

        .summary-img {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
            background: rgba(17, 24, 39, 0.06);
            border: 1px solid var(--border);
        }

        .summary-main {
            flex: 1;
        }

        .summary-name {
            font-weight: 900;
            font-size: 13px;
        }

        .summary-meta {
            font-size: 12px;
            color: var(--muted);
            font-weight: 800;
        }

        .summary-line {
            font-weight: 900;
            font-size: 13px;
            white-space: nowrap;
        }

        .inventory-history-pagination {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
            gap: 6px;
        }

        .inventory-history-pagination .page-pill {
            min-width: 28px;
            height: 28px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #f9fafb;
            font-size: 12px;
            font-weight: 800;
            color: #4b5563;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .inventory-history-pagination .page-pill-active {
            border-color: #4f46e5;
            background: #4f46e5;
            color: #fff;
        }

        @media (max-width: 980px) {
            .sale-products-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .sale-products-grid {
                grid-template-columns: 1fr 1fr;
            }
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
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            align-items: flex-start;
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

            .table {
                min-width: 0;
            }
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

            .table {
                font-size: 11px;
            }

            .table thead th,
            .table tbody td {
                padding: 8px 6px;
            }

            #cierre .dash-card {
                margin: 4px 4px 8px;
            }

            #cierre .dash-card-head {
                font-size: 12px;
                text-align: center;
            }

            /* Cierre: tabla en formato tarjetas apiladas en móvil */
            #cierre .table {
                font-size: 13px;
            }

            #cierre .table thead {
                display: none;
            }

            #cierre .table,
            #cierre .table tbody,
            #cierre .table tr,
            #cierre .table td {
                display: block;
                width: 100%;
            }

            #cierre .table tbody tr {
                margin-bottom: 10px;
                border: 1px solid var(--border);
                border-radius: 10px;
                overflow: hidden;
                background: #ffffff;
            }

            #cierre .table tbody td {
                padding: 6px 10px;
            }

            #cierre .table tbody td:nth-child(1) {
                text-align: center;
                padding-top: 10px;
            }

            #cierre .table tbody td:nth-child(2),
            #cierre .table tbody td:nth-child(3),
            #cierre .table tbody td:nth-child(4) {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
            }

            #cierre .table tbody td:nth-child(2)::before,
            #cierre .table tbody td:nth-child(3)::before,
            #cierre .table tbody td:nth-child(4)::before {
                font-size: 11px;
                font-weight: 800;
                color: #6b7280;
            }

            #cierre .table tbody td:nth-child(2)::before { content: 'Producto'; }
            #cierre .table tbody td:nth-child(3)::before { content: 'Cantidad'; }
            #cierre .table tbody td:nth-child(4)::before { content: 'Total'; }

            #cierre .table tbody td:nth-child(4) {
                border-top: 1px solid rgba(229, 231, 235, 0.9);
                margin-top: 4px;
                padding-top: 8px;
            }
        }

        .toast {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 9999;
            padding: 10px 16px;
            border-radius: 999px;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            box-shadow: 0 15px 35px rgba(22, 163, 74, 0.45);
            color: #f0fdf4;
            font-weight: 900;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.35s ease, transform 0.35s ease;
        }

        .toast-hide {
            opacity: 0;
            transform: translateY(-8px);
        }

        .toast-icon {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* Modo impresión solo para la vista de cierre */
        @media print {
            body.print-cierre {
                background: #ffffff;
            }

            body.print-cierre header,
            body.print-cierre nav,
            body.print-cierre .modal-backdrop,
            body.print-cierre #inicio,
            body.print-cierre #inventario,
            body.print-cierre #ventas,
            body.print-cierre #estadisticas {
                display: none !important;
            }

            body.print-cierre #cierrePrintBtn {
                display: none !important;
            }

            body.print-cierre #cierrePagination {
                display: none !important;
            }

            body.print-cierre main {
                padding: 0;
            }

            body.print-cierre .wrap {
                max-width: 100%;
                margin: 0;
            }

            body.print-cierre #cierre {
                display: block !important;
                border: 0;
                box-shadow: none;
            }

            body.print-cierre .dash-card {
                box-shadow: none;
            }
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

@if(session('sale_success'))
    <div id="saleSuccessToast" class="toast" role="status" aria-live="polite">
        <div class="toast-icon">✓</div>
        <div>{{ session('sale_success') }}</div>
    </div>
@endif

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
            <a class="tab" href="#cierre">
                <svg viewBox="0 0 24 24" fill="none"><path d="M5 5h14v4H5zM5 11h9v8H5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                Cierre
            </a>
            <a class="tab" href="#admin">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 3a3 3 0 0 1 3 3v1h2a2 2 0 0 1 2 2v10H5V9a2 2 0 0 1 2-2h2V6a3 3 0 0 1 3-3Zm-1 4V6a1 1 0 1 1 2 0v1h-2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
                Panel Admin
            </a>
        </div>
    </div>
</nav>

<main class="main">
    <div class="wrap">
        <section id="inicio" class="panel full hash-section">
            <div class="home-hero">
                <h2 class="home-hero-title">Bienvenido a tu panel de Inventario y Ventas</h2>
                <p class="home-hero-subtitle">Controla tus productos, registra ventas y obtén un resumen claro de cómo se mueve tu negocio en tiempo real.</p>

                <div class="home-hero-badges">
                    <span class="home-pill">Inventario en tiempo real</span>
                    <span class="home-pill">Ventas rápidas y seguras</span>
                    <span class="home-pill">Estadísticas y cierres diarios</span>
                </div>
            </div>

            <div class="home-grid">
                <div class="home-card">
                    <h3 class="home-card-title">
                        <span style="font-size:16px;">📦</span>
                        <span>Inventario</span>
                    </h3>
                    <p class="home-card-desc">Administra tus productos, actualiza el stock y consulta el historial de movimientos de inventario.</p>
                    <div class="home-card-actions">
                        <a href="#inventario" class="btn-home-primary">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>Ir a Inventario</span>
                        </a>
                    </div>
                </div>

                <div class="home-card">
                    <h3 class="home-card-title">
                        <span style="font-size:16px;">🧾</span>
                        <span>Ventas</span>
                    </h3>
                    <p class="home-card-desc">Crea nuevas ventas, revisa el historial de facturas y consulta el detalle de cada ticket.</p>
                    <div class="home-card-actions">
                        <a href="#ventas" class="btn-home-primary">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>Ir a Ventas</span>
                        </a>
                    </div>
                </div>

                <div class="home-card">
                    <h3 class="home-card-title">
                        <span style="font-size:16px;">📊</span>
                        <span>Estadísticas</span>
                    </h3>
                    <p class="home-card-desc">Consulta los últimos productos vendidos y las últimas facturas para analizar tu rendimiento.</p>
                    <div class="home-card-actions">
                        <a href="#estadisticas" class="btn-home-primary">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>Ir a Estadísticas</span>
                        </a>
                    </div>
                </div>

                <div class="home-card">
                    <h3 class="home-card-title">
                        <span style="font-size:16px;">🧮</span>
                        <span>Cierre de ventas</span>
                    </h3>
                    <p class="home-card-desc">Obtén el resumen diario de ingresos, productos vendidos y genera un formato listo para imprimir.</p>
                    <div class="home-card-actions">
                        <a href="#cierre" class="btn-home-primary">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>Ir a Cierre</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="inventario" class="panel full hash-section" hidden>
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin:0 6px 8px;">
                <div class="panel-title" style="margin:0;">Inventario</div>
                <button id="openInventoryHistory" type="button" class="btn-secondary btn-small" style="display:inline-flex; align-items:center; gap:6px;">
                    <span style="font-size:14px;">🕒</span>
                    <span>Historial</span>
                </button>
            </div>
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
                                data-id="{{ (int) $p->id }}"
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
                <div id="serverErrorAlert" class="alert" style="display:block;">
                    {{ $errors->first() }}
                </div>
            @endif

            @php(
                $totalFacturas = (int) ($kpis['total_facturas'] ?? 0)
            )
            @php(
                $ingresosTotales = (int) ($kpis['ingresos_totales'] ?? 0)
            )
            @php(
                $ticketPromedio = $totalFacturas > 0 ? (int) floor($ingresosTotales / $totalFacturas) : 0
            )

            @php($ventasDateValue = $ventasDate ?? '')

            <div class="sales-summary-card" style="margin: 6px; margin-bottom: 2px;">
                <div class="sales-day-row">
                    <div>
                        <div class="sales-summary-label">Filtro por día</div>
                        <input
                            id="salesDateFilter"
                            type="date"
                            value="{{ $ventasDateValue }}"
                            style="margin-top:6px; border:1px solid #e5e7eb; border-radius:8px; padding:6px 8px; font-size:12px; font-weight:700;"
                        />
                    </div>
                    <div class="sales-day-metrics">
                        @if($ventasDiaAgg)
                            @php($goalDia = (int) ($ventasGoalDia ?? 0))
                            @php($actualDia = (int) ($ventasDiaAgg['total'] ?? 0))
                            @php($ratioDia = $goalDia > 0 ? ($actualDia / $goalDia) : 0)
                            @php($goalClass = $ratioDia >= 1 ? 'sales-goal-current-ok' : ($ratioDia >= 0.7 ? 'sales-goal-current-mid' : 'sales-goal-current-low'))
                            <div class="sales-day-chip">
                                Vendido en el día:
                                <strong>${{ number_format((int) $ventasDiaAgg['total'], 0, '.', ',') }}</strong>
                            </div>
                            <div class="sales-day-chip">
                                Efectivo:
                                <strong>${{ number_format((int) $ventasDiaAgg['efectivo'], 0, '.', ',') }}</strong>
                            </div>
                            <div class="sales-day-chip">
                                Nequi:
                                <strong>${{ number_format((int) $ventasDiaAgg['nequi'], 0, '.', ',') }}</strong>
                            </div>
                            <div class="sales-day-chip">
                                Facturas del día:
                                <strong>{{ number_format((int) $ventasDiaAgg['total_facturas'], 0, '.', ',') }}</strong>
                            </div>
                            <div class="sales-day-chip">
                                Objetivo de venta:
                                <strong>${{ number_format($goalDia, 0, '.', ',') }}</strong>
                            </div>
                            <div class="sales-day-chip {{ $goalClass }}">
                                Objetivo actual:
                                <strong>${{ number_format($actualDia, 0, '.', ',') }}</strong>
                            </div>
                        @else
                            <div class="sales-day-chip">
                                Selecciona un día para ver el detalle de ventas.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="sales-summary-grid">
                <div class="sales-summary-card">
                    <div class="sales-summary-label">Total facturas</div>
                    <div class="sales-summary-value">{{ number_format($totalFacturas, 0, '.', ',') }}</div>
                    <div class="sales-summary-sub">Histórico registrado en el sistema</div>
                </div>
                <div class="sales-summary-card">
                    <div class="sales-summary-label">Ingresos totales</div>
                    <div class="sales-summary-value">${{ number_format($ingresosTotales, 0, '.', ',') }}</div>
                    <div class="sales-summary-sub">Suma de todas las ventas</div>
                </div>
                <div class="sales-summary-card">
                    <div class="sales-summary-label">Ticket promedio</div>
                    <div class="sales-summary-value">${{ number_format($ticketPromedio, 0, '.', ',') }}</div>
                    <div class="sales-summary-sub">Ingresos totales / número de facturas</div>
                </div>
            </div>

            <div class="searchbar" role="search">
                <span style="font-weight:900; color:#6b7280; font-size:12px;">🔎</span>
                <input id="salesSearch" type="text" placeholder="Buscar venta..." aria-label="Buscar venta" />
                <div id="salesPagination" class="inventory-history-pagination" style="margin:0 0 0 auto;"></div>
            </div>

            <div class="table-wrap">
                <table class="table" aria-label="Ventas realizadas">
                    <thead>
                    <tr>
                        <th style="width: 130px;">Venta</th>
                        <th style="width: 260px;">Productos</th>
                        <th>Fecha</th>
                        <th style="width: 120px;">Método</th>
                        <th style="width: 90px;">Items</th>
                        <th style="width: 140px;">Total</th>
                        <th style="width: 80px;"></th>
                    </tr>
                    </thead>
                    <tbody id="salesTbody">
                    @forelse($ventas as $v)
                        @php($itemsCount = (int) $v->items->sum('quantity'))
                        @php($code = 'VTA-' . str_pad((string) $v->id, 6, '0', STR_PAD_LEFT))
                        @php($detailItems = $v->items->map(function ($it) {
                            $product = $it->product;
                            return [
                                'product_id' => (int) $it->product_id,
                                'name' => $product?->name ?? 'Producto',
                                'image_url' => $product && $product->image ? asset('img/' . $product->image) : '',
                                'quantity' => (int) $it->quantity,
                                'unit_price' => (int) $it->unit_price,
                                'line_total' => (int) $it->line_total,
                            ];
                        }))
                        @php($firstItem = $v->items->first())
                        @php($firstProduct = $firstItem?->product)
                        @php($firstImg = $firstProduct && $firstProduct->image ? asset('img/' . $firstProduct->image) : '')
                        @php($productsCount = $v->items->count())
                        @php($productsLabel = $firstProduct?->name
                            ? ($productsCount > 1
                                ? $firstProduct->name . ' +' . ($productsCount - 1) . ' más'
                                : $firstProduct->name)
                            : ($productsCount > 1 ? 'Varios productos' : 'Producto'))
                        <tr>
                            <td><strong>{{ $code }}</strong></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    @if($firstImg)
                                        <img src="{{ $firstImg }}" alt="Imagen de {{ $firstProduct->name ?? 'Producto' }}" style="width:40px; height:40px; border-radius:8px; object-fit:cover; border:1px solid #e5e7eb; background:#f3f4f6;">
                                    @else
                                        <div style="width:40px; height:40px; border-radius:8px; border:1px solid #e5e7eb; background:#f9fafb; display:flex; align-items:center; justify-content:center; font-size:11px; color:#9ca3af; font-weight:800;">No img</div>
                                    @endif
                                    <div>
                                        <div style="font-size:12px; font-weight:900;">{{ $productsLabel }}</div>
                                        <div style="font-size:11px; font-weight:800; color:#6b7280;">{{ $itemsCount }} items</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $v->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="pill {{ $v->payment_method === 'nequi' ? 'nequi' : '' }}">
                                    {{ $v->payment_method === 'nequi' ? 'Nequi' : 'Efectivo' }}
                                </span>
                            </td>
                            <td>{{ $itemsCount }}</td>
                            <td><strong>${{ number_format((int) $v->total, 0, '.', ',') }}</strong></td>
                            <td>
                                <button
                                    type="button"
                                    class="btn-secondary btn-small view-sale-detail"
                                    data-sale-id="{{ $v->id }}"
                                    data-sale-code="{{ $code }}"
                                    data-datetime="{{ $v->created_at?->format('d/m/Y H:i') }}"
                                    data-payment="{{ $v->payment_method }}"
                                    data-total="{{ (int) $v->total }}"
                                    data-items='@json($detailItems)'
                                >Ver</button>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty="1">
                            <td colspan="6" style="color:#6b7280; font-weight:800;">Sin ventas todavía.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section id="estadisticas" class="panel full hash-section" hidden>
            <h2 class="dash-title">Estadísticas</h2>

            <div class="dash-grid">
                    <div class="dash-card">
                    <div class="dash-card-head" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                        <span>Últimos 20 productos vendidos</span>
                        <div id="ultimosVendidosPagination" class="inventory-history-pagination" style="margin-top:0;"></div>
                    </div>
                    <div class="table-wrap" style="margin:0; border:0; border-radius:0;">
                        <table class="table dash-table" aria-label="Últimos productos vendidos">
                            <thead>
                            <tr>
                                <th style="width: 45%;">Producto</th>
                                <th style="width: 12%;">Cant.</th>
                                <th style="width: 23%;">Total</th>
                                <th style="width: 20%;">Hora</th>
                            </tr>
                            </thead>
                            <tbody id="ultimosVendidosTbody">
                            @forelse($ultimosVendidos as $it)
                                <tr>
                                    <td>{{ $it->product?->name ?? 'Producto' }}</td>
                                    <td>{{ (int) $it->quantity }}</td>
                                    <td>${{ number_format((int) $it->line_total, 0, '.', ',') }}</td>
                                    <td>{{ $it->sale?->created_at?->format('h:i a') }}</td>
                                </tr>
                            @empty
                                <tr data-empty="1">
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

                <div class="dash-card">
                    <div class="dash-card-head">Productos más vendidos</div>
                    <div class="table-wrap" style="margin:0; border:0; border-radius:0;">
                        <table class="table dash-table" aria-label="Productos más vendidos">
                            <thead>
                            <tr>
                                <th style="width:80px;">Imagen</th>
                                <th>Producto</th>
                                <th style="width:80px;">Cant.</th>
                                <th style="width:120px;">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($topVendidos as $row)
                                @php($prod = $row->product)
                                @php($imgUrl = $prod && $prod->image ? asset('img/' . $prod->image) : '')
                                <tr>
                                    <td>
                                        @if($imgUrl)
                                            <img src="{{ $imgUrl }}" alt="Imagen de {{ $prod->name ?? 'Producto' }}" style="width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; background:#f3f4f6;">
                                        @else
                                            <span style="font-size:11px; color:#9ca3af; font-weight:800;">Sin imagen</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $prod->name ?? 'Producto' }}</strong></td>
                                    <td>{{ (int) $row->total_qty }}</td>
                                    <td>${{ number_format((int) $row->total_amount, 0, '.', ',') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="color:#6b7280; font-weight:800;">Sin datos de ventas aún.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
        <section id="cierre" class="panel full hash-section" hidden>
            <h2 class="dash-title">Cierre de ventas</h2>

            <div class="dash-card" style="margin:6px 6px 10px;">
                <div class="dash-card-head">Resumen por día</div>
                <div style="padding: 10px 12px; display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:space-between;">
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <label for="cierreDate" style="font-weight:800; font-size:12px; color:#4b5563;">Seleccionar día</label>
                        <input id="cierreDate" type="date" style="border:1px solid #e5e7eb; border-radius:8px; padding:6px 8px; font-size:12px; font-weight:700;">
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:14px; align-items:center;">
                        <div style="min-width:180px;">
                            <div style="font-size:11px; font-weight:800; color:#6b7280;">Dinero vendido</div>
                            <div id="cierreTotalMoney" style="font-size:18px; font-weight:900;">$0</div>
                        </div>
                        <div style="min-width:180px;">
                            <div style="font-size:11px; font-weight:800; color:#6b7280;">Total productos vendidos</div>
                            <div id="cierreTotalProducts" style="font-size:18px; font-weight:900;">0</div>
                        </div>
                        <button id="cierrePrintBtn" type="button" class="btn-secondary btn-small">Imprimir</button>
                    </div>
                </div>
            </div>

            <div class="dash-card" style="margin:6px;">
                <div class="dash-card-head" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <span>Detalle de productos vendidos en el día</span>
                    <div id="cierrePagination" class="inventory-history-pagination" style="margin-top:0;"></div>
                </div>
                <div class="table-wrap" style="margin:0; border:0; border-radius:0;">
                    <table class="table dash-table" aria-label="Detalle de productos vendidos por día">
                        <thead>
                        <tr>
                            <th style="width:80px;">Imagen</th>
                            <th>Producto</th>
                            <th style="width:110px;">Cantidad</th>
                            <th style="width:140px;">Total</th>
                        </tr>
                        </thead>
                        <tbody id="cierreItemsTbody">
                        <tr data-empty="1">
                            <td colspan="4" style="color:#6b7280; font-weight:800;">No hay ventas registradas para este día.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <section id="admin" class="panel full hash-section" hidden>
            <h2 class="dash-title">Panel Admin — Metas de ventas</h2>

            @php($weekdayNames = [
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado',
                7 => 'Domingo',
            ])

            <div class="dash-card" style="margin:6px;">
                <div class="dash-card-head">Metas esperadas por día de la semana</div>
                <div style="padding: 10px 12px;">
                    <p style="font-size:12px; color:#4b5563; font-weight:800; margin-top:0; margin-bottom:10px;">
                        Define aquí la venta esperada para cada día (valores en pesos). Estas metas se usarán en la vista de Ventas para el cálculo del objetivo actual según el día seleccionado.
                    </p>
                    <form method="POST" action="/admin/sales-goals">
                        @csrf
                        <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:14px;">
                            @foreach($weekdayNames as $w => $label)
                                @php($goalRow = ($weekdayGoals ?? collect())->get($w))
                                @php($amount = (int) ($goalRow->amount ?? 0))
                                <div class="sales-summary-card" style="margin:0;">
                                    <div class="sales-summary-label">{{ $label }}</div>
                                    <input
                                        type="number"
                                        min="0"
                                        name="goals[{{ $w }}]"
                                        value="{{ $amount }}"
                                        style="margin-top:8px; width:100%; border:1px solid #e5e7eb; border-radius:9px; padding:8px 10px; font-size:14px; font-weight:700;"
                                    />
                                </div>
                            @endforeach
                        </div>
                        <div style="margin-top:14px; display:flex; justify-content:flex-end;">
                            <button type="submit" class="btn-primary">Guardar metas</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
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
                <button id="productAddToSale" class="btn-primary" type="button" style="margin-top: 14px;">Agregar cantidad</button>
            </div>
        </div>
    </div>
</div>

<form id="inventoryQuickSaleForm" method="POST" action="/inventory/add-stock" style="display:none;">
    @csrf
    <input type="hidden" name="product_id" id="inventoryQuickProductId" />
    <input type="hidden" name="quantity" id="inventoryQuickQuantity" />
</form>

<div id="saleModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal" role="document">
        <div class="modal-head">
            <p class="modal-title">Nueva Venta</p>
            <button id="saleModalClose" class="modal-close" type="button" aria-label="Cerrar">×</button>
        </div>

        <form id="saleForm" method="POST" action="/sales">
            @csrf
            <div class="modal-body" style="align-items: stretch;">
                <div id="saleInlineError" class="alert" style="display:none;"></div>
                <div style="font-weight:900; font-size:13px; padding: 0 2px; margin-bottom:8px;">Seleccionar Productos</div>
                <div id="saleProductsGrid" class="sale-products-grid"></div>

                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                    <div style="font-weight:900; font-size:12px;">Método de pago</div>
                    @php($pm = old('payment_method', ''))
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

            <div id="saleItemsHidden" style="display:none;"></div>

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

<div id="saleConfirmModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal" role="document">
        <div class="modal-head">
            <p class="modal-title">Confirmar Venta</p>
            <button id="saleConfirmClose" class="modal-close" type="button" aria-label="Cerrar">×</button>
        </div>
        <div class="modal-body" style="align-items: stretch;">
            <div style="font-weight:900; font-size:13px; padding: 0 2px;">Resumen de la venta</div>
            <div id="saleSummaryItems" class="summary-list"></div>
            <div style="margin-top:8px; font-size:13px; font-weight:800;">Método de pago: <span id="saleSummaryPayment"></span></div>
        </div>
        <div class="modal-footer">
            <div class="total-box">Total: <span id="saleSummaryTotal">$0</span></div>
            <div class="actions">
                <button id="saleSummaryBack" class="btn-secondary" type="button">Volver</button>
                <button id="saleSummaryConfirm" class="btn-primary" type="button">Confirmar venta</button>
            </div>
        </div>
    </div>
</div>

<div id="saleDetailModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal" role="document">
        <div class="modal-head">
            <p class="modal-title">Detalle de la venta <span id="saleDetailCode" style="font-weight:700;"></span></p>
            <button id="saleDetailClose" class="modal-close" type="button" aria-label="Cerrar">×</button>
        </div>
        <div class="modal-body" style="align-items: stretch;">
            <div style="font-weight:900; font-size:13px; padding: 0 2px;">Productos vendidos</div>
            <div id="saleDetailItems" class="summary-list"></div>
            <div style="margin-top:8px; font-size:13px; font-weight:800;">Método de pago: <span id="saleDetailPayment"></span></div>
            <div style="margin-top:4px; font-size:12px; font-weight:800; color:#4b5563;">Fecha y hora: <span id="saleDetailDateTime"></span></div>
        </div>
        <div class="modal-footer">
            <div class="total-box">Total: <span id="saleDetailTotal">$0</span></div>
            <div class="actions">
                <button id="saleDetailCloseFooter" class="btn-secondary" type="button">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="inventoryHistoryModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal" role="document">
        <div class="modal-head">
            <p class="modal-title">Historial de ingresos a inventario</p>
            <button id="inventoryHistoryClose" class="modal-close" type="button" aria-label="Cerrar">×</button>
        </div>
        <div class="modal-body" style="align-items: stretch; max-height: 70vh; overflow-y: auto;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding:0 2px; margin-bottom:8px;">
                <div style="font-weight:900; font-size:13px;">Últimos movimientos</div>
                <div id="inventoryHistoryPagination" class="inventory-history-pagination" style="margin-top:0;"></div>
            </div>
            <div class="table-wrap" style="margin:0;">
                <table class="table" aria-label="Historial de inventario">
                    <thead>
                    <tr>
                        <th style="width: 80px;">Imagen</th>
                        <th>Producto</th>
                        <th style="width: 90px;">Cantidad</th>
                        <th style="width: 120px;">Stock actual</th>
                        <th style="width: 180px;">Fecha y hora</th>
                    </tr>
                    </thead>
                    <tbody id="inventoryHistoryTbody">
                    @forelse($movimientosInventario ?? [] as $m)
                        @php($prod = $m->product)
                        @php($imgUrl = $prod && $prod->image ? asset('img/' . $prod->image) : '')
                        <tr>
                            <td>
                                @if($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="Imagen de {{ $prod->name ?? 'Producto' }}" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; background:#f3f4f6;">
                                @else
                                    <span style="font-size:11px; color:#9ca3af; font-weight:800;">Sin imagen</span>
                                @endif
                            </td>
                            <td><strong>{{ $prod->name ?? 'Producto eliminado' }}</strong></td>
                            <td>{{ (int) $m->quantity }}</td>
                            <td>{{ $prod?->stock ?? 0 }}</td>
                            <td>{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr data-empty="1">
                            <td colspan="5" style="color:#6b7280; font-weight:800;">Aún no hay registros de ingresos a inventario.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Marca el tab activo según el hash (simple, sin dependencias).
    const tabs = document.querySelectorAll('.tab');
    const sections = {
        inicio: document.getElementById('inicio'),
        inventario: document.getElementById('inventario'),
        ventas: document.getElementById('ventas'),
        estadisticas: document.getElementById('estadisticas'),
        cierre: document.getElementById('cierre'),
        admin: document.getElementById('admin'),
    };
    function setActive() {
        const requested = window.location.hash || '#inicio';
        const valid = ['#inicio', '#inventario', '#ventas', '#estadisticas', '#cierre', '#admin'];
        const hash = valid.includes(requested) ? requested : '#inicio';
        tabs.forEach(t => t.classList.toggle('active', t.getAttribute('href') === hash));

        if (sections.inicio) sections.inicio.hidden = hash !== '#inicio';
        if (sections.inventario) sections.inventario.hidden = hash !== '#inventario';
        if (sections.ventas) sections.ventas.hidden = hash !== '#ventas';
        if (sections.estadisticas) sections.estadisticas.hidden = hash !== '#estadisticas';
        if (sections.cierre) sections.cierre.hidden = hash !== '#cierre';
        if (sections.admin) sections.admin.hidden = hash !== '#admin';
    }
    window.addEventListener('hashchange', setActive);
    setActive();

    // Toast de éxito al crear venta
    const saleToast = document.getElementById('saleSuccessToast');
    if (saleToast) {
        setTimeout(() => {
            saleToast.classList.add('toast-hide');
            setTimeout(() => {
                saleToast.remove();
            }, 400);
        }, 3000);
    }

    const serverErrorAlert = document.getElementById('serverErrorAlert');
    if (serverErrorAlert) {
        setTimeout(() => {
            serverErrorAlert.classList.add('alert-hide');
            setTimeout(() => {
                serverErrorAlert.style.display = 'none';
                serverErrorAlert.classList.remove('alert-hide');
            }, 400);
        }, 3000);
    }

    // Modal de producto
    const modalBackdrop = document.getElementById('productModal');
    const modalClose = document.getElementById('productModalClose');
    const modalImg = document.getElementById('productModalImg');
    const modalName = document.getElementById('productModalName');
    const modalPrice = document.getElementById('productModalPrice');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    const qtyValue = document.getElementById('qtyValue');
    const productAddToSaleBtn = document.getElementById('productAddToSale');
    const inventoryQuickSaleForm = document.getElementById('inventoryQuickSaleForm');
    const inventoryQuickProductIdInput = document.getElementById('inventoryQuickProductId');
    const inventoryQuickQuantityInput = document.getElementById('inventoryQuickQuantity');

    let currentQty = 1;
    let currentProductId = null;

    let numberFormatter;
    const getNumberFormatter = () => {
        if (!numberFormatter) {
            try {
                numberFormatter = new Intl.NumberFormat('es-CO');
            } catch (e) {
                numberFormatter = null;
            }
        }
        return numberFormatter;
    };

    const money = (value) => {
        const fmt = getNumberFormatter();
        if (fmt) {
            try {
                return fmt.format(value);
            } catch (e) {}
        }
        return String(value);
    };

    const renderModal = () => {
        qtyValue.textContent = String(currentQty);
        qtyMinus.disabled = currentQty <= 1;
        qtyMinus.style.opacity = qtyMinus.disabled ? '0.45' : '1';
        qtyMinus.style.cursor = qtyMinus.disabled ? 'not-allowed' : 'pointer';
    };

    const openModal = ({ id, name, price, image }) => {
        const unitPrice = Number(price || 0);
        currentQty = 1;
        currentProductId = id ? Number(id) : null;

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
                id: btn.dataset.id,
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

    if (productAddToSaleBtn && inventoryQuickSaleForm && inventoryQuickProductIdInput && inventoryQuickQuantityInput) {
        productAddToSaleBtn.addEventListener('click', () => {
            if (!currentProductId || currentQty < 1) return;

            inventoryQuickProductIdInput.value = String(currentProductId);
            inventoryQuickQuantityInput.value = String(currentQty);
            inventoryQuickSaleForm.requestSubmit();
        });

        inventoryQuickSaleForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(inventoryQuickSaleForm);

            try {
                const resp = await fetch(inventoryQuickSaleForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!resp.ok) {
                    closeModal();
                    return;
                }

                const data = await resp.json();

                // Actualiza el texto de stock en la tarjeta de inventario
                if (data && typeof data.product_id !== 'undefined' && typeof data.stock !== 'undefined') {
                    const cardBtn = document.querySelector(`.product-add[data-id="${data.product_id}"]`);
                    if (cardBtn) {
                        const card = cardBtn.closest('.product');
                        const badge = card?.querySelector('.product-badge');
                        if (badge) {
                            badge.textContent = 'Stock: ' + data.stock;
                        }
                    }

                    // Mantiene sincronizado el catálogo usado en ventas
                    const prod = findProduct(data.product_id);
                    if (prod) {
                        prod.stock = data.stock;
                    }

                    // Inserta el movimiento en el historial (si la tabla existe)
                    if (inventoryHistoryTbody && data.movement && data.movement.product) {
                        const m = data.movement;
                        const p = m.product;

                        const tr = document.createElement('tr');
                        const imgUrl = p.image_url || '';
                        const name = p.name || 'Producto';
                        const qty = typeof m.quantity !== 'undefined' ? m.quantity : '';
                        const stockNow = typeof p.stock !== 'undefined' ? p.stock : '';

                        let created = m.created_at || '';
                        // Formato simple a dd/mm/yyyy HH:MM si viene como YYYY-MM-DD HH:MM:SS
                        if (created && created.length >= 16) {
                            const [datePart, timePart] = created.split(' ');
                            const [y, mo, d] = datePart.split('-');
                            created = `${d}/${mo}/${y} ${timePart.substring(0,5)}`;
                        }

                        tr.innerHTML = `
                            <td>
                                ${imgUrl
                                    ? `<img src="${imgUrl}" alt="Imagen de ${name}" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; background:#f3f4f6;">`
                                    : '<span style="font-size:11px; color:#9ca3af; font-weight:800;">Sin imagen</span>'}
                            </td>
                            <td><strong>${name}</strong></td>
                            <td>${qty}</td>
                            <td>${stockNow}</td>
                            <td>${created}</td>
                        `;

                        // Elimina fila vacía si existe
                        const emptyRow = inventoryHistoryTbody.querySelector('tr[data-empty="1"]');
                        if (emptyRow) emptyRow.remove();

                        // Inserta al inicio del historial
                        if (inventoryHistoryTbody.firstChild) {
                            inventoryHistoryTbody.insertBefore(tr, inventoryHistoryTbody.firstChild);
                        } else {
                            inventoryHistoryTbody.appendChild(tr);
                        }

                        inventoryHistoryPage = 1;
                        updateInventoryHistoryPagination();
                    }
                }

                closeModal();
            } catch (err) {
                closeModal();
            }
        });
    }


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
    const saleProductsGrid = document.getElementById('saleProductsGrid');
    const saleItemsHidden = document.getElementById('saleItemsHidden');
    const saleTotalEl = document.getElementById('saleTotal');
    const salesSearch = document.getElementById('salesSearch');
    const salesTbody = document.getElementById('salesTbody');
    const salesPagination = document.getElementById('salesPagination');
    const saleInlineError = document.getElementById('saleInlineError');

    const saleConfirmBackdrop = document.getElementById('saleConfirmModal');
    const saleConfirmClose = document.getElementById('saleConfirmClose');
    const saleSummaryItemsEl = document.getElementById('saleSummaryItems');
    const saleSummaryPaymentEl = document.getElementById('saleSummaryPayment');
    const saleSummaryTotalEl = document.getElementById('saleSummaryTotal');
    const saleSummaryBackBtn = document.getElementById('saleSummaryBack');
    const saleSummaryConfirmBtn = document.getElementById('saleSummaryConfirm');

    const saleDetailBackdrop = document.getElementById('saleDetailModal');
    const saleDetailItemsEl = document.getElementById('saleDetailItems');
    const saleDetailPaymentEl = document.getElementById('saleDetailPayment');
    const saleDetailTotalEl = document.getElementById('saleDetailTotal');
    const saleDetailCodeEl = document.getElementById('saleDetailCode');
    const saleDetailDateTimeEl = document.getElementById('saleDetailDateTime');
    const saleDetailCloseBtn = document.getElementById('saleDetailClose');
    const saleDetailCloseFooterBtn = document.getElementById('saleDetailCloseFooter');

    const inventoryHistoryBackdrop = document.getElementById('inventoryHistoryModal');
    const openInventoryHistoryBtn = document.getElementById('openInventoryHistory');
    const inventoryHistoryCloseBtn = document.getElementById('inventoryHistoryClose');
    const inventoryHistoryTbody = document.getElementById('inventoryHistoryTbody');
    const inventoryHistoryPagination = document.getElementById('inventoryHistoryPagination');

    const ultimosVendidosTbody = document.getElementById('ultimosVendidosTbody');
    const ultimosVendidosPagination = document.getElementById('ultimosVendidosPagination');

    const cierreDateInput = document.getElementById('cierreDate');
    const salesDateInput = document.getElementById('salesDateFilter');
    const cierreTotalMoneyEl = document.getElementById('cierreTotalMoney');
    const cierreTotalProductsEl = document.getElementById('cierreTotalProducts');
    const cierreItemsTbody = document.getElementById('cierreItemsTbody');
    const cierrePagination = document.getElementById('cierrePagination');
    const cierrePrintBtn = document.getElementById('cierrePrintBtn');

    const PRODUCTS = @json($catalogoJson);
    const SALES_DAILY = @json($ventasDiariasJson);

    const findProduct = (id) => {
        const num = Number(id);
        if (!num) return null;
        return PRODUCTS.find(p => Number(p.id) === num) || null;
    };

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
        if (paymentInput.value) {
            setPayment(paymentInput.value);
        } else {
            // Ningún método seleccionado por defecto
            payOptions.forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-secondary');
                btn.setAttribute('aria-pressed', 'false');
            });
        }
    }

    const fmt = (value) => {
        const fmtInst = getNumberFormatter();
        if (fmtInst) {
            try { return fmtInst.format(value); } catch (e) {}
        }
        return String(value);
    };

    let saleErrorTimeout = null;
    const showSaleError = (message) => {
        if (!saleInlineError) return;

        if (!message) {
            saleInlineError.style.display = 'none';
            saleInlineError.classList.remove('alert-hide');
            if (saleErrorTimeout) {
                clearTimeout(saleErrorTimeout);
                saleErrorTimeout = null;
            }
            return;
        }

        saleInlineError.textContent = message;
        saleInlineError.style.display = 'block';
        saleInlineError.classList.remove('alert-hide');

        if (saleErrorTimeout) {
            clearTimeout(saleErrorTimeout);
        }
        saleErrorTimeout = setTimeout(() => {
            saleInlineError.classList.add('alert-hide');
            setTimeout(() => {
                saleInlineError.style.display = 'none';
                saleInlineError.classList.remove('alert-hide');
            }, 400);
        }, 3000);
    };

    // Carrito para la venta: { productId: quantity }
    const cart = new Map();

    const cartEntries = () => Array.from(cart.entries()).filter(([, qty]) => qty && qty > 0);

    const rebuildHiddenItems = () => {
        if (!saleItemsHidden) return;
        saleItemsHidden.innerHTML = '';
        const entries = cartEntries();
        entries.forEach(([productId, qty], index) => {
            const pid = document.createElement('input');
            pid.type = 'hidden';
            pid.name = `items[${index}][product_id]`;
            pid.value = String(productId);

            const q = document.createElement('input');
            q.type = 'hidden';
            q.name = `items[${index}][quantity]`;
            q.value = String(qty);

            saleItemsHidden.appendChild(pid);
            saleItemsHidden.appendChild(q);
        });
    };

    const openSaleModal = () => {
        showSaleError('');
        saleBackdrop.classList.add('open');
        saleBackdrop.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeSaleModal = () => {
        saleBackdrop.classList.remove('open');
        saleBackdrop.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const openSaleConfirmModal = () => {
        if (!saleConfirmBackdrop) return;
        saleConfirmBackdrop.classList.add('open');
        saleConfirmBackdrop.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeSaleConfirmModal = () => {
        if (!saleConfirmBackdrop) return;
        saleConfirmBackdrop.classList.remove('open');
        saleConfirmBackdrop.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const openSaleDetailModal = (sale) => {
        if (!saleDetailBackdrop || !saleDetailItemsEl || !saleDetailTotalEl) return;

        saleDetailItemsEl.innerHTML = '';
        const items = Array.isArray(sale.items) ? sale.items : [];
        let total = Number(sale.total || 0);

        items.forEach(it => {
            const name = it.name || 'Producto';
            const img = it.image_url || '';
            const qty = Number(it.quantity || 0);
            const unit = Number(it.unit_price || 0);
            const line = Number(it.line_total || (qty * unit));

            const div = document.createElement('div');
            div.className = 'summary-item';
            div.innerHTML = `
                <img class="summary-img" src="${img}" alt="Imagen de ${name}">
                <div class="summary-main">
                    <div class="summary-name">${name}</div>
                    <div class="summary-meta">Cantidad: ${qty} • Valor unitario: $${fmt(unit)}</div>
                </div>
                <div class="summary-line">$${fmt(line)}</div>
            `;
            saleDetailItemsEl.appendChild(div);
        });

        saleDetailTotalEl.textContent = '$' + fmt(total);

        if (saleDetailPaymentEl) {
            const method = sale.payment === 'nequi' ? 'Nequi' : 'Efectivo';
            saleDetailPaymentEl.textContent = method;
        }

        if (saleDetailCodeEl) {
            saleDetailCodeEl.textContent = sale.code ? (' ' + sale.code) : '';
        }

        if (saleDetailDateTimeEl) {
            saleDetailDateTimeEl.textContent = sale.datetime || '';
        }

        saleDetailBackdrop.classList.add('open');
        saleDetailBackdrop.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeSaleDetailModal = () => {
        if (!saleDetailBackdrop) return;
        saleDetailBackdrop.classList.remove('open');
        saleDetailBackdrop.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const HISTORY_PAGE_SIZE = 5;
    let inventoryHistoryPage = 1;

    const getInventoryHistoryRows = () => {
        if (!inventoryHistoryTbody) return [];
        return Array.from(inventoryHistoryTbody.querySelectorAll('tr')).filter(tr => !tr.dataset.empty);
    };

    function updateInventoryHistoryPagination() {
        const rows = getInventoryHistoryRows();
        const totalPages = Math.max(1, Math.ceil(rows.length / HISTORY_PAGE_SIZE));
        if (inventoryHistoryPage > totalPages) inventoryHistoryPage = totalPages;
        if (inventoryHistoryPage < 1) inventoryHistoryPage = 1;

        rows.forEach((tr, index) => {
            const page = Math.floor(index / HISTORY_PAGE_SIZE) + 1;
            tr.style.display = page === inventoryHistoryPage ? '' : 'none';
        });

        if (!inventoryHistoryPagination) return;

        if (rows.length <= HISTORY_PAGE_SIZE) {
            inventoryHistoryPagination.innerHTML = '';
            inventoryHistoryPagination.style.display = 'none';
            return;
        }

        inventoryHistoryPagination.style.display = 'flex';
        inventoryHistoryPagination.innerHTML = '';

        for (let p = 1; p <= totalPages; p++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(p);
            btn.className = 'page-pill' + (p === inventoryHistoryPage ? ' page-pill-active' : '');
            btn.addEventListener('click', () => {
                inventoryHistoryPage = p;
                updateInventoryHistoryPagination();
            });
            inventoryHistoryPagination.appendChild(btn);
        }
    }

    const ULTIMOS_PAGE_SIZE = 10;
    let ultimosPage = 1;

    const getUltimosRows = () => {
        if (!ultimosVendidosTbody) return [];
        return Array.from(ultimosVendidosTbody.querySelectorAll('tr')).filter(tr => !tr.dataset.empty);
    };

    function updateUltimosPagination() {
        const rows = getUltimosRows();
        if (!rows.length) {
            if (ultimosVendidosPagination) {
                ultimosVendidosPagination.innerHTML = '';
                ultimosVendidosPagination.style.display = 'none';
            }
            return;
        }

        const totalPages = Math.max(1, Math.ceil(rows.length / ULTIMOS_PAGE_SIZE));
        if (ultimosPage > totalPages) ultimosPage = totalPages;
        if (ultimosPage < 1) ultimosPage = 1;

        rows.forEach((tr, index) => {
            const page = Math.floor(index / ULTIMOS_PAGE_SIZE) + 1;
            tr.style.display = page === ultimosPage ? '' : 'none';
        });

        if (!ultimosVendidosPagination) return;

        if (rows.length <= ULTIMOS_PAGE_SIZE) {
            ultimosVendidosPagination.innerHTML = '';
            ultimosVendidosPagination.style.display = 'none';
            return;
        }

        ultimosVendidosPagination.style.display = 'flex';
        ultimosVendidosPagination.innerHTML = '';

        for (let p = 1; p <= totalPages; p++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(p);
            btn.className = 'page-pill' + (p === ultimosPage ? ' page-pill-active' : '');
            btn.addEventListener('click', () => {
                ultimosPage = p;
                updateUltimosPagination();
            });
            ultimosVendidosPagination.appendChild(btn);
        }
    }

    const SALES_PAGE_SIZE = 8;
    let salesPage = 1;

    const getSalesRows = () => {
        if (!salesTbody) return [];
        return Array.from(salesTbody.querySelectorAll('tr')).filter(tr => !tr.dataset.empty && tr.dataset.noresults !== '1');
    };

    function updateSalesPagination(resetPage = false) {
        if (!salesTbody) return;

        const rows = getSalesRows().filter(tr => tr.dataset.visible !== '0');

        if (!rows.length) {
            // No filas visibles (quizá por búsqueda); mostramos todas ocultas y dejamos mensaje aparte
            if (salesPagination) {
                salesPagination.innerHTML = '';
                salesPagination.style.display = 'none';
            }
            return;
        }

        const totalPages = Math.max(1, Math.ceil(rows.length / SALES_PAGE_SIZE));

        if (resetPage || salesPage > totalPages) salesPage = 1;
        if (salesPage < 1) salesPage = 1;

        // Primero ocultamos todas
        getSalesRows().forEach(tr => {
            tr.style.display = 'none';
        });

        rows.forEach((tr, index) => {
            const page = Math.floor(index / SALES_PAGE_SIZE) + 1;
            tr.style.display = page === salesPage ? '' : 'none';
        });

        if (!salesPagination) return;

        if (rows.length <= SALES_PAGE_SIZE) {
            salesPagination.innerHTML = '';
            salesPagination.style.display = 'none';
            return;
        }

        salesPagination.style.display = 'flex';
        salesPagination.innerHTML = '';

        for (let p = 1; p <= totalPages; p++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(p);
            btn.className = 'page-pill' + (p === salesPage ? ' page-pill-active' : '');
            btn.addEventListener('click', () => {
                salesPage = p;
                updateSalesPagination(false);
            });
            salesPagination.appendChild(btn);
        }
    }

    const CIERRE_PAGE_SIZE = 5;
    let cierrePage = 1;
    let cierreItemsData = [];
    let cierreShowAllForPrint = false;

    function renderCierreTable() {
        if (!cierreItemsTbody) return;

        if (!Array.isArray(cierreItemsData) || !cierreItemsData.length) {
            cierreItemsTbody.innerHTML = '<tr data-empty="1"><td colspan="4" style="color:#6b7280; font-weight:800;">No hay ventas registradas para este día.</td></tr>';
            if (cierrePagination) {
                cierrePagination.innerHTML = '';
                cierrePagination.style.display = 'none';
            }
            return;
        }

        const totalPages = Math.max(1, Math.ceil(cierreItemsData.length / CIERRE_PAGE_SIZE));
        if (!cierreShowAllForPrint) {
            if (cierrePage > totalPages) cierrePage = totalPages;
            if (cierrePage < 1) cierrePage = 1;
        }

        cierreItemsTbody.innerHTML = '';
        let sliceStart = 0;
        let sliceEnd = cierreItemsData.length;
        if (!cierreShowAllForPrint) {
            sliceStart = (cierrePage - 1) * CIERRE_PAGE_SIZE;
            sliceEnd = sliceStart + CIERRE_PAGE_SIZE;
        }

        cierreItemsData.slice(sliceStart, sliceEnd).forEach(it => {
            const name = it.name || 'Producto';
            const img = it.image_url || '';
            const qty = Number(it.quantity || 0);
            const amount = Number(it.amount || 0);

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    ${img
                        ? `<img src="${img}" alt="Imagen de ${name}" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; background:#f3f4f6;">`
                        : '<span style="font-size:11px; color:#9ca3af; font-weight:800;">Sin imagen</span>'}
                </td>
                <td><strong>${name}</strong></td>
                <td>${qty}</td>
                <td>$${fmt(amount)}</td>
            `;
            cierreItemsTbody.appendChild(tr);
        });

        if (!cierrePagination) return;

        if (totalPages <= 1 || cierreShowAllForPrint) {
            cierrePagination.innerHTML = '';
            cierrePagination.style.display = 'none';
            return;
        }

        cierrePagination.style.display = 'flex';
        cierrePagination.innerHTML = '';

        for (let p = 1; p <= totalPages; p++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = String(p);
            btn.className = 'page-pill' + (p === cierrePage ? ' page-pill-active' : '');
            btn.addEventListener('click', () => {
                cierrePage = p;
                renderCierreTable();
            });
            cierrePagination.appendChild(btn);
        }
    }

    const openInventoryHistoryModal = () => {
        if (!inventoryHistoryBackdrop) return;
        updateInventoryHistoryPagination();
        inventoryHistoryBackdrop.classList.add('open');
        inventoryHistoryBackdrop.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeInventoryHistoryModal = () => {
        if (!inventoryHistoryBackdrop) return;
        inventoryHistoryBackdrop.classList.remove('open');
        inventoryHistoryBackdrop.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };
    const recalcSaleTotal = () => {
        let total = 0;
        cartEntries().forEach(([productId, qty]) => {
            const product = findProduct(productId);
            if (!product) return;

            const maxStock = Number(product.stock || 0);
            const safeQty = (!maxStock || qty <= maxStock) ? qty : maxStock;
            const unitPrice = Number(product.price || 0);
            total += Math.max(0, safeQty) * Math.max(0, unitPrice);
        });
        saleTotalEl.textContent = '$' + fmt(total);
    };

    const buildSaleSummary = () => {
        if (!saleSummaryItemsEl || !saleSummaryTotalEl) return false;

        const entries = cartEntries();
        if (!entries.length) return false;

        saleSummaryItemsEl.innerHTML = '';
        let total = 0;

        entries.forEach(([productId, qty]) => {
            const product = findProduct(productId);
            if (!product) return;

            const unitPrice = Number(product.price || 0);
            const line = Math.max(0, qty) * Math.max(0, unitPrice);
            total += line;

            const item = document.createElement('div');
            item.className = 'summary-item';
            item.innerHTML = `
                <img class="summary-img" src="${product.image_url || ''}" alt="Imagen de ${product.name}">
                <div class="summary-main">
                    <div class="summary-name">${product.name}</div>
                    <div class="summary-meta">Cantidad: ${qty} • Valor unitario: $${fmt(unitPrice)}</div>
                </div>
                <div class="summary-line">$${fmt(line)}</div>
            `;
            saleSummaryItemsEl.appendChild(item);
        });

        saleSummaryTotalEl.textContent = '$' + fmt(total);

        if (saleSummaryPaymentEl && paymentInput) {
            const method = paymentInput.value === 'nequi'
                ? 'Nequi'
                : paymentInput.value === 'efectivo'
                    ? 'Efectivo'
                    : '';
            saleSummaryPaymentEl.textContent = method || '-';
        }

        return true;
    };

    const buildSaleProductsGrid = () => {
        if (!saleProductsGrid) return;
        saleProductsGrid.innerHTML = '';

        PRODUCTS.forEach(p => {
            const card = document.createElement('article');
            card.className = 'sale-product-card';
            card.innerHTML = `
                <div class="sale-product-media">
                    <img src="${p.image_url || ''}" alt="Imagen de ${p.name}">
                </div>
                <div class="sale-product-body">
                    <p class="sale-product-name">${p.name}</p>
                    <p class="sale-product-price">$${fmt(p.price)}</p>
                    <div class="sale-qty-controls" data-id="${p.id}">
                        <button type="button" class="qty-btn qty-minus">−</button>
                        <div class="qty-value">1</div>
                        <button type="button" class="qty-btn qty-plus">+</button>
                        <button type="button" class="sale-clear-btn" style="display:none;" aria-label="Limpiar cantidad">×</button>
                    </div>
                    <div style="font-size:11px; color:${p.stock > 0 ? '#16a34a' : '#b91c1c'}; font-weight:800;">Stock: ${p.stock}</div>
                </div>
            `;
            const qtyControls = card.querySelector('.sale-qty-controls');
            const qtyValueEl = card.querySelector('.qty-value');
            const minusBtn = card.querySelector('.qty-minus');
            const plusBtn = card.querySelector('.qty-plus');
            const clearBtn = card.querySelector('.sale-clear-btn');
            const productId = p.id;

            const updateQtyUI = () => {
                const current = cart.get(productId) || 0;
                qtyControls.hidden = false;
                if (!current) {
                    qtyValueEl.textContent = '0';
                    minusBtn.disabled = true;
                    if (clearBtn) clearBtn.style.display = 'none';
                    return;
                }
                qtyValueEl.textContent = String(current);
                minusBtn.disabled = false;
                if (clearBtn) clearBtn.style.display = current >= 5 ? 'inline-flex' : 'none';
            };

            plusBtn.addEventListener('click', () => {
                let current = cart.get(productId) || 0;
                const maxStock = Number(p.stock || 0);
                if (maxStock && current >= maxStock) {
                    showSaleError(`No hay más stock disponible para ${p.name}.`);
                    return;
                }
                current = current ? current + 1 : 1;
                cart.set(productId, current);
                rebuildHiddenItems();
                recalcSaleTotal();
                updateQtyUI();
            });

            minusBtn.addEventListener('click', () => {
                let current = cart.get(productId) || 0;
                if (!current) return;
                current -= 1;
                if (current <= 0) {
                    cart.delete(productId);
                } else {
                    cart.set(productId, current);
                }
                rebuildHiddenItems();
                recalcSaleTotal();
                updateQtyUI();
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    if (!cart.has(productId)) return;
                    cart.delete(productId);
                    rebuildHiddenItems();
                    recalcSaleTotal();
                    updateQtyUI();
                });
            }

            updateQtyUI();
            saleProductsGrid.appendChild(card);
        });
    };

    if (openSaleModalBtn) {
        openSaleModalBtn.addEventListener('click', () => {
            // Reset carrito y grilla
            cart.clear();
            rebuildHiddenItems();
            recalcSaleTotal();
            buildSaleProductsGrid();
            if (paymentInput) {
                paymentInput.value = '';
            }
            payOptions.forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-secondary');
                btn.setAttribute('aria-pressed', 'false');
            });
            openSaleModal();
        });
    }
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

    // Validación de campos obligatorios antes de crear la venta
    const saleForm = document.getElementById('saleForm');
    let allowDirectSubmit = false;
    if (saleForm) {
        saleForm.addEventListener('submit', (e) => {
            if (allowDirectSubmit) {
                allowDirectSubmit = false;
                return;
            }

            e.preventDefault();

            const errors = [];

            if (!paymentInput || !paymentInput.value) {
                errors.push('Debes seleccionar el método de pago (Efectivo o Nequi).');
            }

            const entries = cartEntries();
            if (!entries.length) {
                errors.push('Debes seleccionar al menos un producto para la venta.');
            }

            entries.forEach(([productId, qty]) => {
                const product = findProduct(productId);
                if (!product) return;

                const qVal = Number(qty || 0);
                if (!qVal || qVal < 1) {
                    errors.push(`Ingresa una cantidad válida (>= 1) para ${product.name}.`);
                    return;
                }

                const maxStock = Number(product.stock || 0);
                if (maxStock && qVal > maxStock) {
                    errors.push(`No hay stock suficiente para ${product.name} (máx: ${maxStock}).`);
                }
            });

            if (errors.length) {
                showSaleError(errors[0]);
                return;
            }

            if (!buildSaleSummary()) {
                showSaleError('Debes seleccionar al menos un producto para la venta.');
                return;
            }

            openSaleConfirmModal();
        });
    }

    if (saleSummaryBackBtn) {
        saleSummaryBackBtn.addEventListener('click', () => {
            closeSaleConfirmModal();
        });
    }

    if (saleConfirmClose) {
        saleConfirmClose.addEventListener('click', () => {
            closeSaleConfirmModal();
        });
    }

    if (saleConfirmBackdrop) {
        saleConfirmBackdrop.addEventListener('click', (e) => {
            if (e.target === saleConfirmBackdrop) closeSaleConfirmModal();
        });
    }

    if (saleSummaryConfirmBtn && saleForm) {
        saleSummaryConfirmBtn.addEventListener('click', () => {
            allowDirectSubmit = true;
            closeSaleConfirmModal();
            saleForm.submit();
        });
    }

    if (saleDetailCloseBtn) {
        saleDetailCloseBtn.addEventListener('click', closeSaleDetailModal);
    }
    if (saleDetailCloseFooterBtn) {
        saleDetailCloseFooterBtn.addEventListener('click', closeSaleDetailModal);
    }
    if (saleDetailBackdrop) {
        saleDetailBackdrop.addEventListener('click', (e) => {
            if (e.target === saleDetailBackdrop) closeSaleDetailModal();
        });
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && saleDetailBackdrop?.classList.contains('open')) closeSaleDetailModal();
    });

    if (openInventoryHistoryBtn) {
        openInventoryHistoryBtn.addEventListener('click', openInventoryHistoryModal);
    }
    if (inventoryHistoryCloseBtn) {
        inventoryHistoryCloseBtn.addEventListener('click', closeInventoryHistoryModal);
    }
    if (inventoryHistoryBackdrop) {
        inventoryHistoryBackdrop.addEventListener('click', (e) => {
            if (e.target === inventoryHistoryBackdrop) closeInventoryHistoryModal();
        });
    }

    if (salesSearch && salesTbody) {
        salesSearch.addEventListener('input', () => {
            const q = salesSearch.value.toLowerCase().trim();

            const rows = getSalesRows();
            let matches = 0;

            rows.forEach(tr => {
                const text = tr.textContent.toLowerCase();
                const ok = text.includes(q);
                tr.dataset.visible = ok ? '1' : '0';
                if (ok) matches++;
            });

            // Mensaje "sin resultados" si no hay coincidencias
            let noRow = salesTbody.querySelector('tr[data-noresults="1"]');
            if (matches === 0) {
                if (!noRow) {
                    noRow = document.createElement('tr');
                    noRow.dataset.noresults = '1';
                    noRow.innerHTML = '<td colspan="6" style="color:#6b7280; font-weight:800;">Sin resultados para la búsqueda.</td>';
                    salesTbody.appendChild(noRow);
                }
            } else if (noRow) {
                noRow.remove();
            }

            salesPage = 1;
            updateSalesPagination(true);
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

    document.querySelectorAll('.view-sale-detail').forEach(btn => {
        btn.addEventListener('click', () => {
            const code = btn.dataset.saleCode || '';
            const datetime = btn.dataset.datetime || '';
            const payment = btn.dataset.payment || '';
            const total = Number(btn.dataset.total || 0);
            let items = [];
            try {
                items = JSON.parse(btn.dataset.items || '[]');
            } catch (e) {
                items = [];
            }

            openSaleDetailModal({ code, datetime, payment, total, items });
        });
    });

    // Cierre - resumen diario
    async function loadCierreFor(dateStr) {
        if (!cierreTotalMoneyEl || !cierreTotalProductsEl || !cierreItemsTbody) return;

        cierreTotalMoneyEl.textContent = '$0';
        cierreTotalProductsEl.textContent = '0';
        cierreItemsTbody.innerHTML = '<tr><td colspan="4" style="color:#6b7280; font-weight:800;">Cargando información...</td></tr>';

        try {
            const resp = await fetch(`/cierre-data?date=${encodeURIComponent(dateStr)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!resp.ok) {
                throw new Error('Error al cargar cierre');
            }

            const data = await resp.json();

            const totalMoney = Number(data.total_money || 0);
            const totalProducts = Number(data.total_products || 0);
            const items = Array.isArray(data.items) ? data.items : [];

            cierreTotalMoneyEl.textContent = '$' + fmt(totalMoney);
            cierreTotalProductsEl.textContent = String(totalProducts);

            cierreItemsData = items;
            cierrePage = 1;
            renderCierreTable();
        } catch (e) {
            cierreItemsTbody.innerHTML = '<tr><td colspan="4" style="color:#b91c1c; font-weight:800;">No se pudo cargar el cierre. Intenta de nuevo.</td></tr>';
        }
    }

    if (cierreDateInput) {
        // Por defecto, usar el último día con ventas si está en SALES_DAILY; si no, hoy
        let defaultDate = null;
        if (Array.isArray(SALES_DAILY) && SALES_DAILY.length) {
            const all = SALES_DAILY.map(r => r.date).filter(Boolean).sort();
            if (all.length) {
                defaultDate = all[all.length - 1];
            }
        }
        if (!defaultDate) {
            const today = new Date();
            const y = today.getFullYear();
            const m = String(today.getMonth() + 1).padStart(2, '0');
            const d = String(today.getDate()).padStart(2, '0');
            defaultDate = `${y}-${m}-${d}`;
        }

        cierreDateInput.value = defaultDate;
        loadCierreFor(defaultDate);

        cierreDateInput.addEventListener('change', () => {
            if (!cierreDateInput.value) return;
            loadCierreFor(cierreDateInput.value);
        });
    }

    if (salesDateInput) {
        salesDateInput.addEventListener('change', () => {
            const value = salesDateInput.value;
            const url = new URL(window.location.href);

            if (value) {
                url.searchParams.set('ventas_date', value);
            } else {
                url.searchParams.delete('ventas_date');
            }

            url.hash = '#ventas';
            window.location.href = url.toString();
        });
    }

    if (cierrePrintBtn) {
        cierrePrintBtn.addEventListener('click', () => {
            const prevHash = window.location.hash;
            const prevPage = cierrePage;

            // Aseguramos que la pestaña de cierre esté visible
            window.location.hash = '#cierre';
            if (typeof setActive === 'function') {
                setActive();
            }

            // Mostrar todas las filas del cierre para la impresión
            cierreShowAllForPrint = true;
            renderCierreTable();

            document.body.classList.add('print-cierre');

            const handleAfterPrint = () => {
                document.body.classList.remove('print-cierre');
                window.removeEventListener('afterprint', handleAfterPrint);

                // Restaurar la paginación normal después de imprimir
                cierreShowAllForPrint = false;
                cierrePage = prevPage;
                renderCierreTable();

                if (prevHash && prevHash !== '#cierre') {
                    window.location.hash = prevHash;
                    if (typeof setActive === 'function') {
                        setActive();
                    }
                }
            };

            window.addEventListener('afterprint', handleAfterPrint);
            window.print();
        });
    }


    // Configura la paginación inicial del historial (si hay datos)
    if (inventoryHistoryTbody && inventoryHistoryPagination) {
        updateInventoryHistoryPagination();
    }

    // Configura la paginación inicial de últimos productos vendidos
    if (ultimosVendidosTbody && ultimosVendidosPagination) {
        updateUltimosPagination();
    }

    // Configura la paginación inicial de ventas (si hay datos)
    if (salesTbody && salesPagination) {
        // Marca todas como visibles por defecto y aplica paginación
        getSalesRows().forEach(tr => {
            tr.dataset.visible = '1';
        });
        updateSalesPagination(true);
    }

    // ========================
    // Estadísticas - Ventas por día
    // ========================
    try {
        const statsDateInput = document.getElementById('statsDateFilter');
        const statsDayTotal = document.getElementById('statsDayTotal');

        if (statsDateInput && statsDayTotal && Array.isArray(SALES_DAILY)) {
            const dailyByDate = {};
            SALES_DAILY.forEach(row => {
                if (!row || !row.date) return;
                dailyByDate[row.date] = (row.total || 0);
            });

            const allDates = Object.keys(dailyByDate).sort();

            function setDayTotal(dateStr) {
                const total = dailyByDate[dateStr] || 0;
                statsDayTotal.textContent = '$' + total.toLocaleString('es-CO');
            }

            if (allDates.length > 0) {
                const latest = allDates[allDates.length - 1];
                statsDateInput.value = latest;
                setDayTotal(latest);
            }

            statsDateInput.addEventListener('change', () => {
                const value = statsDateInput.value;
                if (!value) {
                    statsDayTotal.textContent = '$0';
                    return;
                }
                setDayTotal(value);
            });
        }
    } catch (e) {
        console.error('Error inicializando estadísticas diarias', e);
    }
</script>
</body>
</html>
