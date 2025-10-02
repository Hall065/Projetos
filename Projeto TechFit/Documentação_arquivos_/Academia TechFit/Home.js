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
  document.querySelectorAll('button').forEach(btn => {
    if (btn.textContent.includes("Agendar")) {
      btn.addEventListener('click', () => alert("Funcionalidade de agendamento em breve!"));
    }
    if (btn.textContent.includes("Iniciar Treino")) {
      btn.addEventListener('click', () => alert("Iniciando treino... Bora lá!"));
    }
  });
});

function renderCalendar() {
  const daysContainer = document.getElementById("calendar-days");
  const date = new Date();
  
  const month = date.getMonth();
  const year = date.getFullYear();

  // Pega o primeiro dia do mês (ex: 1 de Outubro = terça = 2)
  const firstDay = new Date(year, month, 1).getDay();

  // Último dia do mês
  const lastDate = new Date(year, month + 1, 0).getDate();

  // Últimos dias do mês anterior (para preencher antes do "1")
  const prevLastDate = new Date(year, month, 0).getDate();

  daysContainer.innerHTML = "";

  // Dias do mês anterior (cinza)
  for (let i = firstDay; i > 0; i--) {
    const dayEl = document.createElement("div");
    dayEl.className = "p-3 text-center text-gray-500";
    dayEl.textContent = prevLastDate - i + 1;
    daysContainer.appendChild(dayEl);
  }

  // Dias atuais do mês
  for (let i = 1; i <= lastDate; i++) {
    const dayEl = document.createElement("div");

    // Hoje
    if (i === date.getDate()) {
      dayEl.className = "p-3 text-center bg-red-600 rounded font-bold";
    } else {
      dayEl.className = "p-3 text-center bg-gray-700 rounded hover:bg-red-600 cursor-pointer transition";
    }

    dayEl.textContent = i;

    // Clique em um dia -> evento
    dayEl.addEventListener("click", () => {
      alert(`Treino no dia ${i}/${month+1}/${year}`);
    });

    daysContainer.appendChild(dayEl);
  }
}

// Gera quando carregar a página
document.addEventListener("DOMContentLoaded", renderCalendar);

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
