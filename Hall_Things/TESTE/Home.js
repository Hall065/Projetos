function setActiveMenu(element, sectionId) {
  // Remove active de todos os menus
  document.querySelectorAll('.sidebar-item').forEach(item => {
    item.classList.remove('active');
  });

  // Ativa o menu clicado
  element.classList.add('active');

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
}

// Eventos extras
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('[data-action]').forEach(btn => {
    const action = btn.dataset.action;

    if (action === "agendar") {
      btn.addEventListener('click', () => alert("Funcionalidade de agendamento em breve!"));
    }

    if (action === "iniciar-treino") {
      btn.addEventListener('click', () => alert("Iniciando treino... Bora lá!"));
    }

    if (action === "salvar") {
      btn.addEventListener('click', () => alert("Alterações salvas!"));
    }
  });
});

// ----------------------
// CALENDÁRIO
// ----------------------

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
  monthYearEl.textContent = `${monthNames[month]} ${year}`;

  // Dias do mês anterior (cinza)
  for (let i = firstDay; i > 0; i--) {
    const dayEl = document.createElement("div");
    dayEl.className = "p-3 text-center text-gray-500";
    dayEl.textContent = prevLastDate - i + 1;
    daysContainer.appendChild(dayEl);
  }

  // Dias atuais do mês
  const today = new Date();
  for (let i = 1; i <= lastDate; i++) {
    const dayEl = document.createElement("div");

    if (
      i === today.getDate() &&
      month === today.getMonth() &&
      year === today.getFullYear()
    ) {
      dayEl.className = "p-3 text-center bg-red-600 rounded font-bold";
    } else {
      dayEl.className = "p-3 text-center bg-gray-700 rounded hover:bg-red-600 cursor-pointer transition";
    }

    dayEl.textContent = i;

    dayEl.addEventListener("click", () => {
      alert(`Treino no dia ${i}/${month + 1}/${year}`);
    });

    daysContainer.appendChild(dayEl);
  }
}

// Navegação
document.addEventListener("DOMContentLoaded", () => {
  renderCalendar(currentDate);

  document.getElementById("prev-month").addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar(currentDate);
  });

  document.getElementById("next-month").addEventListener("click", () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar(currentDate);
  });
});
