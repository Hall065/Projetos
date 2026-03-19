# ScoreBot — Guia de Instalação e Execução

## Pré-requisitos

- Python 3.9+
- [Ollama](https://ollama.com) instalado na sua máquina

---

## 1. Instalar o Ollama

### macOS / Linux
```bash
curl -fsSL https://ollama.com/install.sh | sh
```

### Windows
Baixe o instalador em: https://ollama.com/download

---

## 2. Baixar o modelo de IA

```bash
# Recomendado (rápido e eficiente):
ollama pull mistral

# Alternativas (mais pesados, mas melhores):
ollama pull llama3
ollama pull gemma2
```

---

## 3. Configurar o projeto

```bash
# Clone / copie a pasta do projeto e entre nela:
cd scorebot

# Crie um ambiente virtual (opcional, mas recomendado):
python -m venv venv
source venv/bin/activate      # Linux/macOS
venv\Scripts\activate         # Windows

# Instale as dependências:
pip install -r requirements.txt
```

---

## 4. Rodar o ScoreBot

Abra **dois terminais**:

**Terminal 1 — Iniciar o Ollama:**
```bash
ollama serve
```

**Terminal 2 — Iniciar o Flask:**
```bash
python app.py
```

Acesse no navegador: **http://localhost:5000**

---

## Estrutura do Projeto

```
scorebot/
├── app.py                  ← Backend Flask (API + memória de conversa)
├── requirements.txt        ← Dependências Python
├── README.md               ← Este arquivo
│
├── templates/
│   └── index.html          ← Frontend (HTML original, sem alterações visuais)
│
├── static/
│   ├── css/
│   │   └── style.css       ← CSS original, sem alterações
│   └── js/
│       └── script.js       ← Lógica JS de integração com o backend
│
└── img/
    └── mascote.png         ← Imagem do mascote ScoreBot
```

---

## Como funciona

1. **Usuário** abre o navegador → vê a landing page
2. Clica em **"Iniciar Análise"** → vai para o chat
3. O ScoreBot faz perguntas (nome, renda, dívidas, objetivo)
4. O JS envia cada mensagem via `POST /chat` para o Flask
5. O Flask repassa para o **Ollama** com todo o histórico da conversa
6. O Ollama responde como especialista em crédito
7. Quando coletado o suficiente, a IA gera um `[RESULTADO_JSON]` com score, limite e sugestões
8. O JS detecta o JSON, preenche a tela de **Resultados** e navega automaticamente

---

## Trocar o modelo de IA

No arquivo `app.py`, linha 16:
```python
OLLAMA_MODEL = "mistral"   # troque por: llama3, gemma2, phi3, etc.
```

---

## Solução de Problemas

| Problema | Solução |
|---|---|
| `Connection refused` no chat | Execute `ollama serve` no terminal |
| Resposta muito lenta | Use um modelo menor: `ollama pull phi3` |
| Modelo não encontrado | Execute `ollama pull mistral` antes de rodar |
| Porta 5000 ocupada | `python app.py` → edite `port=5001` no final do app.py |
