<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\LivroController;
use App\Models\Livros;  
use App\Models\Membros;
use App\Models\Emprestimos;
use App\Http\Controllers\MembrosController;
use App\Http\Controllers\EmprestimoController;
use App\Http\Controllers\EmprestimoAdminController;
use App\Http\Controllers\AutorController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\MinhaBibliotecaController;
use App\Http\Controllers\CarteirinhaController;
use App\Http\Controllers\MultaController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\OperacaoController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\PagamentoController;
use Illuminate\Support\Facades\Schedule;

Route::get('/', function () {
    return view('welcome', [
        'landingStats' => [
            'livros' => Livros::count(),
            'membros' => Membros::count(),
            'emprestimos' => Emprestimos::whereIn('status', [
                Emprestimos::STATUS_APROVADO,
                Emprestimos::STATUS_RETIRADO,
                Emprestimos::STATUS_EM_USO,
                Emprestimos::STATUS_DEVOLUCAO_SOLICITADA,
            ])->count(),
            'pendencias' => Emprestimos::where('valor_multa', '>', 0)
                ->whereNull('multa_paga_em')
                ->count(),
        ],
    ]);
});

Route::get('/storage/{path}', [UploadController::class, 'show'])
    ->where('path', '.*')
    ->name('uploads.show');

//Middleware para proteger as rotas de cadastro de bibliotecário e livro, só o gerente pode acessar

Schedule::command('emprestimos:lembrar')->dailyAt('08:00');
// Rotas para o CRUD de Livros
// O 'auth' sozinho já chama o guard 'web' por padrão

    // rotas admin...
    Route::get('/admin/livros', [LivroController::class, 'dashboard'])->name('livros.index')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/livros/novo', [LivroController::class, 'create'])->name('livros.create')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/livros/salvar', [LivroController::class, 'store'])->name('livros.store')->middleware('tipo:gerente,bibliotecario');
    Route::delete('/admin/livros/{id}', [LivroController::class, 'destroy'])->name('livros.destroy')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/livros/{id}/editar', [LivroController::class, 'edit'])->name('livros.edit')->middleware('tipo:gerente,bibliotecario');
    Route::put('/admin/livros/{id}', [LivroController::class, 'update'])->name('livros.update')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/operacao', [OperacaoController::class, 'index'])->name('admin.operacao.index')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/emprestimos', [EmprestimoAdminController::class, 'index'])->name('admin.emprestimos.index')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/emprestimos/{id}/devolver', [EmprestimoAdminController::class, 'devolver'])->name('admin.emprestimos.devolver')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/emprestimos/{id}/aprovar', [EmprestimoAdminController::class, 'aprovar'])->name('admin.emprestimos.aprovar')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/emprestimos/{id}/retirar', [EmprestimoAdminController::class, 'retirar'])->name('admin.emprestimos.retirar')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/emprestimos/balcao', [EmprestimoAdminController::class, 'registrarBalcao'])->name('admin.emprestimos.balcao')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/emprestimos/{id}/iniciar-uso', [EmprestimoAdminController::class, 'iniciarUso'])->name('admin.emprestimos.iniciar-uso')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/emprestimos/{id}/encerrar', [EmprestimoAdminController::class, 'encerrar'])->name('admin.emprestimos.encerrar')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/emprestimos/{id}/regularizar-multa', [EmprestimoAdminController::class, 'regularizarMulta'])->name('admin.emprestimos.regularizar-multa')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/reservas/{id}/atender', [EmprestimoAdminController::class, 'atenderReserva'])->name('admin.reservas.atender')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/emprestimos/{id}/rejeitar', [EmprestimoAdminController::class, 'rejeitar'])->name('admin.emprestimos.rejeitar')->middleware('tipo:gerente,bibliotecario');
    // Rotas para o CRUD de Bibliotecários
    Route::post('/admin/bibliotecarios/salvar', [FuncionarioController::class, 'store'])->name('bibliotecarios.store')->middleware('tipo:gerente');
    Route::get('/admin/bibliotecarios/novo', [FuncionarioController::class, 'create'])->name('bibliotecarios.create')->middleware('tipo:gerente');
    Route::get('/admin/bibliotecarios', [FuncionarioController::class, 'index'])->name('bibliotecarios.index')->middleware('tipo:gerente');
    Route::get('/admin/bibliotecarios/{bibliotecario}/editar', [FuncionarioController::class, 'edit'])->name('bibliotecarios.edit')->middleware('tipo:gerente');
    Route::put('/admin/bibliotecarios/{bibliotecario}', [FuncionarioController::class, 'update'])->name('bibliotecarios.update')->middleware('tipo:gerente');
    Route::delete('/admin/bibliotecarios/{bibliotecario}', [FuncionarioController::class, 'destroy'])->name('bibliotecarios.destroy')->middleware('tipo:gerente');

Route::get('/emprestimos/{id}/comprovante', [EmprestimoController::class, 'comprovante'])
    ->middleware('membro')
    ->name('emprestimos.comprovante');

//Rotas para o CRUD de Membros
Route::get('/membros/novo', [MembrosController::class, 'create'])->name('membros.create')->middleware('tipo:gerente,bibliotecario');
Route::post('/membros/salvar', [MembrosController::class, 'store'])->name('membros.store')->middleware('tipo:gerente,bibliotecario');
Route::get('/admin/membros/{membro}/editar', [MembrosController::class, 'edit'])->name('membros.edit')->middleware('tipo:gerente,bibliotecario');
Route::put('/admin/membros/{membro}', [MembrosController::class, 'update'])->name('membros.update')->middleware('tipo:gerente,bibliotecario');

Route::post('/livros/{id}/alugar', [EmprestimoController::class, 'alugar'])->middleware('membro')->name('livros.alugar');
Route::post('/livros/{id}/reservar', [EmprestimoController::class, 'reservar'])->middleware('membro')->name('livros.reservar');
Route::post('/livros/{livro}/favorito', [FavoritoController::class, 'toggle'])->middleware('membro')->name('livros.favorito.toggle');
Route::get('/livros/{id}', [LivroController::class, 'show'])->name('livros.show'); // Rotas públicas para listar e ver detalhes dos livros
Route::post('/livros/{id}/comentarios', [LivroController::class, 'storeComentario'])
    ->middleware('auth:web,membro')
    ->name('livros.comentarios.store');
Route::put('/livros/{livro}/comentarios/{comentario}', [LivroController::class, 'updateComentario'])
    ->middleware('auth:web,membro')
    ->name('livros.comentarios.update');
Route::delete('/livros/{livro}/comentarios/{comentario}', [LivroController::class, 'destroyComentario'])
    ->middleware('auth:web,membro')
    ->name('livros.comentarios.destroy');

Route::get('/dashboard', [LivroController::class, 'dashboard'])
    ->middleware(['auth:web,membro'])
    ->name('dashboard');


    Route::middleware(['auth:web,membro'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::get('/admin/autores', [AutorController::class, 'index'])->name('autores.index')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/autores/novo', [AutorController::class, 'create'])->name('autores.create')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/autores/salvar', [AutorController::class, 'store'])->name('autores.store')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/autores/{id}/editar', [AutorController::class, 'edit'])->name('autores.edit')->middleware('tipo:gerente,bibliotecario');
    Route::put('/admin/autores/{id}', [AutorController::class, 'update'])->name('autores.update')->middleware('tipo:gerente,bibliotecario');
    Route::delete('/admin/autores/{id}', [AutorController::class, 'destroy'])->name('autores.destroy')->middleware('tipo:gerente,bibliotecario');
    Route::get('/autores/{id}', [AutorController::class, 'show'])->name('autores.show'); // Pública para ver detalhes

    Route::get('/admin/categorias', [CategoriaController::class, 'index'])->name('categorias.index')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/categorias', [CategoriaController::class, 'store'])->name('categorias.store')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/categorias/{categoria}/editar', [CategoriaController::class, 'edit'])->name('categorias.edit')->middleware('tipo:gerente,bibliotecario');
    Route::put('/admin/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update')->middleware('tipo:gerente,bibliotecario');

    Route::get('/meus-emprestimos', [EmprestimoController::class, 'historico'])
    ->middleware('membro')
    ->name('emprestimos.historico');
    Route::post('/meus-emprestimos/{id}/solicitar-devolucao', [EmprestimoController::class, 'solicitarDevolucao'])
        ->middleware('membro')
        ->name('emprestimos.solicitar-devolucao');
    Route::post('/meus-emprestimos/{id}/cancelar-solicitacao', [EmprestimoController::class, 'cancelarSolicitacao'])
        ->middleware('membro')
        ->name('emprestimos.cancelar-solicitacao');
    Route::post('/meus-emprestimos/{id}/renovar', [EmprestimoController::class, 'renovar'])
        ->middleware('membro')
        ->name('emprestimos.renovar');
    Route::post('/minhas-reservas/{id}/cancelar', [EmprestimoController::class, 'cancelarReserva'])
        ->middleware('membro')
        ->name('reservas.cancelar');
    Route::get('/meus-favoritos', [FavoritoController::class, 'index'])
        ->middleware('membro')
        ->name('favoritos.index');
    Route::get('/minha-biblioteca', [MinhaBibliotecaController::class, 'index'])
        ->middleware('membro')
        ->name('membros.biblioteca');
    Route::get('/minha-situacao', [MinhaBibliotecaController::class, 'situacao'])
        ->middleware('membro')
        ->name('membros.situacao');
    Route::get('/pagamentos/multa/{emprestimo}', [PagamentoController::class, 'checkout'])
        ->middleware('membro')
        ->name('pagamentos.checkout');
    Route::post('/pagamentos/multa/{emprestimo}', [PagamentoController::class, 'pagar'])
        ->middleware('membro')
        ->name('pagamentos.pagar');
    Route::get('/pagamentos/{pagamento}/comprovante', [PagamentoController::class, 'comprovante'])
        ->middleware('membro')
        ->name('pagamentos.comprovante');
    Route::get('/pagamentos/{pagamento}/comprovante/pdf', [PagamentoController::class, 'comprovantePdf'])
        ->middleware('membro')
        ->name('pagamentos.comprovante.pdf');
    Route::get('/minha-carteirinha', [CarteirinhaController::class, 'show'])
        ->middleware('membro')
        ->name('membros.carteirinha');
    Route::get('/minha-carteirinha/pdf', [CarteirinhaController::class, 'pdf'])
        ->middleware('membro')
        ->name('membros.carteirinha.pdf');

    // Rotas admin: Perfil de Membros
    Route::get('/admin/membros/perfis', [App\Http\Controllers\PerfilMembrosController::class, 'index'])->name('admin.membros.perfis')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/membros/{membro}', [App\Http\Controllers\PerfilMembrosController::class, 'show'])->name('admin.membros.show')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/membros/{membro}/message', [App\Http\Controllers\PerfilMembrosController::class, 'sendMessage'])->name('admin.membros.message')->middleware('tipo:gerente,bibliotecario');
    Route::put('/admin/membros/{membro}/senha', [App\Http\Controllers\PerfilMembrosController::class, 'resetPassword'])->name('admin.membros.password')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/relatorios', [RelatorioController::class, 'index'])->name('admin.relatorios.index')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/relatorios/pdf', [RelatorioController::class, 'exportarPdf'])->name('admin.relatorios.pdf')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/multas', [MultaController::class, 'index'])->name('admin.multas.index')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/multas/pdf', [MultaController::class, 'exportarPdf'])->name('admin.multas.pdf')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/multas/csv', [MultaController::class, 'exportarCsv'])->name('admin.multas.csv')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/pagamentos/{pagamento}/aprovar', [PagamentoController::class, 'aprovar'])->name('admin.pagamentos.aprovar')->middleware('tipo:gerente,bibliotecario');
    Route::post('/admin/pagamentos/{pagamento}/recusar', [PagamentoController::class, 'recusar'])->name('admin.pagamentos.recusar')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/auditoria', [AuditLogController::class, 'index'])->name('admin.auditoria.index')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/auditoria/pdf', [AuditLogController::class, 'exportarPdf'])->name('admin.auditoria.pdf')->middleware('tipo:gerente,bibliotecario');
    Route::get('/admin/auditoria/csv', [AuditLogController::class, 'exportarCsv'])->name('admin.auditoria.csv')->middleware('tipo:gerente,bibliotecario');

require __DIR__.'/auth.php';

// Notifications
use App\Http\Controllers\NotificationController;
Route::middleware(['auth:web,membro'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-read');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/read', [NotificationController::class, 'clearRead'])->name('notifications.clear-read');
});
