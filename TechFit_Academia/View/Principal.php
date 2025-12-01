<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechFit - Bem-vindo</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="Assets/Css/Principal.css">
</head>
<body>
  <!-- Navbar -->
  <header class="navbar">
    <h1 class="logo"><i class="fas fa-dumbbell"></i>TechFit</h1>
    <nav class="nav-links">
      <a href="#servicos">Serviços</a>
      <a href="#planos">Planos</a>
      <a href="#contato">Contato</a>
      <a href="?rota=login" class="btn-red">Login</a>
    </nav>
  </header>
  
  <!-- Hero -->
  <section class="hero">
    <h2>Transforme seu corpo com a <span class="highlight-red">TechFit</span></h2>
    <p>Treinos personalizados, acompanhamento e resultados reais.</p>
    <a href="?rota=login" class="btn-red btn-large">Começar Agora</a>
  </section>
  
  <!-- Serviços -->
  <section id="servicos" class="servicos">
    <h3>Nossos Serviços</h3>
    <div class="card-grid">
      <div class="card card-gradient">
        <i class="fas fa-dumbbell card-icon"></i>
        <h4>Musculação</h4>
        <p>Equipamentos modernos e ambiente motivador.</p>
      </div>
      <div class="card card-gradient">
        <i class="fas fa-running card-icon"></i>
        <h4>Treino Funcional</h4>
        <p>Melhore sua resistência e condicionamento físico.</p>
      </div>
      <div class="card card-gradient">
        <i class="fas fa-heartbeat card-icon"></i>
        <h4>Avaliação Física</h4>
        <p>Acompanhe sua evolução com dados reais.</p>
      </div>
    </div>
  </section>
  
  <!-- Planos -->
  <section id="planos" class="planos">
    <h3>Nossos Planos</h3>
    <div class="card-grid">
      <div class="card card-gradient">
        <h4>Básico</h4>
        <p>Acesso livre à academia</p>
        <p class="price">R$ 99/mês</p>
        <a href="?rota=login" class="btn-red">Assinar</a>
      </div>
      <div class="card card-gradient">
        <h4>Premium</h4>
        <p>Treino + acompanhamento</p>
        <p class="price">R$ 149/mês</p>
        <a href="?rota=login" class="btn-red">Assinar</a>
      </div>
      <div class="card card-gradient">
        <h4>VIP</h4>
        <p>Personal Trainer dedicado</p>
        <p class="price">R$ 299/mês</p>
        <a href="?rota=login" class="btn-red">Assinar</a>
      </div>
    </div>
  </section>
  
  <!-- Contato -->
  <section id="contato" class="contato">
    <h3>Fale Conosco</h3>
    <form class="contact-form">
      <input type="text" placeholder="Nome">
      <input type="email" placeholder="Email">
      <textarea placeholder="Mensagem"></textarea>
      <button type="submit" class="btn-red">Enviar</button>
    </form>
  </section>
  
  <script>
    document.addEventListener("DOMContentLoaded", () => { console.log("Principal Page Loaded"); });
  </script>
</body>
</html>
