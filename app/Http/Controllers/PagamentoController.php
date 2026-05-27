<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Emprestimos;
use App\Models\Pagamento;
use App\Models\User;
use App\Notifications\PagamentoRegistrado;
use App\Notifications\PagamentoRevisado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PagamentoController extends Controller
{
    public function checkout(Emprestimos $emprestimo): View|RedirectResponse
    {
        $membro = auth()->guard('membro')->user();
        abort_unless($membro && $emprestimo->membro_id === $membro->id, 403);

        $emprestimo->load(['livro.autor', 'pagamentos' => fn ($query) => $query->latest()]);

        if (!$emprestimo->multaPendente()) {
            return redirect()
                ->route('membros.situacao')
                ->with('sucesso', 'Essa multa já está regularizada.');
        }

        return view('membros.pagamentos.checkout', [
            'membro' => $membro,
            'emprestimo' => $emprestimo,
            'pagamentoPendente' => $emprestimo->pagamentos
                ->where('status', Pagamento::STATUS_PENDENTE)
                ->first(),
        ]);
    }

    public function pagar(Request $request, Emprestimos $emprestimo): RedirectResponse
    {
        $membro = auth()->guard('membro')->user();
        abort_unless($membro && $emprestimo->membro_id === $membro->id, 403);

        $validated = $request->validate([
            'metodo' => ['required', 'in:pix,cartao,saldo_biblioteca'],
            'card_name' => ['required_if:metodo,cartao', 'nullable', 'string', 'max:120'],
            'card_last_digits' => ['required_if:metodo,cartao', 'nullable', 'digits:4'],
        ], [
            'metodo.required' => 'Escolha uma forma de pagamento.',
            'metodo.in' => 'Forma de pagamento inválida.',
            'card_name.required_if' => 'Informe o nome impresso no cartão fictício.',
            'card_last_digits.required_if' => 'Informe os 4 últimos dígitos do cartão fictício.',
            'card_last_digits.digits' => 'Informe exatamente 4 dígitos.',
        ]);

        if (!$emprestimo->multaPendente()) {
            return redirect()
                ->route('membros.situacao')
                ->with('sucesso', 'Essa multa já está regularizada.');
        }

        if ($emprestimo->pagamentos()->where('status', Pagamento::STATUS_PENDENTE)->exists()) {
            return redirect()
                ->route('pagamentos.checkout', $emprestimo)
                ->with('erro', 'Já existe um pagamento aguardando conferência da equipe.');
        }

        $pagamento = DB::transaction(function () use ($emprestimo, $membro, $validated) {
            $pagamento = Pagamento::create([
                'membro_id' => $membro->id,
                'emprestimo_id' => $emprestimo->id,
                'codigo' => 'BT-PAY-' . now()->format('ymd') . '-' . Str::upper(Str::random(6)),
                'metodo' => $validated['metodo'],
                'status' => Pagamento::STATUS_PENDENTE,
                'valor' => $emprestimo->valor_multa,
                'referencia' => 'MUL-' . str_pad((string) $emprestimo->id, 6, '0', STR_PAD_LEFT),
                'metadata' => [
                    'card_name' => $validated['card_name'] ?? null,
                    'card_last_digits' => $validated['card_last_digits'] ?? null,
                    'ambiente' => 'simulado',
                ],
                'pago_em' => null,
            ]);

            AuditLog::record('pagamento_enviado', 'Enviou pagamento fictício para conferência.', $emprestimo, [
                'membro' => $membro->nome,
                'valor' => number_format((float) $emprestimo->valor_multa, 2, ',', '.'),
                'codigo' => $pagamento->codigo,
                'metodo' => $pagamento->metodo,
            ]);

            return $pagamento;
        });

        $equipe = User::whereIn('tipo_usuario', ['gerente', 'bibliotecario'])->get();
        Notification::send($equipe, new PagamentoRegistrado($pagamento));

        return redirect()
            ->route('membros.situacao')
            ->with('sucesso', 'Pagamento enviado. A equipe vai conferir antes de regularizar a multa.');
    }

    public function aprovar(Pagamento $pagamento): RedirectResponse
    {
        abort_unless(auth()->guard('web')->check(), 403);

        if ($pagamento->status !== Pagamento::STATUS_PENDENTE) {
            return redirect()->back()->with('erro', 'Este pagamento já foi revisado.');
        }

        $pagamento->load(['membro', 'emprestimo']);

        DB::transaction(function () use ($pagamento) {
            $pagamento->update([
                'status' => Pagamento::STATUS_APROVADO,
                'reviewed_by' => auth()->guard('web')->id(),
                'reviewed_at' => now(),
                'pago_em' => now(),
            ]);

            $pagamento->emprestimo->update([
                'multa_paga_em' => now(),
                'multa_regularizada_por' => auth()->guard('web')->id(),
            ]);

            AuditLog::record('pagamento_aprovado', 'Aprovou pagamento fictício de multa.', $pagamento->emprestimo, [
                'membro' => $pagamento->membro?->nome,
                'valor' => number_format((float) $pagamento->valor, 2, ',', '.'),
                'codigo' => $pagamento->codigo,
            ]);
        });

        $pagamento->membro?->notify(new PagamentoRevisado($pagamento));

        return redirect()->back()->with('sucesso', 'Pagamento aprovado e multa regularizada.');
    }

    public function recusar(Request $request, Pagamento $pagamento): RedirectResponse
    {
        abort_unless(auth()->guard('web')->check(), 403);

        if ($pagamento->status !== Pagamento::STATUS_PENDENTE) {
            return redirect()->back()->with('erro', 'Este pagamento já foi revisado.');
        }

        $validated = $request->validate([
            'motivo_recusa' => ['nullable', 'string', 'max:255'],
        ]);

        $pagamento->load(['membro', 'emprestimo']);
        $pagamento->update([
            'status' => Pagamento::STATUS_RECUSADO,
            'reviewed_by' => auth()->guard('web')->id(),
            'reviewed_at' => now(),
            'motivo_recusa' => $validated['motivo_recusa'] ?? 'Conferência não aprovada pela equipe.',
        ]);

        AuditLog::record('pagamento_recusado', 'Recusou pagamento fictício de multa.', $pagamento->emprestimo, [
            'membro' => $pagamento->membro?->nome,
            'valor' => number_format((float) $pagamento->valor, 2, ',', '.'),
            'codigo' => $pagamento->codigo,
            'motivo' => $pagamento->motivo_recusa,
        ]);

        $pagamento->membro?->notify(new PagamentoRevisado($pagamento));

        return redirect()->back()->with('sucesso', 'Pagamento recusado. O membro foi notificado.');
    }

    public function comprovante(Pagamento $pagamento): View
    {
        $membro = auth()->guard('membro')->user();
        abort_unless($membro && $pagamento->membro_id === $membro->id, 403);

        $pagamento->load(['membro', 'emprestimo.livro.autor']);

        return view('membros.pagamentos.comprovante', compact('pagamento'));
    }

    public function comprovantePdf(Pagamento $pagamento)
    {
        $membro = auth()->guard('membro')->user();
        abort_unless($membro && $pagamento->membro_id === $membro->id, 403);

        $pagamento->load(['membro', 'emprestimo.livro.autor']);

        return Pdf::loadView('membros.pagamentos.comprovante-pdf', compact('pagamento'))
            ->setPaper('a4')
            ->download("comprovante-{$pagamento->codigo}.pdf");
    }
}
