<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifiable = $this->currentNotifiable();
        if (!$notifiable) {
            return $request->expectsJson()
                ? response()->json(['data' => []])
                : redirect()->route('login');
        }

        $notifications = $this->notificationsFor($notifiable, 80);

        if ($request->expectsJson()) {
            return response()->json([
                'unread_count' => $notifications->whereNull('read_at')->count(),
                'data' => $notifications->map(fn ($notification) => [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ]),
            ]);
        }

        $unreadCount = $notifications->whereNull('read_at')->count();
        $readCount = $notifications->whereNotNull('read_at')->count();
        $typeCounts = $notifications
            ->groupBy(fn ($notification) => $notification->meta['grupo'])
            ->map->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'readCount', 'typeCounts'));
    }

    public function markAllRead(Request $request)
    {
        $notifiable = $this->currentNotifiable();
        if ($notifiable) {
            DB::table('notifications')
                ->where('notifiable_type', $notifiable::class)
                ->where('notifiable_id', $notifiable->getAuthIdentifier())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
        return response()->json(['ok' => true]);
    }

    public function markRead(Request $request, string $id)
    {
        $notifiable = $this->currentNotifiable();

        if (!$notifiable) {
            abort(403, 'Você precisa estar logado para acessar notificações.');
        }

        $updated = $this->notificationQuery($notifiable)
            ->where('id', $id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'updated' => $updated]);
        }

        return redirect()->back()->with('sucesso', 'Notificação marcada como lida.');
    }

    public function clearRead(Request $request)
    {
        $notifiable = $this->currentNotifiable();

        if (!$notifiable) {
            abort(403, 'Você precisa estar logado para gerenciar notificações.');
        }

        $deleted = $this->notificationQuery($notifiable)
            ->whereNotNull('read_at')
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'deleted' => $deleted]);
        }

        return redirect()->back()->with('sucesso', "{$deleted} notificação(ões) lida(s) removida(s).");
    }

    private function notificationsFor($notifiable, int $limit)
    {
        return $this->notificationQuery($notifiable)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                $notification->data = json_decode($notification->data, true) ?: [];
                $notification->meta = $this->metaForType($notification->data['type'] ?? 'mensagem');
                $notification->action = $this->actionForNotification($notification->data);

                return $notification;
            });
    }

    private function currentNotifiable()
    {
        return Auth::guard('web')->user() ?: Auth::guard('membro')->user();
    }

    private function notificationQuery($notifiable)
    {
        return DB::table('notifications')
            ->where('notifiable_type', $notifiable::class)
            ->where('notifiable_id', $notifiable->getAuthIdentifier());
    }

    private function metaForType(string $type): array
    {
        if (str_contains($type, 'rejeitado')) {
            return ['grupo' => 'alertas', 'label' => 'Alerta', 'icon' => 'ph-warning-circle', 'classes' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300'];
        }

        if (str_contains($type, 'reserva')) {
            return ['grupo' => 'reservas', 'label' => 'Reserva', 'icon' => 'ph-bookmark-simple', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'];
        }

        if (str_contains($type, 'devolucao')) {
            return ['grupo' => 'devolucoes', 'label' => 'Devolução', 'icon' => 'ph-arrow-u-up-left', 'classes' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300'];
        }

        if (str_contains($type, 'emprestimo')) {
            return ['grupo' => 'emprestimos', 'label' => 'Empréstimo', 'icon' => 'ph-handshake', 'classes' => 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300'];
        }

        if (str_contains($type, 'senha')) {
            return ['grupo' => 'alertas', 'label' => 'Acesso', 'icon' => 'ph-key', 'classes' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300'];
        }

        if (str_contains($type, 'pagamento') || str_contains($type, 'multa')) {
            return ['grupo' => 'financeiro', 'label' => 'Multa', 'icon' => 'ph-currency-circle-dollar', 'classes' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300'];
        }

        return ['grupo' => 'mensagens', 'label' => 'Mensagem', 'icon' => 'ph-chat-circle-text', 'classes' => 'border-slate-200 bg-slate-50 text-slate-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-300'];
    }

    private function actionForNotification(array $data): ?array
    {
        $type = $data['type'] ?? 'mensagem';

        if (str_contains($type, 'emprestimo') || str_contains($type, 'devolucao')) {
            if (Auth::guard('web')->check()) {
                return ['label' => 'Abrir painel', 'url' => route('admin.emprestimos.index')];
            }

            return ['label' => 'Meus empréstimos', 'url' => route('emprestimos.historico')];
        }

        if (str_contains($type, 'reserva')) {
            if (Auth::guard('web')->check()) {
                return ['label' => 'Abrir operação', 'url' => route('admin.operacao.index')];
            }

            return ['label' => 'Minhas reservas', 'url' => route('emprestimos.historico')];
        }

        if (str_contains($type, 'pagamento') || str_contains($type, 'multa')) {
            if (Auth::guard('web')->check()) {
                return ['label' => 'Abrir multas', 'url' => route('admin.multas.index')];
            }

            return ['label' => 'Minha situação', 'url' => route('membros.situacao')];
        }

        if (!empty($data['livro_id'])) {
            return ['label' => 'Ver livro', 'url' => route('livros.show', $data['livro_id'])];
        }

        if (!empty($data['membro_id']) && Auth::guard('web')->check()) {
            return ['label' => 'Resolver acesso', 'url' => route('admin.membros.show', $data['membro_id'])];
        }

        return null;
    }
}
