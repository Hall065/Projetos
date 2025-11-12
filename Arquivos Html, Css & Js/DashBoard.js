// ==========================================
    // CONFIGURAÇÃO DE DADOS (Preparado para MySQL)
    // ==========================================
    
    // Estrutura de dados que virá do banco MySQL
    let userData = {
      id: 1,
      name: "João Silva",
      email: "joao@email.com",
      phone: "(11) 99999-9999",
      plan: "Premium",
      memberSince: "Janeiro 2024",
      notifications: 3
    };

    let dashboardStats = {
      monthlyWorkouts: 24,
      nextWorkout: "Hoje 18:00",
      nextWorkoutType: "Treino A - Peito",
      calories: 2450,
      streak: 7,
      totalWorkouts: 156,
      weeklyFrequency: 4.2
    };

    let availableSchedules = [
      { id: 1, time: "06:00 - 07:00", available: true },
      { id: 2, time: "18:00 - 19:00", available: false },
      { id: 3, time: "19:00 - 20:00", available: true },
      { id: 4, time: "07:00 - 08:00", available: true },
      { id: 5, time: "20:00 - 21:00", available: true },
      { id: 6, time: "21:00 - 22:00", available: false }
    ];

    let appointments = [
      { id: 1, title: "Avaliação Física Mensal", date: "Hoje, 19:00", status: "pending" },
      { id: 2, title: "Consulta com Personal Trainer", date: "Amanhã, 08:00", status: "pending" },
      { id: 3, title: "Treino de Pernas", date: "Amanhã, 18:00", status: "confirmed" }
    ];

    let workoutPlans = [
      {
        id: 1,
        name: "Treino A - Peito e Tríceps",
        exercises: [
          "Supino Reto - 4x12",
          "Supino Inclinado - 3x10",
          "Crucifixo - 3x12",
          "Tríceps Testa - 3x12",
          "Tríceps Corda - 3x15"
        ]
      },
      {
        id: 2,
        name: "Treino B - Costas e Bíceps",
        exercises: [
          "Puxada Frontal - 4x12",
          "Remada Curvada - 3x10",
          "Pullover - 3x12",
          "Rosca Direta - 3x12",
          "Rosca Martelo - 3x15"
        ]
      }
    ];

    let recentActivities = [
      { id: 1, type: "completed", title: "Treino de Peito concluído", time: "Há 2 horas", icon: "check", color: "green" },
      { id: 2, type: "scheduled", title: "Treino agendado para amanhã", time: "Há 1 dia", icon: "calendar", color: "blue" },
      { id: 3, type: "achievement", title: "Meta semanal atingida!", time: "Há 2 dias", icon: "trophy", color: "purple" }
    ];

    // ==========================================
    // FUNÇÕES DE INTEGRAÇÃO COM BANCO DE DADOS
    // ==========================================

    // Simulação de requisição ao backend PHP/MySQL
    async function fetchUserData() {
      try {
        // Em produção, substituir por:
        // const response = await fetch('api/user.php');
        // const data = await response.json();
        
        // Simulação de delay da requisição
        await new Promise(resolve => setTimeout(resolve, 500));
        return userData;
      } catch (error) {
        console.error('Erro ao buscar dados do usuário:', error);
        return null;
      }
    }

    async function fetchDashboardStats() {
      try {
        // const response = await fetch('api/dashboard-stats.php');
        // const data = await response.json();
        await new Promise(resolve => setTimeout(resolve, 500));
        return dashboardStats;
      } catch (error) {
        console.error('Erro ao buscar estatísticas:', error);
        return null;
      }
    }

    async function fetchSchedules() {
      try {
        // const response = await fetch('api/schedules.php');
        // const data = await response.json();
        await new Promise(resolve => setTimeout(resolve, 500));
        return availableSchedules;
      } catch (error) {
        console.error('Erro ao buscar horários:', error);
        return [];
      }
    }

    async function fetchAppointments() {
      try {
        // const response = await fetch('api/appointments.php');
        // const data = await response.json();
        await new Promise(resolve => setTimeout(resolve, 500));
        return appointments;
      } catch (error) {
        console.error('Erro ao buscar agendamentos:', error);
        return [];
      }
    }

    async function fetchWorkoutPlans() {
      try {
        // const response = await fetch('api/workout-plans.php');
        // const data = await response.json();
        await new Promise(resolve => setTimeout(resolve, 500));
        return workoutPlans;
      } catch (error) {
        console.error('Erro ao buscar planos de treino:', error);
        return [];
      }
    }

    async function saveUserProfile(profileData) {
      try {
        // const response = await fetch('api/update-profile.php', {
        //   method: 'POST',
        //   headers: { 'Content-Type': 'application/json' },
        //   body: JSON.stringify(profileData)
        // });
        // const data = await response.json();
        await new Promise(resolve => setTimeout(resolve, 500));
        return { success: true, message: 'Perfil atualizado com sucesso!' };
      } catch (error) {
        console.error('Erro ao salvar perfil:', error);
        return { success: false, message: 'Erro ao atualizar perfil.' };
      }
    }

    // ==========================================
    // RENDERIZAÇÃO DE COMPONENTES
    // ==========================================

    function renderSchedules(schedules) {
      const container = document.getElementById('available-schedules');
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
              ? '<button class="action-btn px-4 py-2 rounded-lg text-sm font-medium" onclick="scheduleAppointment(' + schedule.id + ')">Agendar</button>'
              : '<button class="bg-gray-700 px-4 py-2 rounded-lg text-sm cursor-not-allowed opacity-50">Indisponível</button>'
            }
          </div>
        </div>
      `).join('');
    }

    function renderAppointments(appointments) {
      const container = document.getElementById('appointments-list');
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
              <button class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors" onclick="confirmAppointment(${apt.id})">
                <i class="fas fa-check mr-1"></i>Confirmar
              </button>
              <button class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors" onclick="cancelAppointment(${apt.id})">
                <i class="fas fa-times mr-1"></i>Cancelar
              </button>
            </div>
          </div>
        </div>
      `).join('');
    }

    function renderWorkoutPlans(plans) {
      const container = document.getElementById('workout-plans');
      container.innerHTML = plans.map(plan => `
        <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 hover:border-red-500 transition-all">
          <h4 class="text-xl font-bold mb-4 text-red-400">${plan.name}</h4>
          <ul class="space-y-2 text-gray-300 mb-4">
            ${plan.exercises.map(ex => `<li class="flex items-center"><i class="fas fa-dumbbell text-red-500 text-xs mr-3"></i>${ex}</li>`).join('')}
          </ul>
          <button class="action-btn w-full py-3 rounded-xl font-medium" onclick="editWorkout(${plan.id})">
            <i class="fas fa-edit mr-2"></i>Editar
          </button>
        </div>
      `).join('');
    }

    // ==========================================
    // NAVEGAÇÃO E INTERAÇÕES
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

      // Carrega dados específicos da seção se necessário
      loadSectionData(sectionId);
    }

    async function loadSectionData(sectionId) {
      switch(sectionId) {
        case 'horarios':
          const schedules = await fetchSchedules();
          renderSchedules(schedules);
          break;
        case 'agendamentos':
          const appointments = await fetchAppointments();
          renderAppointments(appointments);
          break;
        case 'treinos':
          const plans = await fetchWorkoutPlans();
          renderWorkoutPlans(plans);
          break;
      }
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
      // Aqui você pode buscar treinos agendados para esse dia do banco
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
        const user = await fetchUserData();
        if (user) {
          document.getElementById('user-name').textContent = user.name.split(' ')[0];
          document.getElementById('header-user-name').textContent = user.name;
          document.getElementById('user-plan').textContent = `Membro ${user.plan}`;
          document.getElementById('notification-count').textContent = user.notifications;
          document.getElementById('input-fullname').value = user.name;
          document.getElementById('input-email').value = user.email;
          document.getElementById('input-phone').value = user.phone;
          document.getElementById('stat-member-since').textContent = user.memberSince;
          document.getElementById('stat-current-plan').textContent = user.plan;
        }

        // Carrega estatísticas do dashboard
        const stats = await fetchDashboardStats();
        if (stats) {
          document.getElementById('stat-monthly-workouts').textContent = stats.monthlyWorkouts;
          document.getElementById('stat-next-workout').textContent = stats.nextWorkout;
          document.getElementById('stat-next-workout-type').textContent = stats.nextWorkoutType;
          document.getElementById('stat-calories').textContent = stats.calories.toLocaleString('pt-BR');
          document.getElementById('stat-streak').textContent = stats.streak;
          document.getElementById('sidebar-streak').textContent = `${stats.streak} dias`;
          document.getElementById('stat-total-workouts').textContent = stats.totalWorkouts;
          document.getElementById('stat-weekly-frequency').textContent = `${stats.weeklyFrequency} dias`;
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

    // ==========================================
    // AÇÕES DO USUÁRIO
    // ==========================================

    function scheduleAppointment(scheduleId) {
      alert(`🎯 Agendando horário ID: ${scheduleId}\n\nEm produção, isso enviaria dados para o backend.`);
      // Em produção:
      // fetch('api/create-appointment.php', {
      //   method: 'POST',
      //   body: JSON.stringify({ scheduleId, userId: userData.id })
      // });
    }

    function confirmAppointment(aptId) {
      alert(`✅ Agendamento ${aptId} confirmado!`);
      // fetch('api/confirm-appointment.php', { method: 'POST', body: JSON.stringify({ aptId }) });
    }

    function cancelAppointment(aptId) {
      if (confirm('Tem certeza que deseja cancelar este agendamento?')) {
        alert(`❌ Agendamento ${aptId} cancelado!`);
        // fetch('api/cancel-appointment.php', { method: 'POST', body: JSON.stringify({ aptId }) });
      }
    }

    function editWorkout(planId) {
      alert(`✏️ Editando plano de treino ID: ${planId}`);
      // Redirecionar para página de edição ou abrir modal
    }

    // Inicializa o dashboard quando a página carregar
    document.addEventListener('DOMContentLoaded', initDashboard);