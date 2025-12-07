// Arquivo: Assets/Js/Admin.js

document.addEventListener('DOMContentLoaded', () => {
    initAdminNavigation();
    loadAdminStats(); // Carrega o dashboard inicial
});

// 1. Navegação da Sidebar (Troca de Telas)
function initAdminNavigation() {
    const links = document.querySelectorAll('.sidebar-item');
    const sections = document.querySelectorAll('.admin-section');

    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('data-target');

            if (targetId) {
                e.preventDefault();

                // 1. ATUALIZA O MENU LATERAL (Visual)
                links.forEach(l => l.classList.remove('active', 'border-l-4', 'border-red-500', 'bg-gradient-to-r'));
                link.classList.add('active', 'border-l-4', 'border-red-500');

                // 2. ESCONDE TODAS AS SEÇÕES
                sections.forEach(sec => sec.classList.add('hidden'));

                // 3. MOSTRA A SEÇÃO CERTA
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    targetSection.classList.remove('hidden');

                    // --- CARREGAMENTO SOB DEMANDA (SEU CÓDIGO) ---
                    if (targetId === 'section-alunos') {
                        carregarAlunos();
                    }
                    if (targetId === 'section-financeiro') {
                        carregarFinanceiro();
                    }
                    if (targetId === 'section-treinos') {
                        // Foca no input quando entra na aba
                        setTimeout(() => document.getElementById('input-busca-treino').focus(), 100);
                    }
                    // ---------------------------------------------

                    // Efeito de fade simples
                    targetSection.style.opacity = '0';
                    setTimeout(() => targetSection.style.opacity = '1', 50);
                }
            }
        });
    });
}

// 2. Carrega Dados do Dashboard (Visão Geral)
async function loadAdminStats() {
    try {
        const response = await fetch('../api/get_admin_stats.php');
        const data = await response.json();

        if (data.success) {
            // Atualiza Cards Superiores
            updateElement('total-alunos-display', data.total_alunos);
            updateElement('treinos-hoje-display', data.treinos_hoje);
            updateElement('faturamento-display', `R$ ${data.faturamento}`);

            // Renderiza Lista de Cadastros Recentes
            renderRecentUsers(data.recentes);
        } else {
            console.error('Erro na API:', data.error);
        }

    } catch (error) {
        console.error('Erro ao conectar na API Admin:', error);
    }
}

// 3. Carrega a Tabela de Alunos (Aba Alunos)
async function carregarAlunos() {
    const tbody = document.getElementById('tabela-alunos-body');
    const contador = document.getElementById('contador-alunos');

    // Se não achar a tabela no HTML, para aqui
    if (!tbody) return;

    try {
        const response = await fetch('../api/get_users.php');
        const data = await response.json();

        if (data.success) {
            tbody.innerHTML = ''; // Limpa a tabela

            if (data.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="p-8 text-center text-gray-500">Nenhum aluno encontrado.</td></tr>`;
                if (contador) contador.innerText = 'Mostrando 0 alunos';
                return;
            }

            // Loop para criar as linhas
            data.data.forEach(aluno => {

                // --- 1. LÓGICA DO PLANO (Já existia) ---
                let planoClass = 'bg-gray-700 text-gray-300 border border-gray-600';
                const plano = (aluno.plano || '').toLowerCase();

                if (plano.includes('vip')) planoClass = 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20';
                else if (plano.includes('premium')) planoClass = 'bg-purple-500/10 text-purple-400 border border-purple-500/20';
                else if (plano.includes('standard')) planoClass = 'bg-blue-500/10 text-blue-400 border border-blue-500/20';

                // --- 2. NOVA LÓGICA DO STATUS (Encaixe AQUI) ---
                // Pega o status do banco ou usa 'ativo' se vier vazio
                const statusReal = (aluno.status || 'ativo').toLowerCase();

                // Configuração Padrão (Verde/Ativo)
                let statusClass = 'text-green-500 bg-green-500/10 border-green-500/20';
                let statusDot = 'bg-green-500';
                let statusIcon = ''; // Se quiser mudar a animação depois

                // Configuração para Inativo/Bloqueado
                if (statusReal === 'inativo' || statusReal === 'bloqueado') {
                    statusClass = 'text-red-500 bg-red-500/10 border-red-500/20';
                    statusDot = 'bg-red-500';
                }
                // Configuração para Pendente
                else if (statusReal === 'pendente') {
                    statusClass = 'text-yellow-500 bg-yellow-500/10 border-yellow-500/20';
                    statusDot = 'bg-yellow-500';
                }

                // Formata data
                const dataCriacao = new Date(aluno.criado_em).toLocaleDateString('pt-BR');

                // --- 3. MONTAGEM DO HTML (Atualizado com as variáveis novas) ---
                const html = `
                    <tr class="hover:bg-gray-800/30 transition-colors border-b border-gray-700/50 group">
                        <td class="p-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-600 flex items-center justify-center font-bold text-white shadow-sm border border-gray-600">
                                    ${aluno.nome.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <p class="font-bold text-white text-sm group-hover:text-red-400 transition-colors">${aluno.nome}</p>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Cadastrado em: ${dataCriacao}</p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex flex-col space-y-1">
                                <span class="text-gray-300 text-sm flex items-center"><i class="fas fa-envelope mr-2 text-gray-600 w-4 text-center"></i>${aluno.email}</span>
                                <span class="text-gray-400 text-xs flex items-center"><i class="fas fa-phone mr-2 text-gray-600 w-4 text-center"></i>${aluno.telefone || '---'}</span>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider ${planoClass}">
                                ${aluno.plano || 'Sem Plano'}
                            </span>
                        </td>
                        
                        <td class="p-4">
                            <span class="flex items-center ${statusClass} text-xs font-bold uppercase tracking-wide px-2 py-1 rounded w-fit border">
                                <span class="w-1.5 h-1.5 rounded-full ${statusDot} mr-2 animate-pulse"></span> ${statusReal}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex justify-center space-x-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                <button onclick="editarAluno(${aluno.id})" class="text-blue-400 hover:text-white p-2 rounded-lg hover:bg-blue-500/20 transition-all" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deletarAluno(${aluno.id})" class="text-red-400 hover:text-white p-2 rounded-lg hover:bg-red-500/20 transition-all" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += html;
            });

            if (contador) contador.innerText = `Mostrando ${data.data.length} alunos`;

        } else {
            console.error('Erro:', data.error);
            tbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-red-400">Erro ao carregar dados: ${data.error}</td></tr>`;
        }
    } catch (error) {
        console.error('Erro na requisição:', error);
        tbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-red-400">Erro de conexão com o servidor.</td></tr>`;
    }
}

// --- Funções Auxiliares ---

// Função para evitar erro se o elemento não existir
function updateElement(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
}

// Renderiza a lista pequena do Dashboard
function renderRecentUsers(users) {
    const container = document.getElementById('lista-recentes');
    if (!container) return;

    container.innerHTML = ''; // Limpa

    users.forEach(user => {
        const dataObj = new Date(user.criado_em);
        const dataFormatada = dataObj.toLocaleDateString('pt-BR');

        const html = `
            <div class="flex items-center justify-between p-4 bg-gray-800/50 rounded-xl hover:bg-gray-800 transition-colors border border-gray-700/50 hover:border-gray-600 mb-3">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-gray-700 to-gray-600 flex items-center justify-center font-bold text-white shadow-md">
                        ${user.nome.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <p class="font-bold text-white text-sm">${user.nome}</p>
                        <p class="text-xs text-gray-400">${user.email}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="block px-3 py-1 rounded-full bg-green-500/10 text-green-500 text-[10px] font-bold border border-green-500/20 mb-1">
                        ${user.plano || 'Standard'}
                    </span>
                    <span class="text-[10px] text-gray-500">${dataFormatada}</span>
                </div>
            </div>
        `;
        container.innerHTML += html;
    });
}

// --- Funções de Ação ---

// 1. Abrir Modal e Preencher Dados
async function editarAluno(id) {
    const modal = document.getElementById('modal-editar');

    // Mostra loading ou abre o modal vazio primeiro (opcional)
    // Vamos buscar os dados
    try {
        const response = await fetch(`../api/get_user_details.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            const user = data.data;

            // Preenche os inputs
            document.getElementById('edit-id').value = user.id;
            document.getElementById('edit-nome').value = user.nome;
            document.getElementById('edit-email').value = user.email;
            document.getElementById('edit-telefone').value = user.telefone || '';
            document.getElementById('edit-plano').value = user.plano || 'Standard';
            document.getElementById('edit-status').value = (user.status || 'ativo').toLowerCase();

            // Abre o modal
            modal.classList.remove('hidden');
        } else {
            alert('Erro ao carregar dados: ' + data.error);
        }
    } catch (error) {
        console.error(error);
        alert('Erro de conexão ao buscar dados.');
    }
}

// 2. Fechar Modal
function fecharModal() {
    document.getElementById('modal-editar').classList.add('hidden');
}

// 3. Salvar Alterações (Submit do Form)
document.getElementById('form-editar-aluno').addEventListener('submit', async (e) => {
    e.preventDefault();

    const dados = {
        id: document.getElementById('edit-id').value,
        nome: document.getElementById('edit-nome').value,
        email: document.getElementById('edit-email').value,
        telefone: document.getElementById('edit-telefone').value,
        plano: document.getElementById('edit-plano').value,
        status: document.getElementById('edit-status').value
    };

    try {
        const response = await fetch('../api/admin_update_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });
        const result = await response.json();

        if (result.success) {
            // 1. Fecha o modal de edição primeiro
            fecharModal();

            // 2. Atualiza a tabela no fundo
            carregarAlunos();
            loadAdminStats();

            // 3. MOSTRA O NOVO MODAL BONITO AO INVÉS DO ALERT
            mostrarSucesso('Dados do aluno atualizados com sucesso!');

        } else {
            alert('Erro ao salvar: ' + result.error); // Para erro pode manter alert ou criar um modal vermelho
        }
    } catch (error) {
        console.error(error);
        alert('Erro ao conectar com o servidor.');
    }
});

// Fecha modal se clicar fora dele
document.getElementById('modal-editar').addEventListener('click', (e) => {
    if (e.target.id === 'modal-editar') fecharModal();
});

// 4. Deletar Aluno
// --- LÓGICA DE EXCLUSÃO DE ALUNOS (RESTAURADA) ---

// 1. Botão da Lixeira (Na Tabela)
function deletarAluno(id) {
    // Grava o ID no input hidden do HTML
    const inputId = document.getElementById('id-para-excluir');
    if(inputId) {
        inputId.value = id;
    } else {
        console.error("ERRO: Não achei o input 'id-para-excluir' no HTML");
        return;
    }
    
    // Abre o modal visualmente
    const modal = document.getElementById('modal-confirmar-exclusao');
    const content = document.getElementById('modal-exclusao-content');
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-90');
        content.classList.add('scale-100');
    }, 10);
}

// 2. Botão "Sim, Excluir" (No Modal)
async function confirmarExclusaoDefinitiva() {
    // Pega o ID que salvamos antes
    const id = document.getElementById('id-para-excluir').value;
    
    // Fecha o modal de pergunta
    fecharModalExclusao();

    try {
        const response = await fetch('../api/admin_delete_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const result = await response.json();

        if (result.success) {
            carregarAlunos(); // Atualiza tabela
            loadAdminStats(); // Atualiza números
            mostrarSucesso('Aluno excluído com sucesso!');
        } else {
            mostrarErro(result.error || 'Erro ao excluir aluno.');
        }
    } catch (error) {
        console.error(error);
        mostrarErro('Erro de conexão ao tentar excluir.');
    }
}

// 3. Botão Cancelar
function fecharModalExclusao() {
    const modal = document.getElementById('modal-confirmar-exclusao');
    const content = document.getElementById('modal-exclusao-content');
    
    modal.classList.add('opacity-0');
    content.classList.remove('scale-100');
    content.classList.add('scale-90');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Fecha se clicar fora (backdrop)
document.getElementById('modal-confirmar-exclusao').addEventListener('click', (e) => {
    if (e.target.id === 'modal-confirmar-exclusao') fecharModalExclusao();
});

// Modal de confirmação de edição
// --- FUNÇÕES DO MODAL DE SUCESSO ---

function mostrarSucesso(mensagem) {
    const modal = document.getElementById('modal-sucesso');
    const content = document.getElementById('modal-sucesso-content');

    document.getElementById('msg-sucesso-texto').innerText = mensagem;
    modal.classList.remove('hidden');

    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-90');
        content.classList.add('scale-100');
    }, 10);
}

function fecharModalSucesso() {
    const modal = document.getElementById('modal-sucesso');
    const content = document.getElementById('modal-sucesso-content');

    // Animação de saída
    modal.classList.add('opacity-0');
    content.classList.remove('scale-100');
    content.classList.add('scale-90');

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300); // Espera a animação terminar para esconder
}

// NOVA FUNÇÃO: MOSTRAR ERRO
function mostrarErro(mensagem) {
    const modal = document.getElementById('modal-erro');
    const content = document.getElementById('modal-erro-content');

    document.getElementById('msg-erro-texto').innerText = mensagem;
    modal.classList.remove('hidden');

    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-90');
        content.classList.add('scale-100');
    }, 10);
}

function fecharModalErro() {
    document.getElementById('modal-erro').classList.add('hidden');
}

// --- LÓGICA DE NOVO ALUNO ---

function abrirModalNovo() {
    // Limpa os campos antes de abrir
    document.getElementById('form-novo-aluno').reset();
    document.getElementById('modal-novo').classList.remove('hidden');
}

document.getElementById('form-novo-aluno').addEventListener('submit', async (e) => {
    e.preventDefault();

    const dados = {
        nome: document.getElementById('novo-nome').value,
        email: document.getElementById('novo-email').value,
        password: document.getElementById('novo-senha').value, // <--- O PULO DO GATO
        telefone: document.getElementById('novo-telefone').value,
        plano: document.getElementById('novo-plano').value,
        status: document.getElementById('novo-status').value
    };

    try {
        const response = await fetch('../api/admin_create_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });
        const result = await response.json();

        if (result.success) {
            document.getElementById('modal-novo').classList.add('hidden');
            carregarAlunos();
            loadAdminStats();
            mostrarSucesso('Novo aluno cadastrado com sucesso!');
        } else {
            // Substituímos o alert pelo Modal de Erro
            mostrarErro(result.error || 'Erro desconhecido ao cadastrar.');
        }
    } catch (error) {
        console.error(error);
        mostrarErro('Erro de conexão com o servidor.');
    }
});

// --- LÓGICA DE EDIÇÃO (ATUALIZADA SEM ALERTS) ---

async function editarAluno(id) {
    try {
        const response = await fetch(`../api/get_user_details.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            const user = data.data;
            document.getElementById('edit-id').value = user.id;
            document.getElementById('edit-nome').value = user.nome;
            document.getElementById('edit-email').value = user.email;
            document.getElementById('edit-telefone').value = user.telefone || '';
            document.getElementById('edit-plano').value = user.plano || 'Standard';
            document.getElementById('edit-status').value = (user.status || 'ativo').toLowerCase();

            document.getElementById('modal-editar').classList.remove('hidden');
        } else {
            mostrarErro(data.error);
        }
    } catch (error) {
        mostrarErro('Erro ao buscar dados do aluno.');
    }
}

document.getElementById('form-editar-aluno').addEventListener('submit', async (e) => {
    e.preventDefault();

    const dados = {
        id: document.getElementById('edit-id').value,
        nome: document.getElementById('edit-nome').value,
        email: document.getElementById('edit-email').value,
        telefone: document.getElementById('edit-telefone').value,
        plano: document.getElementById('edit-plano').value,
        status: document.getElementById('edit-status').value
    };

    try {
        const response = await fetch('../api/admin_update_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });
        const result = await response.json();

        if (result.success) {
            document.getElementById('modal-editar').classList.add('hidden');
            carregarAlunos();
            loadAdminStats();
            mostrarSucesso('Aluno atualizado com sucesso!');
        } else {
            mostrarErro(result.error);
        }
    } catch (error) {
        mostrarErro('Erro ao salvar alterações.');
    }
});

function fecharModal() {
    document.getElementById('modal-editar').classList.add('hidden');
}

// --- LÓGICA DA PESQUISA (Busca Instantânea) ---
const campoBusca = document.getElementById('busca-aluno');

if (campoBusca) {
    campoBusca.addEventListener('keyup', function () {
        const termo = this.value.toLowerCase();
        const linhas = document.querySelectorAll('#tabela-alunos-body tr');

        linhas.forEach(linha => {
            const textoLinha = linha.innerText.toLowerCase();
            if (textoLinha.includes(termo)) {
                linha.style.display = ''; // Mostra
            } else {
                linha.style.display = 'none'; // Esconde
            }
        });
    });
}

// --- LÓGICA DO FINANCEIRO ---

async function carregarFinanceiro() {
    const tbody = document.getElementById('tabela-financeiro-body');

    // Mostra loading no card total enquanto carrega
    updateElement('fin-total-geral', 'Carregando...');

    try {
        const response = await fetch('../api/get_financeiro.php');
        const data = await response.json();

        if (data.success) {
            // 1. Preenche os Cards (Nota Fiscal)
            const resumo = data.resumo;

            // Formatador de Moeda
            const formatarMoeda = (valor) => valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            updateElement('fin-qtd-standard', `${resumo.standard.qtd} Alunos`);
            updateElement('fin-total-standard', formatarMoeda(resumo.standard.total));

            updateElement('fin-qtd-premium', `${resumo.premium.qtd} Alunos`);
            updateElement('fin-total-premium', formatarMoeda(resumo.premium.total));

            updateElement('fin-qtd-vip', `${resumo.vip.qtd} Alunos`);
            updateElement('fin-total-vip', formatarMoeda(resumo.vip.total));

            updateElement('fin-total-geral', formatarMoeda(resumo.total_geral));

            // 2. Preenche a Tabela
            tbody.innerHTML = '';

            if (data.lista.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="p-6 text-center text-gray-500">Nenhum registro financeiro.</td></tr>`;
                return;
            }

            data.lista.forEach(aluno => {
                const status = (aluno.status || 'ativo').toLowerCase();
                let statusHtml = '';
                let btnAcao = '';

                // Lógica de Status e Botão
                if (status === 'ativo') {
                    statusHtml = `<span class="px-2 py-1 rounded bg-green-500/10 text-green-500 border border-green-500/20 text-xs font-bold">EM DIA</span>`;
                    btnAcao = `<span class="text-gray-500 text-xs italic">Nada a fazer</span>`;
                } else if (status === 'pendente') {
                    statusHtml = `<span class="px-2 py-1 rounded bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 text-xs font-bold">PENDENTE</span>`;
                    // Botão para cobrar
                    btnAcao = `
                        <button onclick="enviarCobranca(${aluno.id}, '${aluno.nome}', 'aviso')" 
                                class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs font-bold transition-all shadow shadow-yellow-500/20">
                            <i class="fas fa-bell mr-1"></i> Lembrar
                        </button>`;
                } else { // Inativo/Bloqueado/Atrasado
                    statusHtml = `<span class="px-2 py-1 rounded bg-red-500/10 text-red-500 border border-red-500/20 text-xs font-bold">ATRASADO</span>`;
                    // Botão mais agressivo
                    btnAcao = `
                        <button onclick="enviarCobranca(${aluno.id}, '${aluno.nome}', 'cobranca')" 
                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-bold transition-all shadow shadow-red-500/20">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Cobrar
                        </button>`;
                }

                const html = `
                    <tr class="hover:bg-gray-800/30 transition-colors border-b border-gray-700/50">
                        <td class="p-4 font-medium text-white">${aluno.nome}</td>
                        <td class="p-4 text-gray-400 text-sm uppercase">${aluno.plano || 'Standard'}</td>
                        <td class="p-4">${statusHtml}</td>
                        <td class="p-4 text-right">${btnAcao}</td>
                    </tr>
                `;
                tbody.innerHTML += html;
            });

        } else {
            console.error(data.error);
        }
    } catch (error) {
        console.error('Erro financeiro:', error);
    }
}

async function enviarCobranca(id, nome, tipo) {
    // Define a mensagem baseada no tipo
    let mensagem = '';
    let tituloSucesso = '';

    if (tipo === 'aviso') {
        mensagem = `Olá ${nome}, notamos que sua mensalidade está pendente. Evite o bloqueio do seu acesso!`;
        tituloSucesso = 'Lembrete enviado!';
    } else {
        mensagem = `Olá ${nome}, sua conta consta como atrasada e logo será bloqueada. Por favor, regularize sua situação na recepção.`;
        tituloSucesso = 'Cobrança enviada!';
    }

    try {
        const response = await fetch('../api/send_notification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                usuario_id: id,
                mensagem: mensagem,
                tipo: 'financeiro'
            })
        });
        const result = await response.json();

        if (result.success) {
            mostrarSucesso(`${tituloSucesso} Notificação registrada para ${nome}.`);
        } else {
            mostrarErro('Erro ao enviar notificação: ' + result.error);
        }
    } catch (error) {
        mostrarErro('Erro de conexão ao enviar cobrança.');
    }
}

// --- LÓGICA DE GERENCIAR TREINOS ---

// 1. Busca Dinâmica de Alunos
const inputBuscaTreino = document.getElementById('input-busca-treino');
const dropdownAlunos = document.getElementById('dropdown-alunos');

if (inputBuscaTreino) {
    inputBuscaTreino.addEventListener('keyup', async (e) => {
        const termo = e.target.value;
        
        if (termo.length < 2) {
            dropdownAlunos.classList.add('hidden');
            return;
        }

        try {
            const response = await fetch(`../api/search_students_lite.php?termo=${termo}`);
            const alunos = await response.json();

            dropdownAlunos.innerHTML = '';
            
            if (alunos.length > 0) {
                dropdownAlunos.classList.remove('hidden');
                alunos.forEach(aluno => {
                    const item = document.createElement('div');
                    item.className = 'p-4 hover:bg-gray-700 cursor-pointer border-b border-gray-700 last:border-0 flex justify-between items-center';
                    item.innerHTML = `
                        <div>
                            <p class="font-bold text-white">${aluno.nome}</p>
                            <p class="text-xs text-gray-400">${aluno.email}</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-500"></i>
                    `;
                    // Ao clicar, seleciona o aluno
                    item.onclick = () => selecionarAlunoTreino(aluno.id);
                    dropdownAlunos.appendChild(item);
                });
            } else {
                dropdownAlunos.classList.add('hidden');
            }
        } catch (error) {
            console.error('Erro na busca:', error);
        }
    });

    // Fecha dropdown se clicar fora
    document.addEventListener('click', (e) => {
        if (!inputBuscaTreino.contains(e.target) && !dropdownAlunos.contains(e.target)) {
            dropdownAlunos.classList.add('hidden');
        }
    });
}

// 2. Selecionar Aluno e Carregar Treinos
async function selecionarAlunoTreino(id) {
    // Esconde a busca e mostra a área do aluno
    document.getElementById('treino-busca-container').classList.add('hidden');
    document.getElementById('treino-area-aluno').classList.remove('hidden');
    document.getElementById('dropdown-alunos').classList.add('hidden');
    inputBuscaTreino.value = ''; // Limpa input

    const grid = document.getElementById('grid-treinos');
    grid.innerHTML = '<p class="text-gray-500">Carregando treinos...</p>';

    try {
        const response = await fetch(`../api/get_student_workouts.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            // Preenche Header
            document.getElementById('treino-nome-aluno').innerText = data.aluno.nome;
            document.getElementById('treino-email-aluno').innerText = data.aluno.email;
            document.getElementById('treino-id-aluno-selecionado').value = id;

            // Renderiza Treinos
            grid.innerHTML = '';
            
            if (data.treinos.length === 0) {
                grid.innerHTML = `<div class="col-span-3 text-center py-10 text-gray-500 border border-gray-700 border-dashed rounded-xl">Nenhum treino cadastrado para este aluno.</div>`;
                return;
            }

            data.treinos.forEach(treino => {
                // Tratamento para quebras de linha na descrição para exibir bonito no HTML
                const descFormatada = treino.descricao.replace(/\n/g, '<br>');
                
                const card = document.createElement('div');
                card.className = 'card-gradient p-5 rounded-xl border border-gray-700 hover:border-red-500/50 transition-all shadow-lg group relative';
                card.innerHTML = `
                    <div class="flex justify-between items-start mb-3">
                        <h4 class="text-xl font-bold text-white">${treino.nome_treino}</h4>
                        <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="prepararEdicaoTreino(${treino.id}, '${treino.nome_treino}', \`${treino.descricao}\`)" class="text-blue-400 hover:text-white p-1" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deletarTreino(${treino.id})" class="text-red-400 hover:text-white p-1" title="Excluir">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-gray-400 text-sm bg-gray-800/50 p-3 rounded-lg h-32 overflow-y-auto custom-scrollbar">
                        ${descFormatada}
                    </div>
                    <p class="text-xs text-gray-600 mt-3 text-right">Criado em: ${new Date(treino.criado_em).toLocaleDateString('pt-BR')}</p>
                `;
                grid.appendChild(card);
            });

        } else {
            mostrarErro(data.error);
        }
    } catch (error) {
        console.error(error);
        mostrarErro('Erro ao carregar dados do aluno.');
    }
}

// 3. Voltar para a Busca
function voltarBuscaTreino() {
    document.getElementById('treino-area-aluno').classList.add('hidden');
    document.getElementById('treino-busca-container').classList.remove('hidden');
    setTimeout(() => inputBuscaTreino.focus(), 100);
}

// 4. Modal: Abrir (Novo)
function abrirModalTreino() {
    document.getElementById('form-treino').reset();
    document.getElementById('treino-id').value = ''; // ID vazio = criar
    document.getElementById('titulo-modal-treino').innerText = 'Novo Treino';
    document.getElementById('modal-treino').classList.remove('hidden');
}

// 5. Modal: Abrir (Editar)
function prepararEdicaoTreino(id, nome, descricao) {
    document.getElementById('treino-id').value = id;
    document.getElementById('treino-nome').value = nome;
    document.getElementById('treino-descricao').value = descricao;
    document.getElementById('titulo-modal-treino').innerText = 'Editar Treino';
    document.getElementById('modal-treino').classList.remove('hidden');
}

// 6. Salvar Treino (Create/Update)
document.getElementById('form-treino').addEventListener('submit', async (e) => {
    e.preventDefault();

    const dados = {
        id: document.getElementById('treino-id').value, // Se tiver ID é update, se vazio é insert
        usuario_id: document.getElementById('treino-id-aluno-selecionado').value,
        nome_treino: document.getElementById('treino-nome').value,
        descricao: document.getElementById('treino-descricao').value
    };

    try {
        const response = await fetch('../api/admin_save_workout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });
        const result = await response.json();

        if (result.success) {
            document.getElementById('modal-treino').classList.add('hidden');
            mostrarSucesso(dados.id ? 'Treino atualizado!' : 'Treino criado com sucesso!');
            // Recarrega a lista do aluno atual
            selecionarAlunoTreino(dados.usuario_id);
            // Atualiza dashboard se precisar (opcional)
            loadAdminStats();
        } else {
            mostrarErro(result.error);
        }
    } catch (error) {
        mostrarErro('Erro de conexão.');
    }
});

// 7. Deletar Treino
function deletarTreino(id) {
    // Reutilizamos o modal de exclusão global que já criamos para alunos!
    // Precisamos apenas mudar o comportamento do botão "Sim" temporariamente ou criar um específico.
    // Para simplificar e manter "clean", vamos criar uma variavel global de contexto de exclusão ou um modal simples aqui mesmo.
    // Mas vamos usar o confirm do JS customizado que fizemos antes? Vamos adaptar ele.
    
    // Hack rápido: Alteramos o onclick do botão de confirmação do modal global
    document.getElementById('id-para-excluir').value = id;
    const btnConfirmar = document.querySelector('#modal-confirmar-exclusao button.bg-red-600');
    
    // Guarda a função original para restaurar depois (caso use em alunos)
    const oldOnclick = btnConfirmar.onclick; 
    
    // Define nova ação
    btnConfirmar.onclick = async function() {
        fecharModalExclusao();
        try {
            const response = await fetch('../api/admin_delete_workout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const result = await response.json();
            if (result.success) {
                mostrarSucesso('Treino removido.');
                const alunoId = document.getElementById('treino-id-aluno-selecionado').value;
                selecionarAlunoTreino(alunoId);
            } else {
                mostrarErro(result.error);
            }
        } catch (error) {
            mostrarErro('Erro ao excluir.');
        }
        // Restaura função original (para não quebrar a aba de alunos)
        btnConfirmar.onclick = confirmarExclusaoDefinitiva; 
    };

    // Abre o modal visualmente
    const modal = document.getElementById('modal-confirmar-exclusao');
    const content = document.getElementById('modal-exclusao-content');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-90');
        content.classList.add('scale-100');
    }, 10);
}

// --- LÓGICA DE EXCLUSÃO DE TREINOS (ISOLADA) ---

// 1. Chamado ao clicar na lixeira do CARD DE TREINO
function deletarTreino(id) {
    document.getElementById('id-treino-para-excluir').value = id;
    
    // Abre o modal específico de treino
    const modal = document.getElementById('modal-exclusao-treino');
    const content = document.getElementById('modal-exclusao-treino-content');
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-90');
        content.classList.add('scale-100');
    }, 10);
}

// 2. Chamado ao clicar em "Sim, Excluir" no modal de treino
async function confirmarExclusaoTreino() {
    const id = document.getElementById('id-treino-para-excluir').value;
    const modal = document.getElementById('modal-exclusao-treino');

    // Fecha o modal visualmente
    modal.classList.add('hidden');

    try {
        const response = await fetch('../api/admin_delete_workout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const result = await response.json();

        if (result.success) {
            mostrarSucesso('Ficha de treino removida.');
            
            // Recarrega a lista do aluno que está aberto na tela
            const alunoId = document.getElementById('treino-id-aluno-selecionado').value;
            selecionarAlunoTreino(alunoId);
        } else {
            mostrarErro(result.error || 'Erro ao excluir treino.');
        }
    } catch (error) {
        mostrarErro('Erro de conexão.');
    }
}