document.querySelectorAll('.pcard__img img, #galleryMainImg').forEach((image) => {
    const showFallback = () => {
        image.hidden = true;
        let fallback = image.parentElement.querySelector('[data-image-fallback]');
        if (!fallback) {
            fallback = document.createElement('span');
            fallback.dataset.imageFallback = '';
            fallback.className = 'muted';
            fallback.textContent = 'Imagem indisponível';
            image.parentElement.appendChild(fallback);
        }
        fallback.hidden = false;
    };
    image.addEventListener('error', showFallback);
    image.addEventListener('load', () => {
        image.hidden = false;
        const fallback = image.parentElement.querySelector('[data-image-fallback]');
        if (fallback) fallback.hidden = true;
    });
    if (image.complete && image.naturalWidth === 0) showFallback();
});

document.querySelectorAll('[data-gallery-image]').forEach((button) => {
    button.addEventListener('click', () => {
        const mainImage = document.getElementById('galleryMainImg');
        if (!mainImage) return;
        mainImage.src = button.dataset.galleryImage;
        document.querySelectorAll('[data-gallery-image]').forEach((thumbnail) => {
            const selected = thumbnail === button;
            thumbnail.classList.toggle('is-active', selected);
            thumbnail.setAttribute('aria-pressed', String(selected));
        });
    });
});

document.querySelectorAll('[data-product]').forEach((product) => {
    const price = product.querySelector('[data-product-price]');
    const priceHint = product.querySelector('[data-price-hint]');
    const stock = product.querySelector('[data-product-stock]');
    const selection = product.querySelector('[data-selection]');
    const whatsapp = product.querySelector('[data-whatsapp]');
    const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

    product.querySelectorAll('[data-variation]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.disabled) return;
            product.querySelectorAll('[data-variation]').forEach((option) => {
                const selected = option === button;
                option.classList.toggle('is-active', selected);
                option.setAttribute('aria-pressed', String(selected));
            });
            const formattedPrice = currency.format(Number(button.dataset.priceCents) / 100);
            if (price) price.textContent = Number(button.dataset.priceCents) > 0 ? formattedPrice : 'Consulte o preço';
            if (priceHint) priceHint.textContent = 'Preço da opção selecionada';
            if (selection) selection.textContent = `Selecionado: ${button.dataset.variation}`;
            if (stock) {
                const remaining = Number(button.dataset.stock);
                stock.textContent = remaining <= 5 ? `Últimas ${remaining} unidades` : 'Em estoque';
                stock.className = `stock-badge stock-badge--${remaining <= 5 ? 'low' : 'ok'}`;
            }
            if (whatsapp) {
                const url = new URL(whatsapp.href);
                const message = `Olá! Tenho interesse no produto: ${product.dataset.product}\nOpção: ${button.dataset.variation}\n${location.href.split('?')[0]}`;
                url.searchParams.set('text', message);
                whatsapp.href = url.toString();
                const label = whatsapp.querySelector('[data-whatsapp-label]');
                if (label) label.textContent = 'Pedir pelo WhatsApp';
            }
        });
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('.mobile-menu[open]').forEach((menu) => {
        menu.open = false;
        menu.querySelector('summary')?.focus();
    });
});

document.querySelectorAll('form[data-submit-label]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        if (!button) return;
        button.disabled = true;
        button.textContent = form.dataset.submitLabel;
        form.setAttribute('aria-busy', 'true');
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submit-label]').forEach((form) => {
        const button = form.querySelector('button[type="submit"]');
        if (!button) return;
        button.disabled = false;
        button.textContent = button.dataset.originalLabel || button.textContent;
        form.removeAttribute('aria-busy');
    });
});
