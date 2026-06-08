<?php

namespace App\Http\Controllers;

use App\Models\Membros;
use App\Models\Emprestimos;
use App\Models\AuditLog;
use App\Models\Livros;
use App\Notifications\MessageToMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PerfilMembrosController extends Controller
{
    public function index()
    {
        // Apenas administradores (gerente e bibliotecário) podem acessar
        $user = auth()->guard('web')->user();
        if (!$user || !in_array($user->tipo_usuario, ['gerente', 'bibliotecario'])) {
            abort(403, 'Você não tem permissão para consultar perfis de membros.');
        }

        $membros = Membros::with(['comentarios', 'emprestimos'])
            ->get()
            ->map(function ($membro) {
                $emprestimos = $membro->emprestimos;
                $atrasados = $emprestimos->filter(fn($emprestimo) => $emprestimo->isAtrasado());
                $multasNaoPagas = $emprestimos
                    ->where('status', Emprestimos::STATUS_DEVOLVIDO)
                    ->where('valor_multa', '>', 0)
                    ->whereNull('multa_paga_em')
                    ->sum('valor_multa');
                $emprestimosCompletados = $emprestimos
                    ->where('status', Emprestimos::STATUS_ENCERRADO)
                    ->count();
                $emprestimosAtivos = $emprestimos
                    ->whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
                    ->count();

                // Definir status do perfil
                $perfil = 'bom';
                if ($multasNaoPagas > 0) {
                    $perfil = 'com_multa';
                } elseif (count($atrasados) > 0) {
                    $perfil = 'devendo';
                }

                return [
                    'membro' => $membro,
                    'emprestimosAtrasados' => $atrasados,
                    'multasNaoPagas' => $multasNaoPagas,
                    'emprestimosCompletados' => $emprestimosCompletados,
                    'emprestimosAtivos' => $emprestimosAtivos,
                    'totalEmprestimos' => $emprestimos->count(),
                    'ultimoEmprestimo' => $emprestimos->sortByDesc('created_at')->first(),
                    'perfil' => $perfil,
                ];
            });

        // Agrupar por perfil para exibição
        $membrosDevendo = $membros->filter(fn($m) => $m['perfil'] === 'devendo');
        $membrosComMulta = $membros->filter(fn($m) => $m['perfil'] === 'com_multa');
        $membrosBom = $membros->filter(fn($m) => $m['perfil'] === 'bom');

        $allMembros = $membros->sortBy(fn($item) => $item['membro']->nome)->values();

        return view('admin.perfil-membros-dashboard', [
            'membrosDevendo' => $membrosDevendo,
            'membrosComMulta' => $membrosComMulta,
            'membrosBom' => $membrosBom,
            'totalMembros' => $membros->count(),
            'allMembros' => $allMembros,
        ]);
    }

    public function show(Membros $membro)
    {
        // Apenas administradores
        $user = auth()->guard('web')->user();
        if (!$user || !in_array($user->tipo_usuario, ['gerente', 'bibliotecario'])) {
            abort(403, 'Você não tem permissão para consultar este membro.');
        }

        // Detalhes do membro
        $emprestimos = Emprestimos::where('membro_id', $membro->id)
            ->with('livro.autor')
            ->orderBy('created_at', 'desc')
            ->get();

        $atrasados = $emprestimos->filter(fn($e) => $e->isAtrasado());
        $multasTotal = $emprestimos
            ->filter(fn($emprestimo) => $emprestimo->multaPendente())
            ->sum('valor_multa');

        $livrosDisponiveis = Livros::with('autor')
            ->whereNull('deleted_at')
            ->where('quantidade', '>', 0)
            ->orderBy('titulo')
            ->get(['id', 'titulo', 'autor_id', 'quantidade', 'e_bestseller']);

        return view('admin.membro-detalhes-dashboard', [
            'membro' => $membro,
            'emprestimos' => $emprestimos,
            'atrasados' => $atrasados,
            'multasTotal' => $multasTotal,
            'livrosDisponiveis' => $livrosDisponiveis,
        ]);
    }

    public function sendMessage(Request $request, Membros $membro)
    {
        $user = auth()->guard('web')->user();
        if (!$user || !in_array($user->tipo_usuario, ['gerente', 'bibliotecario'])) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $data = $request->validate([
            'subject' => 'required|string|max:191',
            'message' => 'required|string|max:2000',
        ]);

        // Enviar notificação de banco de dados
        try {
            $membro->notify(new \App\Notifications\MessageToMember($data['subject'], $data['message'], $user));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Falha ao enviar mensagem'], 500);
        }

        return response()->json(['ok' => true]);
    }

    public function resetPassword(Request $request, Membros $membro)
    {
        $user = auth()->guard('web')->user();
        if (!$user || !in_array($user->tipo_usuario, ['gerente', 'bibliotecario'])) {
            abort(403, 'Você não tem permissão para redefinir senha de membros.');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $membro->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => null,
        ])->save();

        AuditLog::record('senha_membro_redefinida', "Redefiniu a senha do membro {$membro->nome}.", $membro, [
            'email' => $membro->email,
            'responsavel' => $user->name,
        ]);

        $membro->notify(new MessageToMember(
            'Senha redefinida',
            'Sua senha foi redefinida pela equipe da biblioteca. Use a nova senha informada no atendimento.',
            $user
        ));

        return back()->with('sucesso', 'Senha do membro redefinida com sucesso.');
    }
}
