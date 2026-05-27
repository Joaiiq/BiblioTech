<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Emprestimos;
use App\Models\Pagamento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $pagamento = DB::transaction(function () use ($emprestimo, $membro, $validated) {
            $recusado = ($validated['metodo'] ?? null) === Pagamento::METODO_CARTAO
                && ($validated['card_last_digits'] ?? null) === '0000';

            $pagamento = Pagamento::create([
                'membro_id' => $membro->id,
                'emprestimo_id' => $emprestimo->id,
                'codigo' => 'BT-PAY-' . now()->format('ymd') . '-' . Str::upper(Str::random(6)),
                'metodo' => $validated['metodo'],
                'status' => $recusado ? Pagamento::STATUS_RECUSADO : Pagamento::STATUS_APROVADO,
                'valor' => $emprestimo->valor_multa,
                'referencia' => 'MUL-' . str_pad((string) $emprestimo->id, 6, '0', STR_PAD_LEFT),
                'metadata' => [
                    'card_name' => $validated['card_name'] ?? null,
                    'card_last_digits' => $validated['card_last_digits'] ?? null,
                    'ambiente' => 'simulado',
                ],
                'pago_em' => $recusado ? null : now(),
            ]);

            if (!$recusado) {
                $emprestimo->update([
                    'multa_paga_em' => now(),
                    'multa_regularizada_por' => null,
                ]);

                AuditLog::record('multa_paga_online', 'Registrou pagamento fictício de multa.', $emprestimo, [
                    'membro' => $membro->nome,
                    'valor' => number_format((float) $emprestimo->valor_multa, 2, ',', '.'),
                    'codigo' => $pagamento->codigo,
                    'metodo' => $pagamento->metodo,
                ]);
            }

            return $pagamento;
        });

        if ($pagamento->status === Pagamento::STATUS_RECUSADO) {
            return redirect()
                ->route('pagamentos.checkout', $emprestimo)
                ->with('erro', 'Pagamento recusado no simulador. Use outros dígitos para aprovar.');
        }

        return redirect()
            ->route('pagamentos.comprovante', $pagamento)
            ->with('sucesso', 'Pagamento aprovado. Multa regularizada.');
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
