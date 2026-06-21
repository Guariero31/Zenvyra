<style>
.pi-preview {
    margin-bottom: 1.5rem;
    padding: 1rem 1.25rem 1.25rem;
    border-radius: 12px;
    border: 1px solid rgb(var(--color-gray-200));
    background: rgb(var(--color-gray-50));
}
.dark .pi-preview {
    border-color: rgba(var(--color-gray-700), 0.5);
    background: rgba(var(--color-gray-900), 0.35);
}
.pi-preview-header { margin-bottom: 1rem; }
.pi-preview-title {
    margin: 0 0 0.25rem;
    font-size: 0.95rem;
    font-weight: 600;
    color: rgb(var(--color-gray-900));
}
.dark .pi-preview-title { color: rgb(var(--color-gray-100)); }
.pi-preview-desc {
    margin: 0;
    font-size: 12px;
    line-height: 1.5;
    color: rgb(var(--color-gray-500));
}
.dark .pi-preview-desc { color: rgb(var(--color-gray-400)); }

.pi-preview-modes {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
}
.pi-preview-card { min-width: 0; }
.pi-preview-mode-label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: rgb(var(--color-gray-500));
}

.pi-preview-shell {
    overflow: hidden;
    border-radius: 10px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
}
.pi-preview-layout {
    display: grid;
    grid-template-columns: 92px 1fr;
    min-height: 148px;
}
.pi-preview-shell-light {
    background: var(--pi-page);
}
.pi-preview-shell-dark {
    background: #0f172a;
    border-color: rgba(255, 255, 255, 0.08);
}
.pi-preview-sidebar {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 10px 8px;
    border-right: 1px solid rgba(15, 23, 42, 0.06);
}
.pi-preview-shell-light .pi-preview-sidebar {
    background: var(--pi-sidebar);
}
.pi-preview-shell-dark .pi-preview-sidebar {
    background: #1e293b;
    border-right-color: rgba(255, 255, 255, 0.06);
}
.pi-preview-brand {
    margin-bottom: 4px;
    font-size: 10px;
    font-weight: 700;
    line-height: 1.3;
    color: rgb(var(--color-gray-800));
    word-break: break-word;
}
.pi-preview-shell-dark .pi-preview-brand { color: #e2e8f0; }
.pi-preview-nav-item {
    padding: 4px 6px;
    border-radius: 6px;
    font-size: 10px;
    color: rgb(var(--color-gray-500));
}
.pi-preview-shell-dark .pi-preview-nav-item { color: #94a3b8; }
.pi-preview-nav-active {
    font-weight: 600;
    color: var(--pi-primary);
    background: color-mix(in srgb, var(--pi-primary) 12%, transparent);
}
.pi-preview-main { display: flex; flex-direction: column; min-width: 0; }
.pi-preview-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 8px 10px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
}
.pi-preview-shell-light .pi-preview-topbar { background: var(--pi-topbar); }
.pi-preview-shell-dark .pi-preview-topbar {
    background: #1e293b;
    border-bottom-color: rgba(255, 255, 255, 0.06);
}
.pi-preview-topbar-title {
    font-size: 11px;
    font-weight: 600;
    color: rgb(var(--color-gray-700));
}
.pi-preview-shell-dark .pi-preview-topbar-title { color: #e2e8f0; }
.pi-preview-btn {
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 600;
    color: #fff;
    background: var(--pi-primary);
}
.pi-preview-content {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 10px;
}
.pi-preview-shell-light .pi-preview-content { background: var(--pi-content); }
.pi-preview-shell-dark .pi-preview-content { background: #0f172a; }
.pi-preview-chip {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 600;
    border: 1px solid transparent;
}
.pi-preview-chip-primary {
    color: var(--pi-primary);
    background: color-mix(in srgb, var(--pi-primary) 14%, transparent);
    border-color: color-mix(in srgb, var(--pi-primary) 28%, transparent);
}
.pi-preview-chip-success {
    color: var(--pi-success);
    background: color-mix(in srgb, var(--pi-success) 14%, transparent);
    border-color: color-mix(in srgb, var(--pi-success) 28%, transparent);
}
.pi-preview-chip-warning {
    color: var(--pi-warning);
    background: color-mix(in srgb, var(--pi-warning) 14%, transparent);
    border-color: color-mix(in srgb, var(--pi-warning) 28%, transparent);
}
.pi-preview-chip-danger {
    color: var(--pi-danger);
    background: color-mix(in srgb, var(--pi-danger) 14%, transparent);
    border-color: color-mix(in srgb, var(--pi-danger) 28%, transparent);
}

.pi-banner {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 8px;
    background: rgb(var(--color-primary-50));
    border: 1px solid rgb(var(--color-primary-200));
    margin-bottom: 1.5rem;
    font-size: 13px;
    color: rgb(var(--color-primary-800));
    line-height: 1.5;
}
.dark .pi-banner {
    background: rgba(var(--color-primary-400), 0.1);
    border-color: rgba(var(--color-primary-400), 0.25);
    color: rgb(var(--color-primary-300));
}
.pi-banner-icon {
    flex-shrink: 0;
    margin-top: 1px;
    color: rgb(var(--color-primary-500));
}
.dark .pi-banner-icon {
    color: rgb(var(--color-primary-400));
}
</style>
