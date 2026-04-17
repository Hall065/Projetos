/**
 * ScoreBot — script.js
 * Fluxo com seleção PF/PJ, etapas de conversa e integração Flask
 * Atualizado com: Login/Registro, Voz (STT/TTS), Histórico, Memória
 */

// ============================================================
// ESTADO GLOBAL (original + novos)
// ============================================================
let sessionId       = null;
let userProfileType = null; // 'pf' | 'pj'
let currentStep     = 1;
const TOTAL_STEPS   = 4;

// ── Novos estados ─────────────────────────────────────────
let currentUserId   = null;  // null = modo anônimo
let isAnonymous     = false; // true se escolheu "continuar sem conta"
let autoSpeak       = false; // TTS automático
let micActive       = false; // microfone ligado
let recognition     = null;  // SpeechRecognition instance

// ============================================================
// MARKDOWN → HTML  (formatação das mensagens do bot)
// ============================================================
function parseMarkdown(text) {
    text = text.replace(/<br\s*\/?>/gi, '\n');
    text = text.replace(/<\/?(?!strong|em|b|i)[a-z][^>]*>/gi, '');
    text = text.replace(/```[\w]*\n?([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
    text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/__(.+?)__/g, '<strong>$1</strong>');
    text = text.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
    text = text.replace(/(?<!_)_(?!_)(.+?)(?<!_)_(?!_)/g, '<em>$1</em>');
    text = text.replace(/^[\s]*[-*]\s+(.+)$/gm, '<li>$1</li>');
    text = text.replace(/(<li>.*<\/li>(\n|$))+/g, match => `<ul>${match}</ul>`);
    text = text.replace(/^[\s]*\d+\.\s+(.+)$/gm, '<li>$1</li>');
    text = text.replace(/^#{3}\s+(.+)$/gm, '<strong class="md-h3">$1</strong>');
    text = text.replace(/^#{2}\s+(.+)$/gm, '<strong class="md-h2">$1</strong>');
    text = text.replace(/^#{1}\s+(.+)$/gm, '<strong class="md-h1">$1</strong>');
    text = text.replace(/^---+$/gm, '<hr class="md-hr">');
    text = text.replace(/\n{2,}/g, '</p><p>');
    text = text.replace(/\n/g, '<br>');
    text = `<p>${text}</p>`;
    text = text.replace(/<p><\/p>/g, '');
    text = text.replace(/<p>\s*<\/p>/g, '');
    return text;
}

// ============================================================
// NAVEGAÇÃO (original)
// ============================================================
function showPage(pageId) {
    document.querySelectorAll('.page').forEach(p => p.classList.add('hidden'));
    document.getElementById(pageId).classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Landing → Login (alterado: agora vai para loginPage)
document.getElementById('startBtn').addEventListener('click', () => {
    showPage('loginPage');
});

// ── Botão voltar da loginPage ──────────────────────────────
document.getElementById('backFromLogin').addEventListener('click', () => {
    showPage('landingPage');
});

// Profile → Chat (PF)
document.getElementById('choosePF').addEventListener('click', () => {
    userProfileType = 'pf';
    setBadge('Pessoa Física');
    showPage('chatPage');
    startConversation();
});

// Profile → Chat (PJ)
document.getElementById('choosePJ').addEventListener('click', () => {
    userProfileType = 'pj';
    setBadge('Pessoa Jurídica');
    showPage('chatPage');
    startConversation();
});

// Voltar da seleção de perfil
document.getElementById('backFromProfile').addEventListener('click', () => {
    showPage('loginPage');
});

// Voltar do chat
document.getElementById('backBtn').addEventListener('click', () => {
    resetSession();
    showPage('profilePage');
});

// Nova análise
document.getElementById('novaAnalise').addEventListener('click', () => {
    resetSession();
    showPage('profilePage');
});

// Início dos resultados
document.getElementById('backFromResults').addEventListener('click', () => {
    resetSession();
    showPage('landingPage');
});

// ============================================================
// ★ AUTH — LOGIN / REGISTRO / ANÔNIMO
// ============================================================

/** Alterna entre as abas Login e Registro */
function switchAuthTab(tab) {
    const loginForm    = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const tabLogin     = document.getElementById('tabLogin');
    const tabRegister  = document.getElementById('tabRegister');

    if (tab === 'login') {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
        tabLogin.classList.add('active');
        tabRegister.classList.remove('active');
        document.getElementById('loginError').textContent = '';
    } else {
        loginForm.classList.add('hidden');
        registerForm.classList.remove('hidden');
        tabLogin.classList.remove('active');
        tabRegister.classList.add('active');
        document.getElementById('registerError').textContent = '';
    }
}

/** Faz login via API */
async function handleLogin() {
    const email    = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value.trim();
    const errorEl  = document.getElementById('loginError');
    const btn      = document.getElementById('loginSubmitBtn');

    errorEl.textContent = '';

    if (!email || !password) {
        errorEl.textContent = 'Preencha email e senha.';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Entrando...';

    try {
        const res  = await fetch('/login', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ email, password })
        });
        const data = await res.json();

        if (!res.ok) {
            errorEl.textContent = data.error || 'Erro ao fazer login.';
        } else {
            currentUserId = data.user_id;
            isAnonymous   = false;
            _onAuthSuccess(email);
        }
    } catch (_) {
        errorEl.textContent = 'Erro de conexão com o servidor.';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Entrar';
    }
}

/** Cria uma conta via API */
async function handleRegister() {
    const email    = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPassword').value.trim();
    const errorEl  = document.getElementById('registerError');
    const btn      = document.getElementById('registerSubmitBtn');

    errorEl.textContent = '';

    if (!email || !password) {
        errorEl.textContent = 'Preencha email e senha.';
        return;
    }
    if (password.length < 6) {
        errorEl.textContent = 'Senha deve ter pelo menos 6 caracteres.';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Criando conta...';

    try {
        const res  = await fetch('/register', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ email, password })
        });
        const data = await res.json();

        if (!res.ok) {
            errorEl.textContent = data.error || 'Erro ao criar conta.';
        } else {
            currentUserId = data.user_id;
            isAnonymous   = false;
            _onAuthSuccess(email);
        }
    } catch (_) {
        errorEl.textContent = 'Erro de conexão com o servidor.';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Criar conta';
    }
}

/** Prossegue sem criar conta (modo anônimo) */
function continueAnonymous() {
    currentUserId = null;
    isAnonymous   = true;
    showPage('profilePage');
}

/** Callback após login/registro com sucesso */
function _onAuthSuccess(email) {
    // Persiste localmente para caso de refresh (simples, protótipo)
    sessionStorage.setItem('sb_user_id', currentUserId);
    sessionStorage.setItem('sb_email',   email);

    showPage('profilePage');
}

/** Tenta restaurar sessão de login da sessionStorage */
function _restoreSession() {
    const uid   = sessionStorage.getItem('sb_user_id');
    const email = sessionStorage.getItem('sb_email');
    if (uid) {
        currentUserId = uid;
        isAnonymous   = false;
        _updateUserPill(email);
    }
}

/** Atualiza ou remove o user pill na topbar */
function _updateUserPill(email) {
    // Remove pill existente se houver
    const existingPill = document.querySelector('.user-pill');
    if (existingPill) existingPill.remove();

    if (!email) return;

    const badge = document.getElementById('chatProfileBadge');
    if (!badge) return;

    const pill = document.createElement('span');
    pill.className = 'user-pill';
    pill.textContent = '👤 ' + email.split('@')[0];
    pill.title = email;
    badge.parentNode.insertBefore(pill, badge.nextSibling);
}

// ============================================================
// TEMA  (original)
// ============================================================

// ============================================================
// BADGE DE PERFIL (original)
// ============================================================
function setBadge(text) {
    const el = document.getElementById('chatProfileBadge');
    if (el) el.textContent = text;
}

// ============================================================
// PROGRESSO DE ETAPAS (original)
// ============================================================
function setStep(step) {
    currentStep = step;
    const steps = document.querySelectorAll('.ps-step');
    steps.forEach(el => {
        const n = parseInt(el.dataset.step);
        el.classList.remove('active', 'done');
        if (n === step) el.classList.add('active');
        else if (n < step) el.classList.add('done');
    });
}

// ============================================================
// INICIAR CONVERSA (original)
// ============================================================
async function startConversation() {
    document.getElementById('chatBox').innerHTML = '';
    currentStep = 1;
    setStep(1);
    sessionId = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString();

    // Atualiza user pill com email da sessão
    const email = sessionStorage.getItem('sb_email');
    if (currentUserId && email) _updateUserPill(email);

    // Carrega histórico no sidebar se logado
    if (currentUserId) loadHistory();

    const initMsg = userProfileType === 'pj'
        ? 'Olá! Quero fazer uma análise de crédito para minha empresa.'
        : 'Olá! Quero fazer uma análise de crédito pessoal.';

    await sendToBot(initMsg, false);
}

// ============================================================
// ENVIAR MENSAGEM (original)
// ============================================================
async function sendMessage() {
    const input = document.getElementById('userInput');
    const text  = input.value.trim();
    if (!text) return;
    input.value = '';
    addMessage(text, 'user');
    await sendToBot(text, false);
}

function handleKeyPress(e) {
    if (e.key === 'Enter') sendMessage();
}

// ============================================================
// SCROLL DO CHAT (original)
// ============================================================
function scrollChatToBottom(smooth = true) {
    const chatBox = document.getElementById('chatBox');
    if (!chatBox) return;
    chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: smooth ? 'smooth' : 'instant' });
    setTimeout(() => { chatBox.scrollTop = chatBox.scrollHeight; }, 80);
}

// ============================================================
// COMUNICAÇÃO COM O BACKEND (atualizado para incluir user_id)
// ============================================================
async function sendToBot(userText, showUserMsg = true) {
    if (showUserMsg) addMessage(userText, 'user');

    setInputEnabled(false);
    const typingId = showTyping();

    try {
        const res = await fetch('/chat', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                message:      userText,
                session_id:   sessionId,
                profile_type: userProfileType,
                user_id:      currentUserId || null   // ★ NOVO: envia user_id se logado
            })
        });

        const data = await res.json();
        removeTyping(typingId);

        if (data.error) {
            addMessage(data.error, 'bot');
            setInputEnabled(true);
            return;
        }

        if (data.session_id) sessionId = data.session_id;

        addMessage(data.response, 'bot');
        advanceStep(data.response);

        // TTS automático (se ativado)
        if (autoSpeak) speakText(data.response);

        if (data.result_data) {
            setStep(4);
            setTimeout(() => {
                renderResults(data.result_data);
                showPage('resultsPage');
            }, 1400);
        } else {
            setInputEnabled(true);
        }

    } catch (err) {
        removeTyping(typingId);
        addMessage('Erro de conexão. Verifique se o servidor Flask está rodando em localhost:5000', 'bot');
        setInputEnabled(true);
    }
}

// Heurística para avançar etapas (original)
function advanceStep(botReply) {
    const r = botReply.toLowerCase();
    if (currentStep === 1 && (r.includes('renda') || r.includes('faturamento') || r.includes('salário'))) {
        setStep(2);
    } else if (currentStep === 2 && (r.includes('dívida') || r.includes('histórico') || r.includes('atraso') || r.includes('negativad'))) {
        setStep(3);
    } else if (currentStep === 3 && (r.includes('objetivo') || r.includes('finalidade') || r.includes('quer'))) {
        setStep(4);
    }
}

// ============================================================
// HELPERS DE CHAT (addMessage atualizado com botão TTS)
// ============================================================
function addMessage(text, role) {
    const chatBox = document.getElementById('chatBox');
    const div     = document.createElement('div');
    div.className = `message ${role}`;

    if (role === 'bot') {
        div.innerHTML = parseMarkdown(text);

        // ★ Botão de leitura por voz em cada mensagem do bot
        const speakBtn = document.createElement('button');
        speakBtn.className = 'speak-btn';
        speakBtn.title     = 'Ouvir esta mensagem';
        speakBtn.innerHTML = '🔊 ouvir';
        speakBtn.onclick   = () => speakText(text);
        div.appendChild(speakBtn);
    } else {
        div.textContent = text;
    }

    chatBox.appendChild(div);
    scrollChatToBottom();
}

function showTyping() {
    const chatBox = document.getElementById('chatBox');
    const id  = 'typing-' + Date.now();
    const div = document.createElement('div');
    div.className = 'message bot typing-indicator';
    div.id = id;
    div.innerHTML = '<span></span><span></span><span></span>';
    chatBox.appendChild(div);
    scrollChatToBottom();
    return id;
}

function removeTyping(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

function setInputEnabled(enabled) {
    const input = document.getElementById('userInput');
    const btn   = document.getElementById('sendButton');
    input.disabled = !enabled;
    btn.disabled   = !enabled;
    if (enabled) input.focus();
}

// ============================================================
// ★ VOZ — SPEECH-TO-TEXT (STT)
// ============================================================

/** Inicializa o SpeechRecognition da Web API */
function initSpeechRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        console.warn('SpeechRecognition não suportado neste navegador.');
        const micBtn = document.getElementById('micBtn');
        if (micBtn) {
            micBtn.title   = 'Reconhecimento de voz não suportado neste navegador';
            micBtn.style.opacity = '0.4';
            micBtn.style.cursor  = 'not-allowed';
            micBtn.onclick = () => null;
        }
        return;
    }

    recognition = new SpeechRecognition();
    recognition.lang        = 'pt-BR';
    recognition.continuous  = false;
    recognition.interimResults = false;

    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        const input      = document.getElementById('userInput');
        if (input) {
            input.value = transcript;
            input.focus();
        }
        _setMicState(false);
    };

    recognition.onerror = (event) => {
        console.error('Erro no microfone:', event.error);
        _setMicState(false);
    };

    recognition.onend = () => {
        _setMicState(false);
    };
}

/** Liga/desliga o microfone */
function toggleMic() {
    if (!recognition) {
        initSpeechRecognition();
        if (!recognition) return;
    }

    if (micActive) {
        recognition.stop();
        _setMicState(false);
    } else {
        try {
            recognition.start();
            _setMicState(true);
        } catch (e) {
            console.error('Erro ao iniciar microfone:', e);
        }
    }
}

function _setMicState(active) {
    micActive = active;
    const btn = document.getElementById('micBtn');
    if (!btn) return;
    btn.classList.toggle('listening', active);
    btn.title = active ? 'Clique para parar' : 'Falar por voz';
}

// ============================================================
// ★ VOZ — TEXT-TO-SPEECH (TTS)
// ============================================================

/** Faz o browser ler o texto em voz alta */
function speakText(text) {
    if (!window.speechSynthesis) return;

    // Cancela fala em andamento
    window.speechSynthesis.cancel();

    // Remove markdown e HTML antes de ler
    const plain = text
        .replace(/\*\*(.+?)\*\*/g, '$1')
        .replace(/\*(.+?)\*/g, '$1')
        .replace(/<[^>]+>/g, '')
        .replace(/#{1,3}\s/g, '')
        .replace(/\[RESULTADO_JSON\][\s\S]*\[\/RESULTADO_JSON\]/g, '')
        .trim();

    const utterance = new SpeechSynthesisUtterance(plain);
    utterance.lang   = 'pt-BR';
    utterance.rate   = 1.0;
    utterance.pitch  = 1.0;
    utterance.volume = 1.0;

    // Tenta usar voz em português se disponível
    const voices = window.speechSynthesis.getVoices();
    const ptVoice = voices.find(v => v.lang.startsWith('pt'));
    if (ptVoice) utterance.voice = ptVoice;

    window.speechSynthesis.speak(utterance);
}

/** Liga/desliga TTS automático para novas mensagens do bot */
function toggleAutoSpeak() {
    autoSpeak = !autoSpeak;
    const btn  = document.getElementById('ttsToggleBtn');
    const x1   = document.getElementById('ttsX1');
    const x2   = document.getElementById('ttsX2');

    if (btn)  btn.classList.toggle('active', autoSpeak);
    if (autoSpeak) {
        // Mostra ícone de som ativo (sem o X)
        if (x1) x1.setAttribute('d', 'M15.54 8.46a5 5 0 0 1 0 7.07');
        if (x2) x2.setAttribute('d', 'M19.07 4.93a10 10 0 0 1 0 14.14');
        if (x1) x1.removeAttribute('x1');
        if (x2) x2.removeAttribute('x1');
        btn.title = 'Desativar leitura automática';
    } else {
        // Mostra ícone mudo (com X)
        if (x1) { x1.setAttribute('x1','23'); x1.setAttribute('y1','9'); x1.setAttribute('x2','17'); x1.setAttribute('y2','15'); }
        if (x2) { x2.setAttribute('x1','17'); x2.setAttribute('y1','9'); x2.setAttribute('x2','23'); x2.setAttribute('y2','15'); }
        btn.title = 'Ativar leitura automática';
        window.speechSynthesis && window.speechSynthesis.cancel();
    }
}

// ============================================================
// ★ HISTÓRICO DE CHATS
// ============================================================

/** Abre/fecha o sidebar de histórico */
function toggleSidebar() {
    const sidebar = document.getElementById('historySidebar');
    const overlay = document.getElementById('historyOverlay');

    const isOpen = sidebar.classList.toggle('open');
    overlay.classList.toggle('hidden', !isOpen);

    if (isOpen && currentUserId) {
        loadHistory();
    } else if (isOpen && !currentUserId) {
        document.getElementById('historyList').innerHTML =
            '<p class="hs-login-note">Faça login para salvar e ver o histórico de análises.</p>';
    }
}

/** Busca histórico da API e renderiza no sidebar */
async function loadHistory() {
    if (!currentUserId) return;

    const list = document.getElementById('historyList');
    list.innerHTML = '<p class="hs-login-note">Carregando...</p>';

    try {
        const res  = await fetch(`/history?user_id=${currentUserId}`);
        const data = await res.json();
        renderHistory(data.chats || []);
    } catch (_) {
        list.innerHTML = '<p class="hs-login-note">Erro ao carregar histórico.</p>';
    }
}

/** Renderiza a lista de chats no sidebar */
function renderHistory(chats) {
    const list = document.getElementById('historyList');

    if (!chats.length) {
        list.innerHTML = '<p class="hs-login-note">Nenhuma análise salva ainda.<br>Faça uma análise para vê-la aqui.</p>';
        return;
    }

    list.innerHTML = '';
    chats.forEach(chat => {
        const item = document.createElement('div');
        item.className = 'hs-item';

        const date    = new Date(chat.created_at * 1000);
        const dateStr = date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' });
        const type    = chat.profile_type === 'pj' ? '🏢 Empresa' : '👤 Pessoal';

        item.innerHTML = `
            <div class="hs-item-title">${type}</div>
            <div class="hs-item-meta">${dateStr} · ${chat.message_count} mensagens</div>
        `;
        list.appendChild(item);
    });
}

// ============================================================
// ★ MEMÓRIA PERSONALIZADA
// ============================================================

/** Abre o modal de salvar memória */
function openMemoryModal() {
    const modal   = document.getElementById('memoryModal');
    const errorEl = document.getElementById('memError');
    document.getElementById('memKey').value   = '';
    document.getElementById('memValue').value = '';
    if (errorEl) errorEl.textContent = '';
    modal.classList.remove('hidden');
    setTimeout(() => document.getElementById('memKey').focus(), 100);
}

/** Fecha o modal de memória */
function closeMemoryModal() {
    document.getElementById('memoryModal').classList.add('hidden');
}

/** Salva uma entrada de memória via API */
async function saveMemoryEntry() {
    const key     = document.getElementById('memKey').value.trim();
    const value   = document.getElementById('memValue').value.trim();
    const errorEl = document.getElementById('memError');

    if (errorEl) errorEl.textContent = '';

    if (!key || !value) {
        if (errorEl) errorEl.textContent = 'Preencha tipo e valor.';
        return;
    }

    if (!currentUserId) {
        if (errorEl) errorEl.textContent = 'Faça login para salvar na memória.';
        return;
    }

    try {
        const res  = await fetch('/memory', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ user_id: currentUserId, key, value })
        });
        const data = await res.json();

        if (res.ok) {
            closeMemoryModal();
            // Feedback visual — mensagem temporária no chat
            addMessage(`✅ Informação salva na memória: "${key}: ${value}". Será usada nas próximas análises!`, 'bot');
        } else {
            if (errorEl) errorEl.textContent = data.error || 'Erro ao salvar.';
        }
    } catch (_) {
        if (errorEl) errorEl.textContent = 'Erro de conexão.';
    }
}

// ============================================================
// RENDERIZAR RESULTADOS (original)
// ============================================================
function renderResults(d) {
    document.getElementById('res-nome').textContent    = d.nome    || 'Usuário';
    document.getElementById('res-score-num').textContent = d.score || '—';

    const tierEl = document.getElementById('res-tier');
    tierEl.textContent  = d.tier || '—';
    tierEl.style.color  = scoreTierColor(d.score);

    document.getElementById('res-renda').textContent   = d.renda   || '—';
    document.getElementById('res-perfil').textContent  = d.perfil  || '—';
    document.getElementById('res-objetivo').textContent = d.objetivo || '—';
    document.getElementById('res-limite').textContent  = d.limite  || '—';

    const tag = document.getElementById('resProfileTag');
    if (tag) tag.textContent = userProfileType === 'pj' ? 'PJ' : 'PF';

    animateScoreArc(d.score || 0);
    renderSugestoes(d.sugestoes || []);
}

function animateScoreArc(score) {
    const arc           = document.getElementById('score-arc-fill');
    const circumference = 2 * Math.PI * 54;
    const pct           = Math.min(Math.max(score / 1000, 0), 1);
    arc.style.stroke    = scoreTierColor(score);
    setTimeout(() => {
        arc.style.strokeDasharray = `${pct * circumference} ${circumference}`;
    }, 400);
}

function scoreTierColor(score) {
    if (score >= 800) return '#2ecc71';
    if (score >= 600) return '#f6e033';
    if (score >= 400) return '#e67e22';
    return '#e74c3c';
}

function renderSugestoes(sugestoes) {
    const grid = document.getElementById('sugestoes-grid');
    grid.innerHTML = '';
    sugestoes.forEach((s, i) => {
        const card = document.createElement('div');
        card.className = 'sug-card' + (s.destaque ? ' sug-destaque' : '');
        card.style.animationDelay = `${i * 0.12}s`;
        card.innerHTML = `
            <div class="sug-icon-wrap">${getSugIcon(s.icone)}</div>
            <div class="sug-body">
                <div class="sug-title">${s.titulo}</div>
                <div class="sug-desc">${s.descricao}</div>
            </div>
            ${s.destaque ? '<div class="sug-badge-dest">Destaque</div>' : ''}
        `;
        grid.appendChild(card);
    });
}

function getSugIcon(emoji) {
    const map = {
        '💳': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="3"/><line x1="2" y1="10" x2="22" y2="10"/></svg>`,
        '📈': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>`,
        '🏦': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`,
        '🏢': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="2" width="18" height="20" rx="1"/><line x1="9" y1="22" x2="9" y2="12"/><line x1="15" y1="22" x2="15" y2="12"/><rect x="9" y="12" width="6" height="10"/><line x1="8" y1="6" x2="8" y2="6.01"/><line x1="12" y1="6" x2="12" y2="6.01"/><line x1="16" y1="6" x2="16" y2="6.01"/></svg>`,
        '📊': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>`,
        '💰': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 0 0 0 4h4a2 2 0 0 1 0 4H8"/><line x1="12" y1="6" x2="12" y2="8"/><line x1="12" y1="16" x2="12" y2="18"/></svg>`,
        '💡': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 1 7 7c0 2.38-1.19 4.47-3 5.74V17a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 0 1 7-7z"/></svg>`,
    };
    return map[emoji] || map['💡'];
}

// ============================================================
// RESET (original + limpa estados de voz/auth)
// ============================================================
async function resetSession() {
    if (sessionId) {
        try {
            await fetch('/reset', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ session_id: sessionId })
            });
        } catch (_) {}
    }
    sessionId       = null;
    userProfileType = null;
    currentStep     = 1;
    document.getElementById('chatBox').innerHTML            = '';
    document.getElementById('chatProfileBadge').textContent = '';
    setStep(1);

    // Para TTS em andamento
    if (window.speechSynthesis) window.speechSynthesis.cancel();

    // Para microfone se ativo
    if (micActive && recognition) {
        recognition.stop();
        _setMicState(false);
    }

    // Fecha sidebar se aberto
    const sidebar = document.getElementById('historySidebar');
    const overlay = document.getElementById('historyOverlay');
    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.add('hidden');

    // Remove user pill
    document.querySelectorAll('.user-pill').forEach(el => el.remove());
}

// ============================================================
// LÓGICA DE TEMA (original)
// ============================================================
const themeCheckbox = document.getElementById('themeCheckbox');
if (themeCheckbox) {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    themeCheckbox.checked = (currentTheme === 'light');

    themeCheckbox.addEventListener('change', function() {
        const html = document.documentElement;
        if (this.checked) { html.setAttribute('data-theme', 'light'); }
        else              { html.setAttribute('data-theme', 'dark');  }
    });
}

// ============================================================
// ANIMAÇÕES DE SCROLL / LOADING / PARALLAX (original)
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
    // Restaura sessão de login
    _restoreSession();

    // Inicializa SpeechRecognition
    initSpeechRecognition();

    // ── ESCONDE O LOADING SCREEN ──
    const loadingScreen = document.getElementById('loadingScreen');
    if (loadingScreen) {
        setTimeout(() => { loadingScreen.classList.add('fade-out'); }, 1200);
    }

    // Intersection Observer para Fade In / Fade Out
    const revealElements = document.querySelectorAll('.reveal-item');
    const revealOptions  = { threshold: 0.15, rootMargin: "0px 0px -50px 0px" };

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                entry.target.classList.remove('hidden-scroll');
            } else {
                entry.target.classList.remove('visible');
                entry.target.classList.add('hidden-scroll');
            }
        });
    }, revealOptions);

    revealElements.forEach(el => revealObserver.observe(el));

    // Animação de Parallax via Scroll
    const parallaxElements = document.querySelectorAll('.parallax-item');
    window.addEventListener('scroll', () => {
        let scrollY = window.scrollY;
        parallaxElements.forEach(el => {
            let speed = el.getAttribute('data-speed') || 0.3;
            el.style.transform = `translateY(${scrollY * speed}px)`;
        });
    });
});