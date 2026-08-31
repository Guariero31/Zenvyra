<div>
    <style>
        .zv-qa-grid { display: grid; gap: 1rem; grid-template-columns: repeat(1, minmax(0, 1fr)); }
        @media (min-width: 768px) { .zv-qa-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

        .zv-qa-card { display: block; border-radius: 0.75rem; padding: 1.5rem; background: #fff; border: 1px solid rgba(17, 24, 39, 0.06); text-decoration: none; transition: border-color 0.15s ease, transform 0.15s ease; }
        .dark .zv-qa-card { background: #1f2937; border-color: rgba(255, 255, 255, 0.06); }
        .zv-qa-card:hover { border-color: #f59e0b; transform: translateY(-1px); }

        .zv-qa-title { font-size: 1rem; font-weight: 700; color: #111827; }
        .dark .zv-qa-title { color: #f9fafb; }

        .zv-qa-desc { margin-top: 0.35rem; font-size: 0.85rem; color: #6b7280; }
        .dark .zv-qa-desc { color: #9ca3af; }
    </style>

    <div class="zv-qa-grid">
        @foreach ($actions as $action)
            <a href="{{ $action['url'] }}" class="zv-qa-card">
                <div class="zv-qa-title">{{ $action['title'] }}</div>
                <div class="zv-qa-desc">{{ $action['description'] }}</div>
            </a>
        @endforeach
    </div>
</div>
