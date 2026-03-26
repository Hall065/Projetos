"""
ScoreBot — Backend Flask com suporte PF / PJ
"""

from flask import Flask, request, jsonify, render_template, send_from_directory
from flask_cors import CORS
import requests
import json
import uuid
import os

app = Flask(__name__, template_folder='templates', static_folder='static')
CORS(app)

# ============================================================
# CONFIGURAÇÕES
# ============================================================
OLLAMA_URL   = "http://localhost:11434/api/chat"
OLLAMA_MODEL = "deepseek-v3.1:671b-cloud"  # troque se preferir outro modelo

conversation_memory = {}  # { session_id: [{ role, content }, ...] }

# ============================================================
# PROMPTS BASE
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

def get_system_prompt(profile_type):
    return SYSTEM_PROMPT_PJ if profile_type == 'pj' else SYSTEM_PROMPT_PF

# ============================================================
# ROTAS
# ============================================================

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/img/<path:filename>')
def serve_img(filename):
    return send_from_directory(os.path.join(app.root_path, 'img'), filename)

@app.route('/chat', methods=['POST'])
def chat():
    data = request.get_json()
    if not data or 'message' not in data:
        return jsonify({'error': 'Mensagem não fornecida'}), 400

    user_message  = data['message'].strip()
    session_id    = data.get('session_id') or str(uuid.uuid4())
    profile_type  = data.get('profile_type', 'pf')  # 'pf' ou 'pj'

    if session_id not in conversation_memory:
        conversation_memory[session_id] = {
            'messages': [],
            'profile_type': profile_type
        }

    session = conversation_memory[session_id]
    session['messages'].append({"role": "user", "content": user_message})

    payload = {
        "model": OLLAMA_MODEL,
        "messages": [
            {"role": "system", "content": get_system_prompt(session['profile_type'])},
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

    # Limita histórico a 20 mensagens
    if len(session['messages']) > 20:
        session['messages'] = session['messages'][-20:]

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
        'response': bot_reply,
        'session_id': session_id,
        'result_data': result_data
    })


@app.route('/reset', methods=['POST'])
def reset_session():
    data = request.get_json()
    sid = data.get('session_id')
    if sid and sid in conversation_memory:
        del conversation_memory[sid]
    return jsonify({'status': 'ok'})


if __name__ == '__main__':
    print("=" * 50)
    print("  ScoreBot rodando em http://localhost:5000")
    print(f"  Modelo: {OLLAMA_MODEL}")
    print("=" * 50)
    app.run(debug=True, port=5000)
