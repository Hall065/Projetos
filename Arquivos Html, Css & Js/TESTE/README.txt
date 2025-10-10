TechFit - Frontend aprimorado (versão estática)
Arquivos incluídos:
- Principal.html, Principal.css, Principal.js
- Home.html, Home.css (ajustado), Home.js
- Admin.html, Admin.css (ajustado), Admin.js
- Login.html, Login.css, Login.js
- components.css (novos estilos compartilhados)
- components.js (scripts compartilhados: sidebar toggle, logout simulado, form loader)
Como usar:
1. Descompacte techfit_frontend.zip em uma pasta pública (ex: 'public/') e abra Principal.html no navegador.
2. As ações que simulam backend (login, formulários) usam data-simulate="post" e componentes.js para mostrar loader e alerta.
3. Para conectar backend depois, troque os forms para action="seu_php.php" e remova data-simulate.