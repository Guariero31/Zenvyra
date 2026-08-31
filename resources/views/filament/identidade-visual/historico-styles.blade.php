<style>
.pi-hist-section {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgb(var(--color-gray-200));
}
.dark .pi-hist-section {
    border-top-color: rgba(var(--color-gray-700), 0.5);
}
.pi-hist-title {
    font-size: 1rem;
    font-weight: 600;
    color: rgb(var(--color-gray-900));
    margin: 0 0 0.25rem;
}
.dark .pi-hist-title {
    color: rgb(var(--color-gray-100));
}
.pi-hist-desc {
    font-size: 12px;
    color: rgb(var(--color-gray-500));
    margin: 0 0 1.25rem;
}
.dark .pi-hist-desc {
    color: rgb(var(--color-gray-400));
}

.pi-hist-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 8px;
    border: 1px solid rgb(var(--color-gray-200));
}
.dark .pi-hist-wrap {
    border-color: rgba(var(--color-gray-700), 0.5);
}
.pi-hist-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    min-width: 760px;
}
.pi-hist-table thead tr {
    background: rgb(var(--color-gray-50));
    border-bottom: 1px solid rgb(var(--color-gray-200));
}
.dark .pi-hist-table thead tr {
    background: rgba(var(--color-gray-800), 0.5);
    border-bottom-color: rgba(var(--color-gray-700), 0.5);
}
.pi-hist-table th {
    padding: 10px 14px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgb(var(--color-gray-500));
    white-space: nowrap;
}
.dark .pi-hist-table th {
    color: rgb(var(--color-gray-400));
}
.pi-hist-table td {
    padding: 9px 14px;
    border-bottom: 1px solid rgb(var(--color-gray-100));
    color: rgb(var(--color-gray-700));
    vertical-align: middle;
    line-height: 1.45;
}
.dark .pi-hist-table td {
    border-bottom-color: rgba(var(--color-gray-700), 0.3);
    color: rgb(var(--color-gray-300));
}
.pi-hist-table tbody tr:last-child td {
    border-bottom: none;
}
.pi-hist-table tbody tr:hover td {
    background: rgb(var(--color-gray-50));
}
.dark .pi-hist-table tbody tr:hover td {
    background: rgba(var(--color-gray-700), 0.15);
}
.pi-col-data {
    white-space: nowrap;
    font-size: 12px;
    color: rgb(var(--color-gray-400));
}
.pi-col-autor { white-space: nowrap; font-weight: 500; }
.pi-col-campo { white-space: nowrap; font-weight: 600; }
.pi-col-valor { max-width: 200px; word-break: break-word; }

.pi-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 2px 9px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    border: 1px solid transparent;
}
.pi-badge-claro {
    background: #fefce8;
    color: #854d0e;
    border-color: #fde047;
}
.dark .pi-badge-claro {
    background: rgba(234, 179, 8, 0.1);
    color: #fde047;
    border-color: rgba(234, 179, 8, 0.25);
}
.pi-badge-escuro {
    background: #eef2ff;
    color: #3730a3;
    border-color: #c7d2fe;
}
.dark .pi-badge-escuro {
    background: rgba(99, 102, 241, 0.12);
    color: #a5b4fc;
    border-color: rgba(99, 102, 241, 0.3);
}
.pi-badge-geral {
    background: rgb(var(--color-gray-100));
    color: rgb(var(--color-gray-600));
    border-color: rgb(var(--color-gray-300));
}
.dark .pi-badge-geral {
    background: rgba(var(--color-gray-700), 0.3);
    color: rgb(var(--color-gray-400));
    border-color: rgba(var(--color-gray-600), 0.4);
}

.pi-swatch {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 1px solid rgba(0,0,0,0.12);
    vertical-align: middle;
    flex-shrink: 0;
}
.dark .pi-swatch { border-color: rgba(255,255,255,0.15); }
.pi-hex { font-family: ui-monospace, monospace; font-size: 12px; }
.pi-hex-novo { font-weight: 600; color: rgb(var(--color-gray-900)); }
.dark .pi-hex-novo { color: rgb(var(--color-gray-100)); }
.pi-em { color: rgb(var(--color-gray-300)); font-size: 13px; }
.dark .pi-em { color: rgb(var(--color-gray-600)); }

.pi-empty {
    padding: 32px 16px;
    text-align: center;
    border: 1px dashed rgb(var(--color-gray-300));
    border-radius: 8px;
    color: rgb(var(--color-gray-400));
    font-size: 13px;
}
.dark .pi-empty {
    border-color: rgba(var(--color-gray-600), 0.5);
    color: rgb(var(--color-gray-500));
}
</style>
