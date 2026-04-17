"""
ScoreBot — Backend Flask com suporte PF / PJ
Atualizado com: Login/Registro, Histórico, Memória, Voz (frontend), Criptografia
"""

from flask import Flask, request, jsonify, render_template, send_from_directory
from flask_cors import CORS
import requests
import json
import uuid
import os
import hashlib
import base64
import time

app = Flask(__name__, template_folder='templates', static_folder='static')
CORS(app)

# ============================================================
# CONFIGURAÇÕES
# ============================================================
OLLAMA_URL   = "http://localhost:11434/api/chat"
OLLAMA_MODEL = "deepseek-v3.1:671b-cloud"  # troque se preferir outro modelo

DATA_DIR = os.path.join(os.path.dirname(__file__), 'data')

conversation_memory = {}  # { session_id: { messages, profile_type, user_id, ... } }

# ============================================================
# HELPERS — JSON / CRIPTOGRAFIA
# ============================================================

def ensure_data_dir():
    """Cria /data/ e arquivos JSON se não existirem."""
    os.makedirs(DATA_DIR, exist_ok=True)
    defaults = {
        'users.json':  {'users': []},
        'chats.json':  {'chats': []},
        'memory.json': {'memory': []},
    }
    for fname, default in defaults.items():
        path = os.path.join(DATA_DIR, fname)
        if not os.path.exists(path):
            _write_json(path, default)


def load_json(filename: str) -> dict:
    """Carrega um arquivo JSON da pasta /data/."""
    path = os.path.join(DATA_DIR, filename)
    try:
        with open(path, 'r', encoding='utf-8') as f:
            return json.load(f)
    except (FileNotFoundError, json.JSONDecodeError):
        return {}


def save_json(filename: str, data: dict) -> None:
    """Salva dados em um arquivo JSON na pasta /data/."""
    path = os.path.join(DATA_DIR, filename)
    os.makedirs(DATA_DIR, exist_ok=True)
    _write_json(path, data)


def _write_json(path: str, data: dict) -> None:
    with open(path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)


# --- Criptografia básica (XOR + base64 — aceitável para protótipo) ---
_SECRET = b'ScoreBot2025SecretKeyPrototype!!'  # 32 bytes


def encrypt(text: str) -> str:
    """Ofusca campo sensível com XOR + base64."""
    if not text:
        return text
    data = text.encode('utf-8')
    key  = (_SECRET * (len(data) // len(_SECRET) + 1))[:len(data)]
    xored = bytes(a ^ b for a, b in zip(data, key))
    return base64.b64encode(xored).decode('utf-8')


def decrypt(token: str) -> str:
    """Reverte a ofuscação XOR + base64."""
    if not token:
        return token
    try:
        data = base64.b64decode(token.encode('utf-8'))
        key  = (_SECRET * (len(data) // len(_SECRET) + 1))[:len(data)]
        return bytes(a ^ b for a, b in zip(data, key)).decode('utf-8')
    except Exception:
        return token  # retorna original em caso de erro


def hash_password(password: str) -> str:
    """Hash SHA-256 da senha (com salt fixo do app — protótipo)."""
    salted = f"scorebot_salt_{password}_2025"
    return hashlib.sha256(salted.encode('utf-8')).hexdigest()


# ============================================================
# PROMPTS BASE (inalterados)
# ============================================================
SYSTEM_PROMPT_PF = """Você é o ScoreBot, assistente especialista em score de crédito e finanças pessoais para PESSOA FÍSICA.

Personalidade: amigável, direto, linguagem simples. Use emojis com moderação.

Fluxo obrigatório (etapa por etapa, 1 pergunta de cada vez):
1. Pergunte o nome completo
2. Pergunte a renda mensal líquida
3. Pergunte se possui dívidas em aberto ou negativações (sim/não e detalhes)
4. Pergunte há quanto tempo está sem atrasos em pagamentos
5. Pergunte o objetivo financeiro (ex: cartão, empréstimo, financiamento)

Após coletar TODOS os dados, gere o resultado no formato JSON abaixo (ao final da sua resposta):

[RESULTADO_JSON]{"nome":"...","score":750,"tier":"Bom","renda":"R$ X.XXX","perfil":"Assalariado","objetivo":"...","limite":"R$ X.XXX","sugestoes":[{"icone":"💳","titulo":"...","descricao":"...","destaque":false},{"icone":"📈","titulo":"...","descricao":"...","destaque":true},{"icone":"🏦","titulo":"...","descricao":"...","destaque":false}]}[/RESULTADO_JSON]

Regras:
- Calcule o score entre 300–950 com base nos dados fornecidos
- Limite de crédito = score / 1000 * renda * 1.5 (aproximado)
- As sugestões devem ser práticas e baseadas na situação real do usuário
- Responda SEMPRE em português brasileiro
- Esta é uma análise educacional simulada"""

SYSTEM_PROMPT_PJ = """Você é o ScoreBot, assistente especialista em crédito empresarial para PESSOA JURÍDICA.

Personalidade: profissional, objetivo, linguagem clara para empresários.

Fluxo obrigatório (etapa por etapa, 1 pergunta de cada vez):
1. Pergunte o nome da empresa e o segmento de atuação
2. Pergunte o faturamento mensal médio
3. Pergunte se a empresa possui restrições no CNPJ ou dívidas com fornecedores
4. Pergunte há quanto tempo a empresa está ativa e se possui conta PJ ativa
5. Pergunte a finalidade do crédito (capital de giro, expansão, equipamentos, etc.)

Após coletar TODOS os dados, gere o resultado no formato JSON abaixo (ao final da sua resposta):

[RESULTADO_JSON]{"nome":"...","score":720,"tier":"Bom","renda":"R$ XX.XXX/mês","perfil":"Empresa - [segmento]","objetivo":"...","limite":"R$ XX.XXX","sugestoes":[{"icone":"🏢","titulo":"...","descricao":"...","destaque":false},{"icone":"📊","titulo":"...","descricao":"...","destaque":true},{"icone":"💰","titulo":"...","descricao":"...","destaque":false}]}[/RESULTADO_JSON]

Regras:
- Score entre 300–950 baseado em saúde financeira da empresa
- Para PJ, o limite pode ser até 3x o faturamento mensal (conforme score)
- Sugestões devem ser orientadas ao crescimento empresarial
- Responda SEMPRE em português brasileiro
- Esta é uma análise educacional simulada"""


def get_system_prompt(profile_type: str) -> str:
    return SYSTEM_PROMPT_PJ if profile_type == 'pj' else SYSTEM_PROMPT_PF


def build_system_prompt(profile_type: str, user_id: str | None = None) -> str:
    """
    Retorna o system prompt base + memória personalizada do usuário (se existir).
    Isso dá ao modelo contexto extra sobre o usuário entre sessões.
    """
    base = get_system_prompt(profile_type)

    if not user_id:
        return base  # modo anônimo: sem memória

    mem_data = load_json('memory.json')
    user_memories = [m for m in mem_data.get('memory', []) if m.get('user_id') == user_id]

    if not user_memories:
        return base

    mem_lines = '\n'.join(
        f"- {m['key']}: {decrypt(m['value'])}"
        for m in user_memories
    )
    return base + f"\n\n## Memória personalizada do usuário (use para personalizar respostas):\n{mem_lines}"


# ============================================================
# ROTAS — AUTH
# ============================================================

@app.route('/register', methods=['POST'])
def register():
    data     = request.get_json() or {}
    email    = (data.get('email') or '').strip().lower()
    password = (data.get('password') or '').strip()

    if not email or not password:
        return jsonify({'error': 'Email e senha são obrigatórios'}), 400

    if len(password) < 6:
        return jsonify({'error': 'Senha deve ter pelo menos 6 caracteres'}), 400

    users_data = load_json('users.json')
    users = users_data.get('users', [])

    # Verifica se e-mail já existe (descriptografa para comparar)
    for u in users:
        if decrypt(u.get('email', '')) == email:
            return jsonify({'error': 'Email já cadastrado. Faça login.'}), 409

    new_user = {
        'id':         str(uuid.uuid4()),
        'email':      encrypt(email),       # campo criptografado
        'password':   hash_password(password),
        'created_at': int(time.time())
    }
    users.append(new_user)
    save_json('users.json', {'users': users})

    return jsonify({
        'user_id': new_user['id'],
        'message': 'Conta criada com sucesso! 🎉'
    })


@app.route('/login', methods=['POST'])
def login():
    data     = request.get_json() or {}
    email    = (data.get('email') or '').strip().lower()
    password = (data.get('password') or '').strip()

    if not email or not password:
        return jsonify({'error': 'Email e senha são obrigatórios'}), 400

    users_data = load_json('users.json')
    for u in users_data.get('users', []):
        if decrypt(u.get('email', '')) == email and u['password'] == hash_password(password):
            return jsonify({
                'user_id': u['id'],
                'message': 'Login realizado! Bem-vindo de volta 👋'
            })

    return jsonify({'error': 'Email ou senha incorretos'}), 401


# ============================================================
# ROTAS — HISTÓRICO DE CHATS
# ============================================================

@app.route('/history', methods=['GET'])
def get_history():
    user_id = request.args.get('user_id')

    if not user_id:
        return jsonify({'chats': []})

    chats_data = load_json('chats.json')
    user_chats = [c for c in chats_data.get('chats', []) if c.get('user_id') == user_id]

    # Retorna apenas resumos (sem mensagens completas) — mais leve para o frontend
    summaries = []
    for c in reversed(user_chats[-30:]):  # últimos 30, mais recentes primeiro
        summaries.append({
            'chat_id':       c['chat_id'],
            'created_at':    c.get('created_at', 0),
            'profile_type':  c.get('profile_type', 'pf'),
            'message_count': len(c.get('messages', []))
        })

    return jsonify({'chats': summaries})


# ============================================================
# ROTAS — MEMÓRIA PERSONALIZADA
# ============================================================

@app.route('/memory', methods=['POST'])
def save_memory():
    data    = request.get_json() or {}
    user_id = (data.get('user_id') or '').strip()
    key     = (data.get('key') or '').strip()
    value   = (data.get('value') or '').strip()

    if not user_id or not key or not value:
        return jsonify({'error': 'user_id, key e value são obrigatórios'}), 400

    mem_data = load_json('memory.json')
    memories = mem_data.get('memory', [])

    # Atualiza se a chave já existe para este usuário
    for m in memories:
        if m.get('user_id') == user_id and m.get('key') == key:
            m['value'] = encrypt(value)   # valor criptografado
            save_json('memory.json', {'memory': memories})
            return jsonify({'status': 'updated', 'key': key})

    # Caso contrário, adiciona novo registro
    memories.append({
        'user_id': user_id,
        'key':     key,
        'value':   encrypt(value)          # valor criptografado
    })
    save_json('memory.json', {'memory': memories})
    return jsonify({'status': 'saved', 'key': key})


@app.route('/memory', methods=['GET'])
def get_memory():
    """Retorna as chaves de memória do usuário (valores descriptografados)."""
    user_id = request.args.get('user_id')
    if not user_id:
        return jsonify({'memory': []})

    mem_data = load_json('memory.json')
    user_mem = [
        {'key': m['key'], 'value': decrypt(m['value'])}
        for m in mem_data.get('memory', [])
        if m.get('user_id') == user_id
    ]
    return jsonify({'memory': user_mem})


# ============================================================
# ROTA — CHAT (atualizada com user_id + histórico + memória)
# ============================================================

@app.route('/chat', methods=['POST'])
def chat():
    data = request.get_json()
    if not data or 'message' not in data:
        return jsonify({'error': 'Mensagem não fornecida'}), 400

    user_message  = data['message'].strip()
    session_id    = data.get('session_id') or str(uuid.uuid4())
    profile_type  = data.get('profile_type', 'pf')
    user_id       = data.get('user_id')   # NOVO: opcional; None = modo anônimo

    # Inicializa sessão em memória se necessário
    if session_id not in conversation_memory:
        conversation_memory[session_id] = {
            'messages':     [],
            'profile_type': profile_type,
            'user_id':      user_id,
            'chat_id':      str(uuid.uuid4()),
            'created_at':   int(time.time())
        }

    session = conversation_memory[session_id]

    # Se o user_id foi fornecido agora mas não antes, associa
    if user_id and not session.get('user_id'):
        session['user_id'] = user_id

    session['messages'].append({"role": "user", "content": user_message})

    # Monta payload para o Ollama com memória embutida no system prompt
    payload = {
        "model":    OLLAMA_MODEL,
        "messages": [
            {"role": "system", "content": build_system_prompt(
                session['profile_type'],
                session.get('user_id')
            )},
            *session['messages']
        ],
        "stream": False
    }

    try:
        response = requests.post(OLLAMA_URL, json=payload, timeout=90)
        response.raise_for_status()
        bot_reply = response.json()['message']['content']
    except requests.exceptions.ConnectionError:
        return jsonify({
            'error': 'Ollama não está rodando. O ícone de lhama deve aparecer na bandeja do sistema.',
            'session_id': session_id
        }), 503
    except Exception as e:
        return jsonify({'error': f'Erro ao chamar IA: {str(e)}', 'session_id': session_id}), 500

    session['messages'].append({"role": "assistant", "content": bot_reply})

    # Limita histórico em RAM a 20 mensagens
    if len(session['messages']) > 20:
        session['messages'] = session['messages'][-20:]

    # Persiste no JSON (só se usuário estiver logado)
    _save_chat_history(session)

    # Extrai JSON de resultado, se presente
    result_data = None
    if '[RESULTADO_JSON]' in bot_reply:
        try:
            start = bot_reply.index('[RESULTADO_JSON]') + len('[RESULTADO_JSON]')
            end   = bot_reply.index('[/RESULTADO_JSON]')
            result_data = json.loads(bot_reply[start:end].strip())
            bot_reply   = bot_reply[:bot_reply.index('[RESULTADO_JSON]')].strip()
            if not bot_reply:
                bot_reply = "✅ Análise concluída! Confira seus resultados."
        except (ValueError, json.JSONDecodeError):
            pass

    return jsonify({
        'response':    bot_reply,
        'session_id':  session_id,
        'result_data': result_data
    })


def _save_chat_history(session: dict) -> None:
    """
    Persiste o histórico da sessão no chats.json.
    Modo anônimo (user_id = None) não persiste — sem conta, sem histórico.
    """
    user_id = session.get('user_id')
    if not user_id:
        return  # anônimo: não salva

    chats_data = load_json('chats.json')
    chats      = chats_data.get('chats', [])
    chat_id    = session['chat_id']

    # Atualiza registro existente
    for c in chats:
        if c['chat_id'] == chat_id:
            c['messages'] = session['messages']
            save_json('chats.json', {'chats': chats})
            return

    # Cria novo registro
    chats.append({
        'chat_id':      chat_id,
        'user_id':      user_id,
        'profile_type': session.get('profile_type', 'pf'),
        'messages':     session['messages'],
        'created_at':   session.get('created_at', int(time.time()))
    })
    save_json('chats.json', {'chats': chats})


# ============================================================
# ROTAS BASE (inalteradas)
# ============================================================

@app.route('/')
def index():
    return render_template('index.html')


@app.route('/img/<path:filename>')
def serve_img(filename):
    return send_from_directory(os.path.join(app.root_path, 'img'), filename)


@app.route('/reset', methods=['POST'])
def reset_session():
    data = request.get_json() or {}
    sid = data.get('session_id')
    if sid and sid in conversation_memory:
        del conversation_memory[sid]
    return jsonify({'status': 'ok'})


# ============================================================
# INICIALIZAÇÃO
# ============================================================
if __name__ == '__main__':
    ensure_data_dir()
    print("=" * 50)
    print("  ScoreBot rodando em http://localhost:5000")
    print(f"  Modelo: {OLLAMA_MODEL}")
    print("  Dados em: ./data/")
    print("=" * 50)
    app.run(debug=True, port=5000)