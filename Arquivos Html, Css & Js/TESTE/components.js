
// components.js - scripts reutilizáveis
document.addEventListener('DOMContentLoaded', () => {
  // sidebar toggle (for responsive)
  const toggleBtn = document.getElementById('sidebar-toggle');
  const sidebar = document.querySelector('.sidebar-root');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('hidden');
      // preserve scroll on small screens
      document.querySelector('body').classList.toggle('overflow-hidden');
    });
  }

  // Simula logout — remove qualquer "login" e leva ao Login.html
  document.querySelectorAll('[data-action="logout"]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      // efeito visual então redireciona para login (simulação)
      btn.textContent = 'Saindo...';
      setTimeout(() => window.location.href = 'Login.html', 600);
    });
  });

  // Simula feedback de formulários com loaders (front-only)
  document.querySelectorAll('form[data-simulate="post"]').forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const submit = form.querySelector('[type="submit"]');
      if (!submit) return;
      const originalText = submit.innerHTML;
      submit.disabled = true;
      submit.innerHTML = '<span class="loader" aria-hidden="true"></span>';
      setTimeout(() => {
        submit.disabled = false;
        submit.innerHTML = originalText;
        // pequena notificação visual
        alert('Simulação: formulário enviado (sem backend).');
      }, 900);
    });
  });
});
