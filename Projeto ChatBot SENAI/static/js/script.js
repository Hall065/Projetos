/**
 * ScoreBot — script.js melhorado
 * Fluxo com seleção PF/PJ, etapas de conversa e integração Flask
 */

// ============================================================
// ESTADO GLOBAL
// ============================================================
let sessionId = null;
let userProfileType = null; // 'pf' | 'pj'
let currentStep = 1;
const TOTAL_STEPS = 4;

// ============================================================
// NAVEGAÇÃO
// ============================================================
function showPage(pageId) {
    document.querySelectorAll('.page').forEach(p => p.classList.add('hidden'));
    document.getElementById(pageId).classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Landing → Profile Selection
document.getElementById('startBtn').addEventListener('click', () => {
    showPage('profilePage');
});

// Profile → Chat (PF)
document.getElementById('choosePF').addEventListener('click', () => {
    userProfileType = 'pf';
    setBadge('👤 Pessoa Física');
    showPage('chatPage');
    startConversation();
});

// Profile → Chat (PJ)
document.getElementById('choosePJ').addEventListener('click', () => {
    userProfileType = 'pj';
    setBadge('🏢 Pessoa Jurídica');
    showPage('chatPage');
    startConversation();
});

// Voltar da seleção de perfil
document.getElementById('backFromProfile').addEventListener('click', () => {
    showPage('landingPage');
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
// TEMA
// ============================================================
document.getElementById('themeToggle').addEventListener('click', () => {
    const html = document.documentElement;
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
});

// ============================================================
// BADGE DE PERFIL
// ============================================================
function setBadge(text) {
    const el = document.getElementById('chatProfileBadge');
    if (el) el.textContent = text;
}

// ============================================================
// PROGRESSO DE ETAPAS
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
// INICIAR CONVERSA
// ============================================================
async function startConversation() {
    document.getElementById('chatBox').innerHTML = '';
    currentStep = 1;
    setStep(1);
    sessionId = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString();

    // Mensagem inicial — diferente por tipo de perfil
    const initMsg = userProfileType === 'pj'
        ? 'Olá! Quero fazer uma análise de crédito para minha empresa.'
        : 'Olá! Quero fazer uma análise de crédito pessoal.';

    await sendToBot(initMsg, false); // false = não exibe a msg do usuário
}

// ============================================================
// ENVIAR MENSAGEM
// ============================================================
async function sendMessage() {
    const input = document.getElementById('userInput');
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    addMessage(text, 'user');
    await sendToBot(text, false);
}

function handleKeyPress(e) {
    if (e.key === 'Enter') sendMessage();
}

// ============================================================
// COMUNICAÇÃO COM O BACKEND
// ============================================================
async function sendToBot(userText, showUserMsg = true) {
    if (showUserMsg) addMessage(userText, 'user');

    setInputEnabled(false);
    const typingId = showTyping();

    try {
        const res = await fetch('/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: userText,
                session_id: sessionId,
                profile_type: userProfileType
            })
        });

        const data = await res.json();
        removeTyping(typingId);

        if (data.error) {
            addMessage(`⚠️ ${data.error}`, 'bot');
            setInputEnabled(true);
            return;
        }

        if (data.session_id) sessionId = data.session_id;

        addMessage(data.response, 'bot');

        // Avança etapa baseado em palavras-chave da resposta
        advanceStep(data.response);

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
        addMessage('⚠️ Erro de conexão. Verifique se o servidor Flask está rodando em localhost:5000', 'bot');
        setInputEnabled(true);
    }
}

// Heurística simples para avançar etapas visualmente
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
// HELPERS DE CHAT
// ============================================================
function addMessage(text, role) {
    const chatBox = document.getElementById('chatBox');
    const div = document.createElement('div');
    div.className = `message ${role}`;
    div.textContent = text;
    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function showTyping() {
    const chatBox = document.getElementById('chatBox');
    const id = 'typing-' + Date.now();
    const div = document.createElement('div');
    div.className = 'message bot typing-indicator';
    div.id = id;
    div.innerHTML = '<span></span><span></span><span></span>';
    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;
    return id;
}

function removeTyping(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

function setInputEnabled(enabled) {
    const input = document.getElementById('userInput');
    const btn = document.getElementById('sendButton');
    input.disabled = !enabled;
    btn.disabled = !enabled;
    if (enabled) input.focus();
}

// ============================================================
// RENDERIZAR RESULTADOS
// ============================================================
function renderResults(d) {
    document.getElementById('res-nome').textContent = d.nome || 'Usuário';
    document.getElementById('res-score-num').textContent = d.score || '—';

    const tierEl = document.getElementById('res-tier');
    tierEl.textContent = d.tier || '—';
    tierEl.style.color = scoreTierColor(d.score);

    document.getElementById('res-renda').textContent = d.renda || '—';
    document.getElementById('res-perfil').textContent = d.perfil || '—';
    document.getElementById('res-objetivo').textContent = d.objetivo || '—';
    document.getElementById('res-limite').textContent = d.limite || '—';

    // Tag PF/PJ nos resultados
    const tag = document.getElementById('resProfileTag');
    if (tag) {
        tag.textContent = userProfileType === 'pj' ? '🏢 PJ' : '👤 PF';
    }

    animateScoreArc(d.score || 0);
    renderSugestoes(d.sugestoes || []);
}

function animateScoreArc(score) {
    const arc = document.getElementById('score-arc-fill');
    const circumference = 2 * Math.PI * 54;
    const pct = Math.min(Math.max(score / 1000, 0), 1);
    arc.style.stroke = scoreTierColor(score);
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
            <div class="sug-icon">${s.icone || '💡'}</div>
            <div class="sug-body">
                <div class="sug-title">${s.titulo}</div>
                <div class="sug-desc">${s.descricao}</div>
            </div>
            ${s.destaque ? '<div class="sug-badge-dest">★ Destaque</div>' : ''}
        `;
        grid.appendChild(card);
    });
}

// ============================================================
// RESET
// ============================================================
async function resetSession() {
    if (sessionId) {
        try {
            await fetch('/reset', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session_id: sessionId })
            });
        } catch (_) {}
    }
    sessionId = null;
    userProfileType = null;
    currentStep = 1;
    document.getElementById('chatBox').innerHTML = '';
    document.getElementById('chatProfileBadge').textContent = '';
    setStep(1);
}
