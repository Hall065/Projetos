// ==========================================
// 1. CONFIGURAÇÃO DE DADOS GLOBAIS
// ==========================================

let userData = {};
let userWorkouts = []; 
let userAppointments = []; // Guarda agendamentos na memória para o calendário

// Mocks iniciais para Horários (serão substituídos pela API)
let availableSchedules = [
  { id: 1, time: "06:00 - 07:00", available: true },
  { id: 2, time: "18:00 - 19:00", available: false },
  { id: 3, time: "19:00 - 20:00", available: true },
  { id: 4, time: "07:00 - 08:00", available: true },
  { id: 5, time: "20:00 - 21:00", available: true },
  { id: 6, time: "21:00 - 22:00", available: false }
];

// ==========================================
// 2. FUNÇÕES DE NAVEGAÇÃO E UI
// ==========================================

function navigateTo(sectionId) {
    // Atualiza menu lateral
    const menuItems = document.querySelectorAll('.sidebar-item');
    menuItems.forEach(item => {
        item.classList.remove('active', 'bg-gray-800', 'border-l-4', 'border-red-500');
        const clickAttr = item.getAttribute('onclick');
        if (clickAttr && clickAttr.includes(`'${sectionId}'`)) {
            item.classList.add('active'); 
        }
    });

    // Troca as seções
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(s => {
        s.style.display = 'none';
        s.classList.remove('active');
    });

    const target = document.getElementById(sectionId);
    if (target) {
        target.style.display = 'block';
        setTimeout(() => target.classList.add('active'), 10);
        
        // Atualiza Título da Página
        const titles = { 'inicio': 'Dashboard', 'horarios': 'Horários Disponíveis', 'agendamentos': 'Meus Agendamentos', 'treinos': 'Meus Treinos', 'agenda': 'Agenda de Treinos', 'perfil': 'Meu Perfil' };
        const pageTitle = document.getElementById('page-title');
        if(pageTitle) pageTitle.textContent = titles[sectionId] || 'Dashboard';
        
        // Carregamentos específicos por aba
        if (sectionId === 'treinos') loadWorkouts(); 
        
        if (sectionId === 'horarios') {
            // Usa a data do filtro se existir, senão usa hoje
            const dateInput = document.getElementById('schedule-date-filter');
            const dateToLoad = dateInput ? dateInput.value : new Date().toISOString().split('T')[0];
            loadSchedules(dateToLoad);
        }
    }
}

// CALENDÁRIO INTELIGENTE (ATUALIZADO COM ÍCONE E CLIQUE)
function renderCalendar(date, appointments = []) {
    const monthYear = document.getElementById("month-year");
    const calendarDays = document.getElementById("calendar-days");
    if(!monthYear || !calendarDays) return;

    const months = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
    monthYear.innerText = `${months[date.getMonth()]} ${date.getFullYear()}`;
    calendarDays.innerHTML = "";

    const tempDate = new Date(date.getFullYear(), date.getMonth(), 1);
    const firstDayIndex = tempDate.getDay();
    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();

    // Dias vazios antes do dia 1
    for (let x = firstDayIndex; x > 0; x--) {
        calendarDays.innerHTML += `<div class="p-4"></div>`;
    }

    const hoje = new Date();
    
    // Identifica dias com treino
    const diasComTreino = new Set();
    if(Array.isArray(appointments)) {
        appointments.forEach(apt => {
            if(apt.status === 'cancelado') return;
            const partes = apt.data_treino.split('-');
            const ano = parseInt(partes[0]);
            const mes = parseInt(partes[1]) - 1; 
            const dia = parseInt(partes[2]);

            if (mes === date.getMonth() && ano === date.getFullYear()) {
                diasComTreino.add(dia);
            }
        });
    }

    // Renderiza dias
    for (let i = 1; i <= lastDay; i++) {
        let classes = "bg-gray-800 hover:bg-gray-700 text-gray-300";
        let iconHtml = "";

        const isToday = i === hoje.getDate() && date.getMonth() === hoje.getMonth() && date.getFullYear() === hoje.getFullYear();
        if (isToday) {
            classes = "bg-gray-700 text-white border border-red-500";
        }

        if (diasComTreino.has(i)) {
            classes = "bg-gradient-to-br from-red-600 to-red-800 text-white shadow-lg font-bold";
            // AQUI ESTÁ A MUDANÇA: Ícone de Haltere em vez de bolinha
            iconHtml = `<div class="mt-1 animate-pulse"><i class="fas fa-dumbbell text-[10px] text-white opacity-80"></i></div>`; 
        }

        // Adiciona evento onclick para abrir detalhes do dia
        calendarDays.innerHTML += `
            <div onclick="openDayDetails(${date.getFullYear()}, ${date.getMonth()}, ${i})" 
                 class="p-2 h-20 flex flex-col items-center justify-start pt-2 rounded-xl text-center cursor-pointer transition-all hover:scale-105 ${classes}">
                <span class="text-lg">${i}</span>
                ${iconHtml}
            </div>
        `;
    }
}

// ==========================================
// 3. FUNÇÕES DE API (LEITURA DO BANCO)
// ==========================================

async function fetchUserData() {
    try {
        const response = await fetch('../api/get_user.php');
        if (!response.ok) throw new Error('Erro na rede');
        const data = await response.json();
        if (data.error) return null;
        return data;
    } catch (error) {
        console.error('Erro ao buscar dados do utilizador:', error);
        return null;
    }
}

async function loadDashboardStats() {
    try {
        const response = await fetch('../api/get_dashboard_stats.php');
        const stats = await response.json();
        
        if (stats && !stats.error) {
            // Cards Padrão
            if(document.getElementById('stat-monthly-workouts')) document.getElementById('stat-monthly-workouts').innerText = stats.monthlyWorkouts;
            if(document.getElementById('stat-next-workout')) document.getElementById('stat-next-workout').innerText = stats.nextWorkout;
            if(document.getElementById('stat-next-workout-type')) document.getElementById('stat-next-workout-type').innerText = stats.nextWorkoutType;
            if(document.getElementById('stat-calories')) document.getElementById('stat-calories').innerText = stats.calories;
            
            // --- ATUALIZA A SEQUÊNCIA (STREAK) NOS DOIS LUGARES ---
            
            // 1. No Card Principal (Topo da página)
            if(document.getElementById('stat-streak')) {
                document.getElementById('stat-streak').innerText = stats.streak;
            }
            
            // 2. Na Barra Lateral (Sidebar esquerda)
            if(document.getElementById('sidebar-streak')) {
                document.getElementById('sidebar-streak').innerText = `${stats.streak} dias`;
            }

            // Aba Perfil
            if(document.getElementById('stat-total-workouts')) document.getElementById('stat-total-workouts').innerText = stats.totalWorkouts;
        }
    } catch (e) {
        console.error("Erro ao carregar estatísticas", e);
    }
}

async function loadNotifications() {
    try {
        const response = await fetch('../api/get_notificacoes.php');
        const result = await response.json();
        
        const list = document.getElementById('notif-list');
        const badge = document.getElementById('notification-count');
        
        if (!result.data || result.data.length === 0) {
            list.innerHTML = '<p class="text-gray-500 text-center py-4 text-sm">Sem novas notificações.</p>';
            badge.style.display = 'none';
            return;
        }

        if (result.unread > 0) {
            badge.innerText = result.unread;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }

        list.innerHTML = result.data.map(notif => {
            let icon = 'fa-info-circle';
            let color = 'text-blue-500';
            
            if(notif.tipo === 'success') { icon = 'fa-check-circle'; color = 'text-green-500'; }
            if(notif.tipo === 'warning') { icon = 'fa-exclamation-triangle'; color = 'text-yellow-500'; }

            return `
            <div class="p-3 border-b border-gray-700 hover:bg-gray-700 transition-colors flex items-start justify-between space-x-3 group">
                <div class="flex items-start space-x-3">
                    <div class="mt-1 ${color}"><i class="fas ${icon}"></i></div>
                    <div>
                        <p class="text-sm text-gray-200 font-medium">${notif.mensagem}</p>
                        <p class="text-xs text-gray-500 mt-1">${new Date(notif.criado_em).toLocaleDateString('pt-BR')} às ${new Date(notif.criado_em).toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'})}</p>
                    </div>
                </div>
                <button onclick="deleteNotification(${notif.id})" class="text-gray-500 hover:text-green-500 transition-colors p-1" title="Marcar como lida (Apagar)">
                    <i class="fas fa-check"></i>
                </button>
            </div>`;
        }).join('');

    } catch (e) {
        console.error("Erro nas notificações", e);
    }
}

async function loadAppointments() {
    try {
        const response = await fetch('../api/get_agendamentos.php');
        const agendamentos = await response.json();
        
        if (agendamentos.error || !Array.isArray(agendamentos)) return;

        userAppointments = agendamentos;
        
        const listContainer = document.getElementById('appointments-list');
        if (listContainer) {
            listContainer.innerHTML = ''; 
            if (agendamentos.length === 0) {
                listContainer.innerHTML = '<p class="text-gray-400 text-center py-4">Nenhum agendamento encontrado.</p>';
            } else {
                agendamentos.forEach(item => {
                    const dateParts = item.data_treino.split('-');
                    const dia = dateParts[2];
                    const mes = dateParts[1]; 
                    const horaInicio = item.hora_inicio.substring(0, 5);
                    const horaFim = item.hora_fim.substring(0, 5);

                    let statusColor = 'text-green-500 border-green-500';
                    let statusBg = 'border-green-500';
                    if(item.status === 'cancelado') { statusColor = 'text-red-500 border-red-500'; statusBg = 'border-red-500'; }

                    const html = `
                        <div class="flex items-center space-x-4 p-4 bg-gray-800 rounded-xl border border-gray-700 hover:border-red-500 transition-all mb-3">
                            <div class="w-16 h-16 bg-gray-700 rounded-lg flex flex-col items-center justify-center border-l-4 ${statusBg}">
                                <span class="text-xl font-bold text-white">${dia}</span>
                                <span class="text-xs uppercase text-gray-400">Mês ${mes}</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-lg text-white">${item.tipo_treino}</h4>
                                <p class="text-gray-400 text-sm">
                                    <i class="fas fa-clock mr-1"></i> ${horaInicio} - ${horaFim}
                                </p>
                            </div>
                            <div class="text-right flex items-center space-x-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold border ${statusColor} bg-opacity-10 bg-gray-900">
                                    ${item.status.toUpperCase()}
                                </span>
                                ${item.status !== 'cancelado' ? `
                                <button onclick="cancelAppointment(${item.id})" class="text-red-400 hover:text-red-600 ml-2 hover:bg-gray-700 p-2 rounded-full transition-colors" title="Cancelar Treino">
                                    <i class="fas fa-times-circle text-xl"></i>
                                </button>` : ''}
                            </div>
                        </div>
                    `;
                    listContainer.innerHTML += html;
                });
            }
        }

        // Atualiza calendário
        const currentDate = new Date(); 
        renderCalendar(currentDate, userAppointments);

    } catch (error) {
        console.error('Erro ao carregar agendamentos:', error);
    }
}

async function loadWorkouts() {
    try {
        const response = await fetch('../api/get_treinos.php');
        const treinos = await response.json();
        userWorkouts = treinos || [];
        renderWorkoutPlans(userWorkouts); 
    } catch (e) {
        console.error("Erro ao buscar treinos", e);
    }
}

async function loadSchedules(dateStr) {
    try {
        const container = document.getElementById('available-schedules');
        if(container) container.innerHTML = '<p class="text-gray-400 text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Verificando agenda...</p>';

        const response = await fetch(`../api/get_horarios.php?date=${dateStr}`);
        const schedules = await response.json();
        renderSchedules(schedules, dateStr);
    } catch (e) { console.error(e); }
}

// ==========================================
// 4. MODALS E FILTROS
// ==========================================

function injectModals() {
    // 1. Modal Agendamento
    if (!document.getElementById('appointment-modal')) {
        const aptModalHTML = `
        <div id="appointment-modal" class="fixed inset-0 bg-black bg-opacity-70 hidden z-50 flex items-center justify-center backdrop-blur-sm">
            <div class="bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-8 border border-gray-700">
                <h3 class="text-2xl font-bold mb-6 text-white flex items-center"><i class="fas fa-calendar-plus text-red-500 mr-3"></i>Novo Agendamento</h3>
                <form onsubmit="handleAppointmentSubmit(event)">
                    <div class="space-y-4">
                        <div><label class="block text-gray-400 mb-2 text-sm">Data</label><input type="date" id="modal-date" required class="w-full bg-gray-900 text-white p-3 rounded-xl border border-gray-600 focus:border-red-500 outline-none"></div>
                        <div><label class="block text-gray-400 mb-2 text-sm">Horário</label><input type="time" id="modal-time" required class="w-full bg-gray-900 text-white p-3 rounded-xl border border-gray-600 focus:border-red-500 outline-none"></div>
                        <div>
                            <label class="block text-gray-400 mb-2 text-sm">Tipo de Treino</label>
                            <select id="modal-type" class="w-full bg-gray-900 text-white p-3 rounded-xl border border-gray-600 focus:border-red-500 outline-none"></select>
                            <p class="text-xs text-gray-500 mt-1">Crie novos tipos na aba "Meus Treinos"</p>
                        </div>
                    </div>
                    <div class="flex space-x-3 mt-8">
                        <button type="button" onclick="document.getElementById('appointment-modal').classList.add('hidden')" class="flex-1 py-3 rounded-xl border border-gray-600 text-gray-300 hover:bg-gray-700 font-bold">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', aptModalHTML);
    }

    // 2. Modal Treino
    if (!document.getElementById('workout-modal')) {
        const workoutModalHTML = `
        <div id="workout-modal" class="fixed inset-0 bg-black bg-opacity-70 hidden z-50 flex items-center justify-center backdrop-blur-sm">
            <div class="bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-8 border border-gray-700">
                <h3 id="workout-modal-title" class="text-2xl font-bold mb-6 text-white flex items-center"><i class="fas fa-dumbbell text-red-500 mr-3"></i>Novo Plano de Treino</h3>
                <form onsubmit="handleWorkoutSubmit(event)">
                    <input type="hidden" id="workout-id">
                    <div class="space-y-4">
                        <div><label class="block text-gray-400 mb-2 text-sm">Nome do Treino</label><input type="text" id="workout-name" placeholder="Ex: Treino A" required class="w-full bg-gray-900 text-white p-3 rounded-xl border border-gray-600 focus:border-red-500 outline-none"></div>
                        <div><label class="block text-gray-400 mb-2 text-sm">Descrição / Exercícios</label><textarea id="workout-desc" rows="4" class="w-full bg-gray-900 text-white p-3 rounded-xl border border-gray-600 focus:border-red-500 outline-none"></textarea></div>
                    </div>
                    <div class="flex space-x-3 mt-8">
                        <button type="button" onclick="document.getElementById('workout-modal').classList.add('hidden')" class="flex-1 py-3 rounded-xl border border-gray-600 text-gray-300 hover:bg-gray-700 font-bold">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700">Salvar Treino</button>
                    </div>
                </form>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', workoutModalHTML);
    }

    // 3. NOVO: Modal de Detalhes do Dia
    if (!document.getElementById('day-details-modal')) {
        const dayModalHTML = `
        <div id="day-details-modal" class="fixed inset-0 bg-black bg-opacity-70 hidden z-50 flex items-center justify-center backdrop-blur-sm">
            <div class="bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 border border-gray-700">
                <div class="flex justify-between items-center mb-6 border-b border-gray-700 pb-4">
                    <h3 id="day-modal-title" class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-calendar-day text-red-500 mr-3"></i>Resumo do Dia
                    </h3>
                    <button onclick="document.getElementById('day-details-modal').classList.add('hidden')" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="day-modal-content" class="space-y-3 max-h-64 overflow-y-auto">
                    <!-- Lista de treinos injetada aqui -->
                </div>

                <div class="mt-6 pt-4 border-t border-gray-700">
                    <button onclick="document.getElementById('day-details-modal').classList.add('hidden')" class="w-full py-2 rounded-xl bg-gray-700 hover:bg-gray-600 text-white font-bold transition-all">
                        Fechar
                    </button>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', dayModalHTML);
    }
}

// INJETA O FILTRO DE DATA NA ABA HORÁRIOS
function injectScheduleFilter() {
    const section = document.getElementById('horarios');
    if (!section) return;
    
    const header = section.querySelector('h3');
    if(header && !document.getElementById('schedule-date-filter')) {
        const container = document.createElement('div');
        container.className = "flex flex-col md:flex-row justify-between items-center mb-6 gap-4";
        
        header.parentNode.insertBefore(container, header);
        container.appendChild(header);
        header.classList.remove('mb-6');

        const today = new Date().toISOString().split('T')[0];
        const inputDiv = document.createElement('div');
        inputDiv.innerHTML = `
            <div class="flex items-center bg-gray-800 rounded-lg p-1 border border-gray-700">
                <span class="text-gray-400 px-3 text-sm font-medium">Data:</span>
                <input type="date" id="schedule-date-filter" value="${today}" 
                    class="bg-transparent text-white p-2 outline-none cursor-pointer font-bold">
            </div>
        `;
        container.appendChild(inputDiv);

        document.getElementById('schedule-date-filter').addEventListener('change', (e) => {
            loadSchedules(e.target.value);
        });
        
        loadSchedules(today);
    }
}

// ABRE MODAL DO DIA (CLIQUE NO CALENDÁRIO)
function openDayDetails(year, month, day) {
    const modal = document.getElementById('day-details-modal');
    const title = document.getElementById('day-modal-title');
    const content = document.getElementById('day-modal-content');
    
    // Formata título
    const dateStr = `${day}/${month + 1}/${year}`;
    title.innerHTML = `<i class="fas fa-calendar-day text-red-500 mr-3"></i>Dia ${dateStr}`;
    
    // Filtra treinos do dia
    content.innerHTML = '';
    
    // Procura nos agendamentos globais
    const treinosDoDia = userAppointments.filter(apt => {
        if(apt.status === 'cancelado') return false;
        const partes = apt.data_treino.split('-');
        const aptAno = parseInt(partes[0]);
        const aptMes = parseInt(partes[1]) - 1;
        const aptDia = parseInt(partes[2]);
        return aptAno === year && aptMes === month && aptDia === day;
    });

    if (treinosDoDia.length === 0) {
        content.innerHTML = `
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-bed text-4xl mb-3 opacity-30"></i>
                <p>Nenhum treino agendado para este dia.</p>
                <p class="text-xs mt-2">Aproveite o descanso!</p>
            </div>
        `;
    } else {
        treinosDoDia.forEach(treino => {
            const hora = treino.hora_inicio.substring(0, 5);
            content.innerHTML += `
                <div class="bg-gray-700/50 p-4 rounded-xl border-l-4 border-red-500 flex justify-between items-center">
                    <div>
                        <p class="font-bold text-white text-lg">${treino.tipo_treino}</p>
                        <p class="text-gray-400 text-sm flex items-center">
                            <i class="fas fa-clock mr-2 text-red-400"></i> ${hora}
                        </p>
                    </div>
                    <div class="bg-gray-800 p-2 rounded-lg">
                        <i class="fas fa-dumbbell text-red-500"></i>
                    </div>
                </div>
            `;
        });
    }

    modal.classList.remove('hidden');
}

// Abre Modal com dados Pré-Preenchidos (Vindo do Horário)
function openAppointmentModal(preDate = null, preTime = null) {
    const select = document.getElementById('modal-type');
    select.innerHTML = ''; 

    if (userWorkouts.length === 0) {
        const opt = document.createElement('option');
        opt.value = "Musculação Livre";
        opt.innerText = "Musculação Livre";
        select.appendChild(opt);
    } else {
        userWorkouts.forEach(treino => {
            const opt = document.createElement('option');
            opt.value = treino.nome_treino;
            opt.innerText = treino.nome_treino;
            select.appendChild(opt);
        });
    }

    const modal = document.getElementById('appointment-modal');
    const today = new Date().toISOString().split('T')[0];
    const inputDate = document.getElementById('modal-date');
    const inputTime = document.getElementById('modal-time');

    inputDate.min = today;
    inputDate.value = preDate || today;
    inputTime.value = preTime || "18:00";

    modal.classList.remove('hidden');
}

function openWorkoutModal(id = null, name = '', desc = '') {
    const modal = document.getElementById('workout-modal');
    document.getElementById('workout-id').value = id || ''; 
    document.getElementById('workout-name').value = name;
    document.getElementById('workout-desc').value = desc;
    document.getElementById('workout-modal-title').innerText = id ? 'Editar Treino' : 'Novo Plano de Treino';
    modal.classList.remove('hidden');
}

// ==========================================
// 5. AÇÕES (SUBMITS E CRUD)
// ==========================================

async function handleAppointmentSubmit(event) {
    event.preventDefault();
    const data = document.getElementById('modal-date').value;
    const hora = document.getElementById('modal-time').value;
    const tipo = document.getElementById('modal-type').value;

    try {
        const response = await fetch('../api/create_agendamento.php', {
            method: 'POST', body: JSON.stringify({ date: data, time: hora, type: tipo })
        });
        const result = await response.json();
        if (result.success) {
            document.getElementById('appointment-modal').classList.add('hidden');
            loadAppointments(); 
            loadDashboardStats();
            loadNotifications(); 
            
            const scheduleFilter = document.getElementById('schedule-date-filter');
            if(scheduleFilter && scheduleFilter.value === data) {
                loadSchedules(data);
            }
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (e) { alert('Erro na conexão.'); }
}

async function handleWorkoutSubmit(event) {
    event.preventDefault();
    const id = document.getElementById('workout-id').value;
    const name = document.getElementById('workout-name').value;
    const desc = document.getElementById('workout-desc').value;

    try {
        const response = await fetch('../api/save_treino.php', {
            method: 'POST', body: JSON.stringify({ id: id, name: name, exercises: desc })
        });
        const result = await response.json();
        if (result.success) {
            document.getElementById('workout-modal').classList.add('hidden');
            loadWorkouts(); 
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (e) { alert('Erro na conexão.'); }
}

async function deleteWorkout(id) {
    if (!confirm('Tem certeza que deseja apagar este plano de treino?')) return;
    try {
        await fetch('../api/delete_treino.php', { method: 'POST', body: JSON.stringify({ id: id }) });
        loadWorkouts();
    } catch(e) { console.error(e); }
}

async function saveProfile() {
    const name = document.getElementById('input-fullname').value;
    const phone = document.getElementById('input-phone').value;
    const btn = event.target; 
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    try {
        await fetch('../api/update_profile.php', { method: 'POST', body: JSON.stringify({ name, phone }) });
        alert('Perfil atualizado!');
        document.getElementById('header-user-name').innerText = name;
        document.getElementById('user-name').innerText = name.split(' ')[0];
    } catch (e) { alert('Erro ao salvar.'); } 
    finally { btn.innerHTML = originalText; }
}

async function cancelAppointment(id) {
    if (!confirm('Deseja cancelar este agendamento?')) return;
    try {
        await fetch('../api/cancel_agendamento.php', { method: 'POST', body: JSON.stringify({ id }) });
        loadAppointments(); 
        loadDashboardStats();
        loadNotifications(); 
        const scheduleFilter = document.getElementById('schedule-date-filter');
        if(scheduleFilter) loadSchedules(scheduleFilter.value);
    } catch (e) { alert('Erro ao cancelar.'); }
}

async function deleteNotification(id) {
    try {
        await fetch('../api/delete_notificacao.php', { method: 'POST', body: JSON.stringify({ id: id }) });
        loadNotifications(); 
    } catch (e) { console.error("Erro ao apagar notificação", e); }
}

// ==========================================
// 6. RENDERIZAÇÃO
// ==========================================

function renderSchedules(schedules, dateStr) {
  const container = document.getElementById('available-schedules');
  if(!container) return;
  container.innerHTML = schedules.map(s => `
    <div class="schedule-item bg-gray-800 p-5 rounded-xl border-l-4 ${s.available ? 'border-green-500' : 'border-red-500'} transition-all flex justify-between items-center group">
      <div>
        <p class="font-bold text-lg text-white">${s.label}</p>
        <p class="text-sm ${s.available ? 'text-green-400' : 'text-red-400'}">
          ${s.available ? '<i class="fas fa-check-circle mr-1"></i>Livre' : `<i class="fas fa-dumbbell mr-1"></i>${s.treino}`}
        </p>
      </div>
      ${s.available
        ? `<button onclick="openAppointmentModal('${dateStr}', '${s.time}')" class="bg-gray-700 hover:bg-green-600 hover:text-white text-gray-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
             <i class="fas fa-plus mr-1"></i> Agendar
           </button>`
        : `<button disabled class="opacity-50 cursor-not-allowed text-gray-500 px-4 py-2 text-sm font-medium">Ocupado</button>`
      }
    </div>
  `).join('');
}

function renderWorkoutPlans(plans) {
  const container = document.getElementById('workout-plans');
  if(!container) return;
  
  if (!plans || plans.length === 0) {
      container.innerHTML = '<div class="col-span-full text-gray-400 text-center py-4">Você ainda não criou nenhum plano de treino.</div>';
      return;
  }
  
  container.innerHTML = plans.map(p => `
    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 hover:border-red-500 transition-all relative group flex flex-col justify-between h-full">
      <div class="flex justify-between items-start gap-4 mb-4">
          <h4 class="text-xl font-bold text-red-400 break-words flex-1">${p.nome_treino}</h4>
          <div class="flex space-x-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity min-w-fit bg-gray-800 rounded-lg">
            <button onclick='openWorkoutModal(${p.id}, "${p.nome_treino}", "${(p.descricao || '').replace(/\n/g, ' ')}")' class="text-blue-400 hover:text-white p-2 hover:bg-blue-600 rounded transition-colors" title="Editar"><i class="fas fa-edit"></i></button>
            <button onclick="deleteWorkout(${p.id})" class="text-red-500 hover:text-white p-2 hover:bg-red-600 rounded transition-colors" title="Excluir"><i class="fas fa-trash"></i></button>
          </div>
      </div>
      <p class="text-gray-300 text-sm whitespace-pre-line overflow-hidden text-ellipsis">${p.descricao || 'Sem descrição.'}</p>
    </div>
  `).join('');
}

// ==========================================
// 7. INICIALIZAÇÃO
// ==========================================

document.addEventListener('DOMContentLoaded', async () => {
    
    // --- 1. CARREGAMENTO IMEDIATO (CORREÇÃO DO PROBLEMA DE F5) ---
    // Agora essas funções iniciam assim que a página abre, sem esperar o usuário
    console.log("Iniciando carregamento do Dashboard...");
    loadAppointments(); 
    loadWorkouts(); 
    loadDashboardStats(); 
    loadNotifications(); 

    // --- 2. CONFIGURAÇÃO DE UI E MODALS ---
    injectModals(); 
    injectScheduleFilter();

    const saveBtn = document.querySelector('#perfil button.action-btn'); 
    if(saveBtn) saveBtn.addEventListener('click', saveProfile);

    const newAptBtn = document.querySelector('#agendamentos button.action-btn');
    if(newAptBtn) {
        newAptBtn.removeEventListener('click', newAptBtn.onclick);
        newAptBtn.addEventListener('click', () => openAppointmentModal());
    }

    const treinosSection = document.getElementById('treinos');
    if (treinosSection) {
        const cardHeader = treinosSection.querySelector('h3');
        // Verifica se o botão já existe para não duplicar
        if (cardHeader && !document.getElementById('btn-new-workout')) {
            const btn = document.createElement('button');
            btn.id = 'btn-new-workout';
            btn.className = "ml-auto bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition-colors flex-shrink-0";
            btn.innerHTML = '<i class="fas fa-plus mr-2"></i>Novo Treino';
            btn.onclick = () => openWorkoutModal();
            
            const headerContainer = document.createElement('div');
            headerContainer.className = "flex justify-between items-center mb-6";
            cardHeader.parentNode.insertBefore(headerContainer, cardHeader);
            headerContainer.appendChild(cardHeader);
            cardHeader.classList.remove('mb-6');
            headerContainer.appendChild(btn);
        }
    }

    // --- 3. MENUS E DROPDOWNS ---
    const notifBtn = document.getElementById('notif-btn');
    const notifMenu = document.getElementById('notif-menu');
    if (notifBtn && notifMenu) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifMenu.classList.toggle('hidden');
            document.getElementById('profile-menu').classList.add('hidden'); 
        });
    }

    const profileBtn = document.getElementById('profile-btn');
    const profileMenu = document.getElementById('profile-menu');
    const dropdownIcon = document.getElementById('dropdown-icon');
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('hidden');
            if(notifMenu) notifMenu.classList.add('hidden'); 
            if(dropdownIcon) dropdownIcon.style.transform = profileMenu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        });
        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.add('hidden');
                if(dropdownIcon) dropdownIcon.style.transform = 'rotate(0deg)';
            }
        });
    }

    document.addEventListener('click', () => {
        if(notifMenu) notifMenu.classList.add('hidden');
        if(profileMenu) {
            profileMenu.classList.add('hidden');
            if(dropdownIcon) dropdownIcon.style.transform = 'rotate(0deg)';
        }
    });

    // --- 4. CALENDÁRIO ---
    let currentDate = new Date();
    renderCalendar(currentDate, []); 
    const prevBtn = document.getElementById("prev-month");
    const nextBtn = document.getElementById("next-month");
    if(prevBtn) prevBtn.addEventListener("click", () => { 
        currentDate.setMonth(currentDate.getMonth() - 1); 
        renderCalendar(currentDate, userAppointments); 
    });
    if(nextBtn) nextBtn.addEventListener("click", () => { 
        currentDate.setMonth(currentDate.getMonth() + 1); 
        renderCalendar(currentDate, userAppointments); 
    });

    // --- 5. DADOS SECUNDÁRIOS (LEGADO) ---
    // Mantemos isso apenas para preencher coisas que o PHP não preencheu (como memberSince)
    // Mas note que as funções vitais (loadAppointments etc) já foram chamadas lá em cima.
    try {
        const data = await fetchUserData();
        if (data) {
            userData = data;
            const notifCount = document.getElementById('notification-count');
            if (notifCount) {
                const count = data.notifications || 0;
                notifCount.innerText = count;
                notifCount.style.display = (count === 0) ? 'none' : 'flex';
            }
            if(document.getElementById('stat-member-since')) {
                document.getElementById('stat-member-since').innerText = data.memberSince;
            }
        }
    } catch (error) {
        console.log("Dados secundários não carregados, mas o dashboard deve funcionar.");
    }
});