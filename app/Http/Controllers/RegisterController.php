<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\SubscriptionManager;
use App\Services\TenantTeamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        private readonly SubscriptionManager $subscriptionManager,
    ) {}

    public function show(): View
    {
        try {
            $plans = Plan::where('is_active', true)
                ->where('slug', '!=', config('subscriptions.evaluation_plan_slug'))
                ->orderBy('price')
                ->get();
        } catch (\Throwable) {
            $plans = collect();
        }

        return view('register', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'store_name.required' => 'Informe o nome da sua loja.',
            'name.required'       => 'Informe seu nome completo.',
            'email.required'      => 'Informe seu e-mail.',
            'password.required'   => 'Crie uma senha.',
            'password.min'        => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed'  => 'As senhas não conferem.',
        ]);

        if (\App\Models\User::where('email', $validated['email'])->exists()) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Este e-mail já está cadastrado. Tente fazer login.']);
        }

        $evaluationPlan = Plan::where('slug', config('subscriptions.evaluation_plan_slug'))->first();

        $tenant = DB::transaction(function () use ($validated, $evaluationPlan): Tenant {
            /** @var Tenant $tenant */
            $tenant = Tenant::create([
                'te_name'  => $validated['store_name'],
                'te_ativo' => true,
            ]);

            // Create the admin user and link to tenant
            $user = \App\Models\User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $tenant->users()->attach($user->getKey(), [
                'tu_papel'            => \App\Enums\TenantPortalRole::AdministradorCliente->value,
                'tu_rotulo_setor'     => null,
                'tu_commission_rate'  => null,
            ]);

            if ($evaluationPlan instanceof Plan) {
                $this->subscriptionManager->startEvaluation($tenant, $evaluationPlan);
            }

            $panelUrl = url("/app/{$tenant->te_slug}");

            Mail::to($user->email)->queue(new WelcomeMail(
                userName:  $user->name,
                storeName: $tenant->te_name,
                panelUrl:  $panelUrl,
            ));

            return $tenant;
        });

        return redirect("/app/{$tenant->te_slug}/login")->with(
            'status',
            'Sua loja foi criada! Faça login para começar.'
        );
    }
}
