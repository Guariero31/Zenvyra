@extends('store.layout', ['navClass' => 'nav--solid'])

@section('title', 'Contato')
@section('og_title', 'Contato — ' . ($theme->nome_loja ?? $tenant->te_name))
@section('og_description', 'Entre em contato com ' . ($theme->nome_loja ?? $tenant->te_name) . '. Estamos aqui para ajudar.')
@section('meta_description', 'Entre em contato com ' . ($theme->nome_loja ?? $tenant->te_name) . '. Estamos aqui para ajudar.')

@push('styles')
<style>
    .contact-wrap {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        padding: 3rem 2rem;
        max-width: 960px;
        margin: 0 auto;
    }

    /* LEFT — INFO */
    .contact-info__title {
        font-size: 30px;
        font-style: italic;
        font-weight: 500;
        letter-spacing: -1px;
        line-height: 1.1;
        margin-bottom: 2.5rem;
    }
    .contact-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 1.5rem;
    }
    .contact-item__icon {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        color: #9ca3af;
        margin-top: 1px;
    }
    .contact-item__label {
        font-size: 10px;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #9ca3af;
        margin-bottom: 2px;
    }
    .contact-item__value { font-size: 13px; font-weight: 500; }

    /* RIGHT — FORM */
    .contact-form__title {
        font-size: 12px;
        font-weight: 500;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 2rem;
        color: #9ca3af;
    }
    .cf-field {
        margin-bottom: 1.75rem;
    }
    .cf-label {
        display: block;
        font-size: 10px;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #9ca3af;
        margin-bottom: 6px;
    }
    .cf-input,
    .cf-textarea {
        width: 100%;
        border: none;
        border-bottom: 1px solid #e5e7eb;
        padding: 6px 0 10px;
        font-size: 14px;
        font-family: inherit;
        background: transparent;
        outline: none;
        color: #111;
        transition: border-color .15s;
        resize: none;
    }
    .cf-input:focus,
    .cf-textarea:focus { border-color: #111; }
    .cf-textarea { min-height: 100px; }

    .cf-submit {
        margin-top: .5rem;
        padding: 12px 28px;
        background: #111;
        color: #fff;
        border: none;
        cursor: pointer;
        font-size: 11px;
        letter-spacing: .1em;
        text-transform: uppercase;
        font-family: inherit;
        border-radius: 6px;
        transition: background .15s;
    }
    .cf-submit:hover { background: #333; }

    .cf-success {
        display: none;
        padding: 14px 16px;
        background: #f0fdf4;
        border-radius: 8px;
        font-size: 13px;
        color: #166534;
        margin-top: 1rem;
    }

    @media (max-width: 700px) {
        .contact-wrap { grid-template-columns: 1fr; gap: 2.5rem; padding: 2rem 1rem; }
    }
</style>
@endpush

@section('content')

<div class="contact-wrap">

    {{-- LEFT --}}
    <div class="contact-info">
        <h1 class="contact-info__title">Entre em<br>contato</h1>

        @if($whatsapp)
        <div class="contact-item">
            <svg class="contact-item__icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            <div>
                <div class="contact-item__label">WhatsApp</div>
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener" class="contact-item__value">{{ $whatsapp }}</a>
            </div>
        </div>
        @endif

        @if($email)
        <div class="contact-item">
            <svg class="contact-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <div>
                <div class="contact-item__label">E-mail</div>
                <a href="mailto:{{ $email }}" class="contact-item__value">{{ $email }}</a>
            </div>
        </div>
        @endif

        @if($endereco)
        <div class="contact-item">
            <svg class="contact-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <div>
                <div class="contact-item__label">Endereço</div>
                <div class="contact-item__value">{{ $endereco }}</div>
            </div>
        </div>
        @endif

        @if(!$whatsapp && !$email && !$endereco)
        <div class="contact-item">
            <svg class="contact-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <div>
                <div class="contact-item__label">Atendimento</div>
                <div class="contact-item__value">Use o formulário ao lado</div>
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT — FORM --}}
    <div class="contact-form">
        <div class="contact-form__title">Enviar mensagem</div>

        @if(session('contact_success'))
        <div class="cf-success" style="display:block;margin-bottom:1.5rem;">
            Mensagem enviada! Retornaremos em breve.
        </div>
        @else

        @if($errors->any())
        <div style="padding:12px 16px;background:#fef2f2;border-radius:8px;font-size:13px;color:#991b1b;margin-bottom:1.25rem;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form action="{{ route('store.contact.send', $tenant->te_slug) }}" method="POST">
            @csrf
            <div class="cf-field">
                <label class="cf-label" for="cf_name">Seu nome</label>
                <input class="cf-input" type="text" id="cf_name" name="name"
                       value="{{ old('name') }}" required autocomplete="name">
            </div>
            <div class="cf-field">
                <label class="cf-label" for="cf_email">E-mail</label>
                <input class="cf-input" type="email" id="cf_email" name="email"
                       value="{{ old('email') }}" required autocomplete="email">
            </div>
            <div class="cf-field">
                <label class="cf-label" for="cf_message">Mensagem</label>
                <textarea class="cf-textarea" id="cf_message" name="message"
                          required>{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="cf-submit">Enviar mensagem</button>
        </form>

        @endif
    </div>

</div>

<footer class="footer">
    <span class="footer__brand">© {{ date('Y') }} {{ $theme->nome_loja ?? $tenant->te_name }}</span>
    <div class="footer__links">
        <a href="{{ route('store.home', $tenant->te_slug) }}" class="footer__link">Início</a>
        <a href="{{ route('store.catalog', $tenant->te_slug) }}" class="footer__link">Catálogo</a>
    </div>
</footer>

@endsection
