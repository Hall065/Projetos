/**
 * ScoreBot — script.js
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
// MARKDOWN → HTML  (formatação das mensagens do bot)
// ============================================================
function parseMarkdown(text) {
    // Remove tags HTML literais que o modelo às vezes injeta (<br>, <br/>, etc.)
    text = text.replace(/<br\s*\/?>/gi, '\n');
    // Remove outros tags HTML residuais mas preserva o conteúdo
    text = text.replace(/<\/?(?!strong|em|b|i)[a-z][^>]*>/gi, '');

    // Blocos de código (``` ... ```)
    text = text.replace(/```[\w]*\n?([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

    // Negrito: **texto** ou __texto__
    text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/__(.+?)__/g, '<strong>$1</strong>');

    // Itálico: *texto* ou _texto_ (não confundir com **)
    text = text.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
    text = text.replace(/(?<!_)_(?!_)(.+?)(?<!_)_(?!_)/g, '<em>$1</em>');

    // Listas não-ordenadas: linhas que começam com - ou *
    text = text.replace(/^[\s]*[-*]\s+(.+)$/gm, '<li>$1</li>');
    text = text.replace(/(<li>.*<\/li>(\n|$))+/g, match => `<ul>${match}</ul>`);

    // Listas ordenadas: 1. 2. 3.
    text = text.replace(/^[\s]*\d+\.\s+(.+)$/gm, '<li>$1</li>');

    // Cabeçalhos (### ## #) — converte para negrito + quebra
    text = text.replace(/^#{3}\s+(.+)$/gm, '<strong class="md-h3">$1</strong>');
    text = text.replace(/^#{2}\s+(.+)$/gm, '<strong class="md-h2">$1</strong>');
    text = text.replace(/^#{1}\s+(.+)$/gm, '<strong class="md-h1">$1</strong>');

    // Linha horizontal ---
    text = text.replace(/^---+$/gm, '<hr class="md-hr">');

    // Quebras de linha duplas → parágrafo
    text = text.replace(/\n{2,}/g, '</p><p>');

    // Quebra simples → <br> (dentro de parágrafos)
    text = text.replace(/\n/g, '<br>');

    // Envolve tudo em parágrafo
    text = `<p>${text}</p>`;

    // Limpa <p> vazios
    text = text.replace(/<p><\/p>/g, '');
    text = text.replace(/<p>\s*<\/p>/g, '');

    return text;
}

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

    const initMsg = userProfileType === 'pj'
        ? 'Olá! Quero fazer uma análise de crédito para minha empresa.'
        : 'Olá! Quero fazer uma análise de crédito pessoal.';

    await sendToBot(initMsg, false);
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
// SCROLL DO CHAT — acompanha novas mensagens
// ============================================================
function scrollChatToBottom(smooth = true) {
    const chatBox = document.getElementById('chatBox');
    if (!chatBox) return;
    chatBox.scrollTo({
        top: chatBox.scrollHeight,
        behavior: smooth ? 'smooth' : 'instant'
    });
    // Fallback para browsers sem suporte a scroll options
    setTimeout(() => { chatBox.scrollTop = chatBox.scrollHeight; }, 80);
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
            addMessage(data.error, 'bot');
            setInputEnabled(true);
            return;
        }

        if (data.session_id) sessionId = data.session_id;

        addMessage(data.response, 'bot');
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
        addMessage('Erro de conexão. Verifique se o servidor Flask está rodando em localhost:5000', 'bot');
        setInputEnabled(true);
    }
}

// Heurística para avançar etapas
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

    if (role === 'bot') {
        // Renderiza markdown como HTML
        div.innerHTML = parseMarkdown(text);
    } else {
        // Mensagem do usuário: texto plano, sem interpretar markdown
        div.textContent = text;
    }

    chatBox.appendChild(div);
    scrollChatToBottom();
}

function showTyping() {
    const chatBox = document.getElementById('chatBox');
    const id = 'typing-' + Date.now();
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

    const tag = document.getElementById('resProfileTag');
    if (tag) {
        tag.textContent = userProfileType === 'pj' ? 'PJ' : 'PF';
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

// Converte emojis comuns de sugestão em SVGs minimalistas
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