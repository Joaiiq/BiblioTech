<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Livros;
use App\Models\Comentario;
use App\Models\Autor;
use App\Models\Emprestimos;
use App\Models\Membros;
use App\Models\Reserva;
use App\Models\Categoria;
use App\Rules\RealisticDate;
use App\Models\AuditLog;

class LivroController extends Controller{
    public function dashboard()
    {
        Emprestimos::expirarRetiradasPendentes();

        $livros = Livros::with(['autor', 'categorias'])->latest()->get();
        $bestsellers = Livros::where('e_bestseller', true)->with(['autor', 'categorias'])->limit(12)->get();
        $livrosNacionais = Livros::where('e_nacional', true)->with(['autor', 'categorias'])->latest()->limit(10)->get();
        $livrosRecentes = Livros::latest()->with(['autor', 'categorias'])->limit(12)->get();
        $categorias = Categoria::nomesDisponiveis();
        $autores = Autor::withCount('livros')->latest()->get();
        $reservasPorLivro = Schema::hasTable('reservas')
            ? Reserva::ativas()
                ->select('livro_id', DB::raw('COUNT(*) as total'))
                ->groupBy('livro_id')
                ->pluck('total', 'livro_id')
            : collect();

        $emprestimosPorLivro = Emprestimos::select('livro_id', DB::raw('COUNT(*) as total'))
            ->groupBy('livro_id')
            ->pluck('total', 'livro_id');

        $livrosMaisReservados = $reservasPorLivro->isNotEmpty()
            ? Livros::with(['autor', 'categorias'])
                ->whereIn('id', $reservasPorLivro->keys())
                ->get()
                ->sortByDesc(fn (Livros $livro) => $reservasPorLivro[$livro->id] ?? 0)
                ->take(4)
                ->values()
            : collect();

        $vitrinePrincipal = $bestsellers->first() ?? $livrosRecentes->first() ?? $livros->first();
        $vitrineNovidades = $livrosRecentes
            ->reject(fn (Livros $livro) => $vitrinePrincipal && $livro->id === $vitrinePrincipal->id)
            ->take(3)
            ->values();

        $categoriasMaisAcessadas = Emprestimos::join('livros', 'emprestimos.livro_id', '=', 'livros.id')
            ->whereNotNull('livros.categoria')
            ->select('livros.categoria', DB::raw('COUNT(*) as total'))
            ->groupBy('livros.categoria')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $totalLivros = Livros::count();
        $totalMembros = Membros::count();
        $emprestimosAtivos = Emprestimos::whereIn('status', [
            Emprestimos::STATUS_APROVADO,
            Emprestimos::STATUS_RETIRADO,
            Emprestimos::STATUS_EM_USO,
        ])->count();
        $devolucoesVencidas = Emprestimos::whereNull('data_devolucao_real')
            ->where('data_devolucao_prevista', '<', today())
            ->count();

        $emprestimosDoMembro = collect();
        $reservasDoMembro = collect();
        $favoritosDoMembro = collect();
        $alertasMembro = collect();
        $metricasMembro = [
            'ativos' => 0,
            'vencendo_hoje' => 0,
            'atrasados' => 0,
            'reservas_ativas' => 0,
            'favoritos' => 0,
        ];

        if (! auth()->guard('web')->check() && auth()->guard('membro')->check()) {
            $membroId = auth()->guard('membro')->id();

            $emprestimosDoMembro = Emprestimos::with('livro.autor')
                ->where('membro_id', $membroId)
                ->whereIn('status', [
                    Emprestimos::STATUS_APROVADO,
                    Emprestimos::STATUS_RETIRADO,
                    Emprestimos::STATUS_EM_USO,
                    Emprestimos::STATUS_DEVOLUCAO_SOLICITADA,
                ])
                ->orderByRaw("FIELD(status, 'aprovado','devolucao_solicitada','retirado','em_uso')")
                ->orderBy('data_devolucao_prevista')
                ->take(4)
                ->get();

            $metricasMembro['ativos'] = Emprestimos::where('membro_id', $membroId)
                ->whereIn('status', Emprestimos::STATUS_ATIVOS)
                ->count();
            $metricasMembro['vencendo_hoje'] = Emprestimos::where('membro_id', $membroId)
                ->whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
                ->whereDate('data_devolucao_prevista', today())
                ->count();
            $metricasMembro['atrasados'] = Emprestimos::where('membro_id', $membroId)
                ->whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
                ->whereDate('data_devolucao_prevista', '<', today())
                ->count();

            if (Schema::hasTable('reservas')) {
                $reservasDoMembro = Reserva::with('livro.autor')
                    ->where('membro_id', $membroId)
                    ->where('status', Reserva::STATUS_ATIVA)
                    ->latest()
                    ->take(3)
                    ->get();

                $metricasMembro['reservas_ativas'] = Reserva::ativas()
                    ->where('membro_id', $membroId)
                    ->count();
            }

            $aprovadosRetirada = Emprestimos::where('membro_id', $membroId)
                ->where('status', Emprestimos::STATUS_APROVADO)
                ->count();
            $devolucoesSolicitadas = Emprestimos::where('membro_id', $membroId)
                ->where('status', Emprestimos::STATUS_DEVOLUCAO_SOLICITADA)
                ->count();
            $multasPendentes = Emprestimos::where('membro_id', $membroId)
                ->where('valor_multa', '>', 0)
                ->whereNull('multa_paga_em')
                ->count();

            if ($metricasMembro['atrasados'] > 0) {
                $alertasMembro->push([
                    'tipo' => 'danger',
                    'icone' => 'ph-warning-circle',
                    'titulo' => 'Existe empréstimo em atraso',
                    'texto' => 'Regularize a devolução para evitar novas multas e liberar próximas solicitações.',
                    'acao' => 'Ver situação',
                    'url' => route('membros.situacao'),
                ]);
            }

            if ($multasPendentes > 0) {
                $alertasMembro->push([
                    'tipo' => 'danger',
                    'icone' => 'ph-currency-circle-dollar',
                    'titulo' => 'Multa pendente',
                    'texto' => 'Procure a biblioteca para regularizar a pendência.',
                    'acao' => 'Detalhes',
                    'url' => route('membros.situacao'),
                ]);
            }

            if ($metricasMembro['vencendo_hoje'] > 0) {
                $alertasMembro->push([
                    'tipo' => 'warning',
                    'icone' => 'ph-calendar-check',
                    'titulo' => 'Prazo vencendo hoje',
                    'texto' => 'Confira seus empréstimos e solicite devolução se já terminou de usar o livro.',
                    'acao' => 'Meus empréstimos',
                    'url' => route('emprestimos.historico'),
                ]);
            }

            if ($aprovadosRetirada > 0) {
                $proximaRetirada = Emprestimos::where('membro_id', $membroId)
                    ->where('status', Emprestimos::STATUS_APROVADO)
                    ->whereNotNull('data_limite_retirada')
                    ->orderBy('data_limite_retirada')
                    ->first();

                $alertasMembro->push([
                    'tipo' => 'info',
                    'icone' => 'ph-bag',
                    'titulo' => 'Livro aguardando retirada',
                    'texto' => $proximaRetirada?->data_limite_retirada
                        ? 'Seu pedido foi aprovado. Retire o exemplar presencialmente até ' . $proximaRetirada->data_limite_retirada->format('d/m/Y') . '.'
                        : 'Seu pedido foi aprovado. Passe na biblioteca para retirar o exemplar.',
                    'acao' => 'Ver pedidos',
                    'url' => route('emprestimos.historico'),
                ]);
            }

            if ($devolucoesSolicitadas > 0) {
                $alertasMembro->push([
                    'tipo' => 'info',
                    'icone' => 'ph-arrow-u-up-left',
                    'titulo' => 'Devolução solicitada',
                    'texto' => 'Finalize a entrega no balcão para concluir o empréstimo.',
                    'acao' => 'Acompanhar',
                    'url' => route('emprestimos.historico'),
                ]);
            }

            if (Schema::hasTable('favoritos')) {
                $favoritosDoMembro = auth()->guard('membro')->user()
                    ->livrosFavoritos()
                    ->with('autor')
                    ->orderByPivot('created_at', 'desc')
                    ->take(4)
                    ->get();
                $metricasMembro['favoritos'] = auth()->guard('membro')->user()
                    ->livrosFavoritos()
                    ->count();
            }
        }

        $recomendados = $this->recomendarParaUsuario();

        return view('dashboard', compact(
            'livros',
            'bestsellers',
            'livrosNacionais',
            'livrosRecentes',
            'categorias',
            'autores',
            'categoriasMaisAcessadas',
            'reservasPorLivro',
            'emprestimosPorLivro',
            'livrosMaisReservados',
            'vitrinePrincipal',
            'vitrineNovidades',
            'totalLivros',
            'totalMembros',
            'emprestimosAtivos',
            'devolucoesVencidas',
            'emprestimosDoMembro',
            'reservasDoMembro',
            'favoritosDoMembro',
            'recomendados',
            'alertasMembro',
            'metricasMembro'
        ));
    }
    /**
     * Recomenda livros para o membro com base no historico e na lista Quero ler.
     */
    public function recomendarParaUsuario()
    {
        $membro = auth()->guard('web')->check() ? null : auth()->guard('membro')->user();

        if (! $membro) {
            return collect();
        }

        $membroId = $membro->id;
        $categoriasHistorico = Emprestimos::where('membro_id', $membroId)
            ->join('livros', 'emprestimos.livro_id', '=', 'livros.id')
            ->whereNotNull('livros.categoria')
            ->select('livros.categoria')
            ->groupBy('livros.categoria')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(4)
            ->pluck('livros.categoria');

        $jaPegouIds = Emprestimos::where('membro_id', $membroId)
            ->pluck('livro_id');

        $favoritoIds = collect();
        $categoriasFavoritas = collect();
        $autoresFavoritos = collect();

        if (Schema::hasTable('favoritos')) {
            $favoritos = $membro->livrosFavoritos()
                ->select('livros.id', 'livros.categoria', 'livros.autor_id')
                ->get();

            $favoritoIds = $favoritos->pluck('id');
            $categoriasFavoritas = $favoritos->pluck('categoria')->filter();
            $autoresFavoritos = $favoritos->pluck('autor_id')->filter();
        }

        $categoriasInteresse = $categoriasHistorico
            ->merge($categoriasFavoritas)
            ->filter()
            ->unique()
            ->values();

        $recomendados = Livros::query()
            ->with('autor')
            ->whereNotIn('id', $jaPegouIds->merge($favoritoIds)->unique())
            ->when($categoriasInteresse->isNotEmpty() || $autoresFavoritos->isNotEmpty(), function ($query) use ($categoriasInteresse, $autoresFavoritos) {
                $query->where(function ($query) use ($categoriasInteresse, $autoresFavoritos) {
                    if ($categoriasInteresse->isNotEmpty()) {
                        $query->whereIn('categoria', $categoriasInteresse);
                    }

                    if ($autoresFavoritos->isNotEmpty()) {
                        $method = $categoriasInteresse->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                        $query->{$method}('autor_id', $autoresFavoritos);
                    }
                });
            })
            ->orderByDesc('e_bestseller')
            ->latest()
            ->take(6)
            ->get();

        if ($recomendados->isNotEmpty()) {
            return $recomendados;
        }

        return Livros::with('autor')
            ->whereNotIn('id', $jaPegouIds->merge($favoritoIds)->unique())
            ->orderByDesc('e_bestseller')
            ->latest()
            ->take(6)
            ->get();
    }

    public function create()
    {
        $autores = Autor::all();
        $categorias = Categoria::orderBy('nome')->get();
        return view('admin.livros.create', compact('autores', 'categorias'));
    }

    public function store(Request $request)
    {
        // 1. Validação Completa (Antigos + Novos)
        $request->validate([
            'titulo'          => 'required|string|max:255',
            'autor_id'        => 'required|exists:autores,id',
            'isbn'            => [
                'required',
                'string',
                'unique:livros,isbn',
                'regex:/^[0-9]{3}-[0-9]{2}-[0-9]{3}-[0-9]{4}-[0-9]{1}$/'
            ],
            'capa'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categorias'      => 'required|array|min:1',
            'categorias.*'    => 'integer|exists:categorias,id',
            'quantidade'      => 'required|integer|min:0',  // NOVO: min:0 impede quantidade negativa!
            'estante'         => 'nullable|string|max:50',
            'localizacao'     => 'nullable|string|max:100',
            'data_publicacao' => ['required', 'date_format:Y-m-d', new RealisticDate('book_publication')],           // NOVO
            'sinopse'         => 'nullable|string|max:3000',
            'editora'         => 'nullable|string|max:255', // NOVO
            'paginas'         => 'nullable|integer|min:1', // NOVO
            'preview'         => 'nullable|string', // NOVO
            'preview_pdf'     => 'nullable|file|mimes:pdf|max:10240',
            'e_nacional'      => 'nullable|boolean',
        ], [
            'isbn.regex'  => 'O ISBN deve ter exatamente 13 números no formato 000-00-000-0000-0.',
            'isbn.unique' => 'Este ISBN já está cadastrado no sistema.',
            'preview_pdf.mimes' => 'A prévia das páginas precisa ser um arquivo PDF.',
            'preview_pdf.max' => 'A prévia em PDF deve ter no máximo 10MB.',
        ]);

        // 2. Prepara TODOS os dados para salvar
        $dadosLivro = [
            'titulo'          => $request->titulo,
            'autor_id'        => $request->autor_id,
            'isbn'            => $request->isbn,
            'e_bestseller'    => $request->has('e_bestseller'),
            'e_nacional'      => $request->has('e_nacional'),
            'categoria'       => Categoria::whereKey($request->categorias[0])->value('nome'),
            'quantidade'      => $request->quantidade,      // NOVO
            'estante'         => $request->estante,
            'localizacao'     => $request->localizacao,
            'data_publicacao' => $request->data_publicacao, // NOVO
            'sinopse'         => $request->sinopse,         // NOVO
            'editora'         => $request->editora,         // NOVO
            'paginas'         => $request->paginas,         // NOVO
            'preview'         => $request->preview,         // NOVO
        ];

      
        if ($request->hasFile('capa') && $request->file('capa')->isValid()) {
            $dadosLivro['capa'] = $request->file('capa')->store('capas', 'public');
        }

        if ($request->hasFile('preview_pdf') && $request->file('preview_pdf')->isValid()) {
            $dadosLivro['preview_pdf'] = $request->file('preview_pdf')->store('previews', 'public');
        }

       
        $livro = Livros::create($dadosLivro);
        $livro->categorias()->sync($request->categorias);
        AuditLog::record('livro_criado', "Cadastrou o livro {$livro->titulo}.", $livro, [
            'isbn' => $livro->isbn,
            'quantidade' => $livro->quantidade,
        ]);

        return redirect()->back()->with('sucesso', 'Livro cadastrado com sucesso!');
    }

    public function destroy(Request $request, $id)
    {
        $livro = Livros::findOrFail($id);
        $titulo = $livro->titulo;
        $livro->delete(); 
        AuditLog::record('livro_excluido', "Removeu o livro {$titulo}.", null, [
            'livro_id' => $id,
            'titulo' => $titulo,
        ]);
        $fallback = route('livros.index', ['acervo' => 1]);
        $previous = url()->previous();

        if ($previous && $previous !== route('livros.show', $id)) {
            return redirect()->to($previous)->with('sucesso', 'Livro removido com sucesso!');
        }

        return redirect($fallback)->with('sucesso', 'Livro removido com sucesso!');
    }

    public function edit($id)
    {
        $livro = Livros::with('categorias')->findOrFail($id);
        $autores = Autor::all();
        $categorias = Categoria::orderBy('nome')->get();
        return view('admin.livros.edit', compact('livro', 'autores', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        $livro = Livros::findOrFail($id);

        // 1. Validação Completa (Antigos + Novos na Edição)
        $request->validate([
            'titulo'          => 'required|string|max:255',
            'autor_id'        => 'required|exists:autores,id',
            'isbn'            => [
                'required',
                'string',
                'unique:livros,isbn,' . $livro->id, // Ignora o próprio livro
                'regex:/^[0-9]{3}-[0-9]{2}-[0-9]{3}-[0-9]{4}-[0-9]{1}$/'
            ],
            'capa'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categorias'      => 'required|array|min:1',
            'categorias.*'    => 'integer|exists:categorias,id',
            'quantidade'      => 'required|integer|min:0',  // NOVO
            'estante'         => 'nullable|string|max:50',
            'localizacao'     => 'nullable|string|max:100',
            'data_publicacao' => ['required', 'date_format:Y-m-d', new RealisticDate('book_publication')],           // NOVO
            'sinopse'         => 'nullable|string|max:3000',
            'editora'         => 'nullable|string|max:255', // NOVO
            'paginas'         => 'nullable|integer|min:1',  // NOVO
            'preview'         => 'nullable|string',         // NOVO
            'preview_pdf'     => 'nullable|file|mimes:pdf|max:10240',
            'e_nacional'      => 'nullable|boolean',
        ], [
            'isbn.regex'  => 'O ISBN deve ter exatamente 13 números no formato 000-00-000-0000-0.',
            'isbn.unique' => 'Este ISBN já pertence a outro livro.',
            'preview_pdf.mimes' => 'A prévia das páginas precisa ser um arquivo PDF.',
            'preview_pdf.max' => 'A prévia em PDF deve ter no máximo 10MB.',
        ]);

        // 2. Prepara os dados atualizados
        $dadosLivro = [
            'titulo'          => $request->titulo,
            'autor_id'        => $request->autor_id,
            'isbn'            => $request->isbn,
            'e_bestseller'    => $request->has('e_bestseller'),
            'e_nacional'      => $request->has('e_nacional'),
            'categoria'       => Categoria::whereKey($request->categorias[0])->value('nome'),
            'quantidade'      => $request->quantidade,      // NOVO
            'estante'         => $request->estante,
            'localizacao'     => $request->localizacao,
            'data_publicacao' => $request->data_publicacao, // NOVO
            'sinopse'         => $request->sinopse,         // NOVO
            'editora'         => $request->editora,         // NOVO
            'paginas'         => $request->paginas,         // NOVO
            'preview'         => $request->preview,         // NOVO
        ];

        // 3. Atualiza a Imagem (Apagando a antiga)
        if ($request->hasFile('capa') && $request->file('capa')->isValid()) {
            if ($livro->capa) {
                Storage::disk('public')->delete($livro->capa);
            }
            $dadosLivro['capa'] = $request->file('capa')->store('capas', 'public');
        }

        if ($request->hasFile('preview_pdf') && $request->file('preview_pdf')->isValid()) {
            if ($livro->preview_pdf) {
                Storage::disk('public')->delete($livro->preview_pdf);
            }
            $dadosLivro['preview_pdf'] = $request->file('preview_pdf')->store('previews', 'public');
        }

        // 4. Salva as alterações
        $livro->update($dadosLivro);
        $livro->categorias()->sync($request->categorias);
        AuditLog::record('livro_atualizado', "Atualizou o livro {$livro->titulo}.", $livro, [
            'isbn' => $livro->isbn,
            'quantidade' => $livro->quantidade,
        ]);

        return redirect()->back()->with('sucesso', 'Livro atualizado com sucesso!');
    }
    public function show($id)
    {
        $livro = Livros::with(['autor', 'categorias', 'comentarios.user', 'comentarios.membro'])->findOrFail($id);
        $comentarios = $livro->comentarios->sortByDesc('created_at');
        $mediaNota = $livro->comentarios->avg('nota');
        $totalComentarios = $livro->comentarios->count();
        $userId = auth()->guard('web')->id();
        $membroId = auth()->guard('web')->check() ? null : auth()->guard('membro')->id();
        $isMembroOperacional = auth()->guard('membro')->check() && !auth()->guard('web')->check();
        $comentarioExistente = $livro->comentarioDe($userId, $membroId);
        $prazoEmprestimoDias = Emprestimos::prazoDiasParaLivro($livro);
        $bloqueiosEmprestimo = [];
        $reservasAtivas = Reserva::ativas()
            ->where('livro_id', $livro->id)
            ->orderBy('created_at')
            ->get();
        $reservaDoMembro = null;
        $posicaoReserva = null;
        $isFavorito = false;
        $membrosParaEmprestimo = collect();

        // Apenas permitir comentário se o membro já tiver devolvido este livro
        $podeComentar = false;
        if ($isMembroOperacional) {
            $podeComentar = Emprestimos::where('livro_id', $livro->id)
                ->where('membro_id', $membroId)
                ->whereIn('status', [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO])
                ->exists();

            $emprestimoAtivoMesmoLivro = Emprestimos::where('membro_id', $membroId)
                ->where('livro_id', $livro->id)
                ->whereIn('status', Emprestimos::STATUS_ATIVOS)
                ->latest()
                ->first();

            if ($emprestimoAtivoMesmoLivro) {
                $bloqueiosEmprestimo[] = match ($emprestimoAtivoMesmoLivro->status) {
                    Emprestimos::STATUS_SOLICITADO => 'Sua solicitação para este livro está em análise.',
                    Emprestimos::STATUS_APROVADO => 'Este livro já foi aprovado para retirada.',
                    default => 'Você já tem um empréstimo ativo deste livro.',
                };
            }

            $totalAtivos = Emprestimos::where('membro_id', $membroId)
                ->whereIn('status', Emprestimos::STATUS_ATIVOS)
                ->count();

            if ($totalAtivos >= 3) {
                $bloqueiosEmprestimo[] = 'Você atingiu o limite de 3 empréstimos ativos.';
            }

            $temVencido = Emprestimos::where('membro_id', $membroId)
                ->whereIn('status', Emprestimos::STATUS_EM_ANDAMENTO)
                ->where('data_devolucao_prevista', '<', today())
                ->exists();

            if ($temVencido) {
                $bloqueiosEmprestimo[] = 'Você possui empréstimos vencidos.';
            }

            if (Emprestimos::possuiMultaPendente($membroId)) {
                $bloqueiosEmprestimo[] = 'Você possui multas pendentes.';
            }

            $reservaDoMembro = $reservasAtivas->firstWhere('membro_id', $membroId);
            if ($reservaDoMembro) {
                $posicaoReserva = $reservasAtivas->search(fn ($reserva) => $reserva->id === $reservaDoMembro->id) + 1;
            }

            if (Schema::hasTable('favoritos')) {
                $isFavorito = auth()->guard('membro')->user()
                    ->livrosFavoritos()
                    ->where('livros.id', $livro->id)
                    ->exists();
            }
        }

        if (auth()->guard('web')->check()) {
            $membrosParaEmprestimo = Membros::query()
                ->orderBy('nome')
                ->get(['id', 'nome', 'numero_carteirinha', 'email']);
        }

        $podeSolicitarEmprestimo = $isMembroOperacional
            && $livro->quantidade > 0
            && empty($bloqueiosEmprestimo);

        $podeReservar = $isMembroOperacional
            && $livro->quantidade <= 0
            && empty($bloqueiosEmprestimo)
            && !$reservaDoMembro;

        return view('livros.show', compact(
            'livro',
            'comentarios',
            'mediaNota',
            'totalComentarios',
            'comentarioExistente',
            'podeComentar',
            'prazoEmprestimoDias',
            'bloqueiosEmprestimo',
            'podeSolicitarEmprestimo',
            'podeReservar',
            'reservasAtivas',
            'reservaDoMembro',
            'posicaoReserva',
            'isFavorito',
            'isMembroOperacional',
            'membrosParaEmprestimo'
        ));
    }

    public function storeComentario(Request $request, $id)
    {
        $request->validate([
            'nota' => 'required|integer|min:1|max:5',
            'comentario' => 'required|string|min:5|max:1000',
        ]);

        $livro = Livros::findOrFail($id);
        $userId = auth()->guard('web')->id();
        $membroId = auth()->guard('web')->check() ? null : auth()->guard('membro')->id();
        $comentarioExistente = $livro->comentarioDe($userId, $membroId);

        if ($comentarioExistente) {
            return redirect()->back()->withErrors(['comentario' => 'Voce ja comentou este livro. Edite seu comentario existente.']);
        }

        // Verifica se o usuário/membro já devolveu este livro antes de permitir comentar
        $podeComentar = false;
        if (! auth()->guard('web')->check() && auth()->guard('membro')->check()) {
            $podeComentar = Emprestimos::where('livro_id', $livro->id)
                ->where('membro_id', $membroId)
                ->whereIn('status', [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO])
                ->exists();
        } elseif (auth()->guard('web')->check()) {
            $podeComentar = Emprestimos::where('livro_id', $livro->id)
                ->where('user_id', $userId)
                ->whereIn('status', [Emprestimos::STATUS_DEVOLVIDO, Emprestimos::STATUS_ENCERRADO])
                ->exists();
        }

        if (! $podeComentar) {
            return redirect()->back()->withErrors(['comentario' => 'Só é possível comentar livros que você já devolveu.']);
        }
        $comentario = new Comentario();
        $comentario->livro_id = $livro->id;
        $comentario->nota = $request->nota;
        $comentario->comentario = $request->comentario;

        if (! auth()->guard('web')->check() && auth()->guard('membro')->check()) {
            $comentario->membro_id = auth()->guard('membro')->id();
        } elseif (auth()->guard('web')->check()) {
            $comentario->user_id = auth()->guard('web')->id();
        } else {
            abort(403, 'Você precisa estar logado para comentar.');
        }

        $comentario->save();

        return redirect()->back()->with('success', 'Comentario enviado com sucesso.');
    }

    public function updateComentario(Request $request, $livroId, $comentarioId)
    {
        $request->validate([
            'nota' => 'required|integer|min:1|max:5',
            'comentario' => 'required|string|min:5|max:1000',
        ]);

        $comentario = Comentario::where('livro_id', $livroId)->findOrFail($comentarioId);
        $userId = auth()->guard('web')->id();
        $membroId = auth()->guard('web')->check() ? null : auth()->guard('membro')->id();

        $isOwner = ($membroId && $comentario->membro_id === $membroId)
            || ($userId && $comentario->user_id === $userId);

        if (!$isOwner) {
            abort(403, 'Você só pode editar comentários criados por você.');
        }

        $comentario->nota = $request->nota;
        $comentario->comentario = $request->comentario;
        $comentario->save();

        return redirect()->back()->with('success', 'Comentario atualizado com sucesso.');
    }

    public function destroyComentario($livroId, $comentarioId)
    {
        $comentario = Comentario::where('livro_id', $livroId)->findOrFail($comentarioId);
        $userId = auth()->guard('web')->id();
        $membroId = auth()->guard('web')->check() ? null : auth()->guard('membro')->id();

        $isOwner = ($membroId && $comentario->membro_id === $membroId)
            || ($userId && $comentario->user_id === $userId);

        if (!$isOwner) {
            abort(403, 'Você só pode remover comentários criados por você.');
        }

        $comentario->delete();

        return redirect()->back()->with('success', 'Comentario removido com sucesso.');
    }
}
