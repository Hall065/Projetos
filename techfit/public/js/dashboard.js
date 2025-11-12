// public/js/dashboard.js

// ==========================================
// API SERVICE - Comunicação com Backend
// ==========================================

const API = {
    baseUrl: 'api.php',
    
    async request(action, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(`${this.baseUrl}?action=${action}`, options);
            return await response.json();
        } catch (error) {
            console.error(`Erro na requisição ${action}:`, error);
            return null;
        }
    },
    
    getUserData() {
        return this.request('getUserData');
    },
    
    getDashboardStats() {
        return this.request('getDashboardStats');
    },
    
    getSchedules() {
        return this.request('getSchedules');
    },
    
    getAppointments() {
        return this.request('getAppointments');
    },
    
    getWorkoutPlans() {
        return this.request('getWorkoutPlans');
    },
    
    createAppointment(scheduleId, title, date) {
        return this.request('createAppointment', 'POST', {
            schedule_id: scheduleId,
            title: title,
            date: date
        });
    },
    
    confirmAppointment(aptId) {
        return this.request('confirmAppointment', 'POST', { id: aptId });
    },
    
    cancelAppointment(aptId) {
        return this.request('cancelAppointment', 'POST', { id: aptId });
    },
    
    updateProfile(name, email, phone) {
        return this.request('updateProfile', 'POST', {
            name: name,
            email: email,
            phone: phone
        });
    }
};

// ==========================================
// RENDERIZAÇÃO DE COMPONENTES
// ==========================================

function renderSchedules(schedules) {
    const container = document.getElementById('available-schedules');
    
    if (!schedules || schedules.length === 0) {
        container.innerHTML = '<p class="text-gray-400 text-center col-span-3">Nenhum horário disponível</p>';
        return;
    }
    
    container.innerHTML = schedules.map(schedule => `
        <div class="schedule-item bg-gray-800 p-5 rounded-xl border-l-4 ${schedule.available ? 'border-green-500' : 'border-red-500'} transition-all">
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-bold text-lg">${schedule.time}</p>
                    <p class="text-sm ${schedule.available ? 'text-green-400' : 'text-red-400'}">
                        <i class="fas fa-circle text-xs mr-1"></i>
                        ${schedule.available ? 'Disponível' : 'Ocupado'}
                    </p>
                </div>
                ${schedule.available 
                    ? `<button class="action-btn px-4 py-2 rounded-lg text-sm font-medium" onclick="scheduleAppointment(${schedule.id})">Agendar</button>`
                    : '<button class="bg-gray-700 px-4 py-2 rounded-lg text-sm cursor-not-allowed opacity-50">Indisponível</button>'
                }
            </div>
        </div>
    `).join('');
}

function renderAppointments(appointments) {
    const container = document.getElementById('appointments-list');
    
    if (!appointments || appointments.length === 0) {
        container.innerHTML = '<p class="text-gray-400 text-center">Nenhum agendamento encontrado</p>';
        return;
    }
    
    container.innerHTML = appointments.map(apt => `
        <div class="schedule-item bg-gray-800 p-5 rounded-xl border border-gray-700">
            <div class="flex justify-between items-center">
                <div class="flex-1">
                    <p class="font-bold text-lg mb-1">${apt.title}</p>
                    <p class="text-gray-400 flex items-center">
                        <i class="fas fa-clock mr-2"></i>${apt.date}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <button class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors" 
                            onclick="confirmAppointment(${apt.id})">
                        <i class="fas fa-check mr-1"></i>Confirmar
                    </button>
                    <button class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors" 
                            onclick="cancelAppointment(${apt.id})">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function renderWorkoutPlans(plans) {
    const container = document.getElementById('workout-plans');
    
    if (!plans || plans.length === 0) {
        container.innerHTML = '<p class="text-gray-400 text-center col-span-2">Nenhum plano de treino encontrado</p>';
        return;
    }
    
    container.innerHTML = plans.map(plan => `
        <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 hover:border-red-500 transition-all">
            <h4 class="text-xl font-bold mb-4 text-red-400">${plan.name}</h4>
            <ul class="space-y-2 text-gray-300 mb-4">
                ${plan.exercises.map(ex => `
                    <li class="flex items-center">
                        <i class="fas fa-dumbbell text-red-500 text-xs mr-3"></i>${ex}
                    </li>
                `).join('')}
            </ul>
            <button class="action-btn w-full py-3 rounded-xl font-medium" onclick="editWorkout(${plan.id})">
                <i class="fas fa-edit mr-2"></i>Editar
            </button>
        </div>
    `).join('');
}

// ==========================================
// NAVEGAÇÃO
// ==========================================

function navigateTo(sectionId) {
    // Remove active de todos os menus
    document.querySelectorAll('.sidebar-item').forEach(item => {
        item.classList.remove('active');
    });

    // Ativa o menu clicado
    event.currentTarget.classList.add('active');

    // Esconde todas as sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });

    // Mostra a section escolhida
    document.getElementById(sectionId).classList.add('active');

    // Atualiza o título
    const titles = {
        'inicio': 'Dashboard',
        'horarios': 'Horários Disponíveis',
        'agendamentos': 'Meus Agendamentos',
        'treinos': 'Meus Treinos',
        'agenda': 'Agenda de Treinos',
        'perfil': 'Meu Perfil'
    };

    document.getElementById('page-title').textContent = titles[sectionId] || 'Dashboard';

    // Carrega dados específicos da seção
    loadSectionData(sectionId);
}

async function loadSectionData(sectionId) {
    switch(sectionId) {
        case 'horarios':
            const schedules = await API.getSchedules();
            renderSchedules(schedules);
            break;
        case 'agendamentos':
            const appointments = await API.getAppointments();
            renderAppointments(appointments);
            break;
        case 'treinos':
            const plans = await API.getWorkoutPlans();
            renderWorkoutPlans(plans);
            break;
    }
}

// ==========================================
// AÇÕES DO USUÁRIO
// ==========================================

async function scheduleAppointment(scheduleId) {
    const title = prompt('Título do agendamento:');
    if (!title) return;
    
    const date = prompt('Data (YYYY-MM-DD):');
    if (!date) return;
    
    const result = await API.createAppointment(scheduleId, title, date);
    
    if (result && result.success) {
        alert('✅ ' + result.message);
        loadSectionData('agendamentos');
    } else {
        alert('❌ ' + (result ? result.message : 'Erro ao agendar'));
    }
}

async function confirmAppointment(aptId) {
    const result = await API.confirmAppointment(aptId);
    
    if (result && result.success) {
        alert('✅ ' + result.message);
        loadSectionData('agendamentos');
    } else {
        alert('❌ ' + (result ? result.message : 'Erro ao confirmar'));
    }
}

async function cancelAppointment(aptId) {
    if (!confirm('Tem certeza que deseja cancelar este agendamento?')) return;
    
    const result = await API.cancelAppointment(aptId);
    
    if (result && result.success) {
        alert('✅ ' + result.message);
        loadSectionData('agendamentos');
    } else {
        alert('❌ ' + (result ? result.message : 'Erro ao cancelar'));
    }
}

function editWorkout(planId) {
    alert(`✏️ Editando plano de treino ID: ${planId}`);
    // Implementar modal ou redirecionamento
}

// ==========================================
// CALENDÁRIO
// ==========================================

let currentDate = new Date();

function renderCalendar(date) {
    const daysContainer = document.getElementById("calendar-days");
    const monthYearEl = document.getElementById("month-year");

    const month = date.getMonth();
    const year = date.getFullYear();

    const firstDay = new Date(year, month, 1).getDay();
    const lastDate = new Date(year, month + 1, 0).getDate();
    const prevLastDate = new Date(year, month, 0).getDate();

    const monthNames = [
        "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
        "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
    ];

    daysContainer.innerHTML = "";
    monthYearEl.innerHTML = `<i class="fas fa-calendar-alt mr-2"></i>${monthNames[month]} ${year}`;

    // Dias do mês anterior
    for (let i = firstDay; i > 0; i--) {
        const dayEl = document.createElement("div");
        dayEl.className = "p-3 text-center text-gray-600 rounded-lg";
        dayEl.textContent = prevLastDate - i + 1;
        daysContainer.appendChild(dayEl);
    }

    // Dias do mês atual
    const today = new Date();
    for (let i = 1; i <= lastDate; i++) {
        const dayEl = document.createElement("div");
        dayEl.className = "calendar-day p-3 text-center rounded-lg cursor-pointer ";

        if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
            dayEl.className += "bg-gradient-to-br from-red-600 to-red-800 font-bold shadow-lg ring-2 ring-red-500";
        } else {
            dayEl.className += "bg-gray-800 hover:bg-red-600 transition-all border border-gray-700";
        }

        dayEl.textContent = i;
        dayEl.addEventListener("click", () => {
            showDayDetails(i, month + 1, year);
        });

        daysContainer.appendChild(dayEl);
    }
}

function showDayDetails(day, month, year) {
    alert(`📅 Dia ${day}/${month}/${year}\n\n🏋️ Treino A - Peito e Tríceps\n⏰ 18:00`);
}

// ==========================================
// PROFILE DROPDOWN
// ==========================================

document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("profile-btn");
    const menu = document.getElementById("profile-menu");
    const icon = document.getElementById("dropdown-icon");

    btn.addEventListener("click", (e) => {
        e.stopPropagation();
        menu.classList.toggle("hidden");
        icon.style.transform = menu.classList.contains("hidden") ? "rotate(0deg)" : "rotate(180deg)";
    });

    document.addEventListener("click", () => {
        if (!menu.classList.contains("hidden")) {
            menu.classList.add("hidden");
            icon.style.transform = "rotate(0deg)";
        }
    });
});

// ==========================================
// INICIALIZAÇÃO
// ==========================================

async function initDashboard() {
    try {
        // Carrega dados do usuário
        const user = await API.getUserData();
        if (user) {
            document.getElementById('user-name').textContent = user.name.split(' ')[0];
            document.getElementById('header-user-name').textContent = user.name;
            document.getElementById('user-plan').textContent = `Membro ${user.plan}`;
            document.getElementById('notification-count').textContent = user.notifications;
            document.getElementById('input-fullname').value = user.name;
            document.getElementById('input-email').value = user.email;
            document.getElementById('input-phone').value = user.phone;
            document.getElementById('stat-member-since').textContent = user.member_since;
            document.getElementById('stat-current-plan').textContent = user.plan;
        }

        // Carrega estatísticas do dashboard
        const stats = await API.getDashboardStats();
        if (stats) {
            document.getElementById('stat-monthly-workouts').textContent = stats.monthly_workouts;
            document.getElementById('stat-next-workout').textContent = stats.next_workout;
            document.getElementById('stat-next-workout-type').textContent = stats.next_workout_type;
            document.getElementById('stat-calories').textContent = stats.calories.toLocaleString('pt-BR');
            document.getElementById('stat-streak').textContent = stats.streak;
            document.getElementById('sidebar-streak').textContent = `${stats.streak} dias`;
            document.getElementById('stat-total-workouts').textContent = stats.total_workouts;
            document.getElementById('stat-weekly-frequency').textContent = `${stats.weekly_frequency} dias`;
        }

        // Renderiza calendário
        renderCalendar(currentDate);

        // Event listeners do calendário
        document.getElementById("prev-month").addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate);
        });

        document.getElementById("next-month").addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate);
        });

    } catch (error) {
        console.error('Erro ao inicializar dashboard:', error);
    }
}

// Inicializa o dashboard quando a página carregar
document.addEventListener('DOMContentLoaded', initDashboard);