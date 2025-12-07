<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechFit - Transforme seu Corpo</title>
  
  <link rel="stylesheet" href="../Assets/Css/Principal.css">
  
  <script src="https://cdn.tailwindcss.com"></script>
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">

  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Rajdhani', 'sans-serif'],
          },
          colors: {
            techfit: {
              red: '#dc2626',
              dark: '#111827',
              card: '#1f2937'
            }
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gray-900 text-white font-sans antialiased selection:bg-red-600 selection:text-white">

  <header class="fixed w-full top-0 z-50 bg-gray-900/80 backdrop-blur-lg border-b border-gray-800 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20">
        <div class="flex items-center space-x-3 cursor-pointer" onclick="window.scrollTo(0,0)">
            <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-800 rounded-lg flex items-center justify-center shadow-lg">
                <i class="fas fa-dumbbell text-white text-lg"></i>
            </div>
            <span class="text-2xl font-bold tracking-wide">TECHFIT</span>
        </div>

        <nav class="hidden md:flex space-x-8 items-center">
          <a href="#servicos" class="text-gray-300 hover:text-red-500 transition-colors font-medium">Serviços</a>
          <a href="#planos" class="text-gray-300 hover:text-red-500 transition-colors font-medium">Planos</a>
          <a href="#contato" class="text-gray-300 hover:text-red-500 transition-colors font-medium">Contato</a>
        </nav>

        <div class="hidden md:flex items-center space-x-4">
          <a href="Login.php" class="text-gray-300 hover:text-white font-medium transition-colors">Entrar</a>
          <a href="Cadastro.php" class="px-6 py-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-bold rounded-lg shadow-lg hover:shadow-red-900/50 transition-all transform hover:-translate-y-0.5">
            Matricule-se
          </a>
        </div>

        <div class="md:hidden flex items-center">
            <button class="text-white hover:text-red-500 text-2xl">
                <i class="fas fa-bars"></i>
            </button>
        </div>
      </div>
    </div>
  </header>

  <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
    <div class="absolute top-0 left-1/2 w-full -translate-x-1/2 h-full z-0 pointer-events-none">
        <div class="absolute top-20 left-1/4 w-96 h-96 bg-red-600/20 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
      <h1 class="text-5xl md:text-7xl font-bold mb-6 tracking-tight leading-tight">
        Seu corpo,<br>
        <span class="bg-clip-text text-transparent bg-gradient-to-r from-red-500 to-red-700 animate-gradient">sua melhor versão.</span>
      </h1>
      <p class="text-xl text-gray-400 mb-10 max-w-2xl mx-auto leading-relaxed">
        Treinos personalizados, acompanhamento em tempo real e uma plataforma moderna para você superar seus limites.
      </p>
      
      <div class="flex flex-col sm:flex-row justify-center gap-4">
        <a href="Cadastro.php" class="px-8 py-4 bg-red-600 hover:bg-red-500 rounded-xl font-bold text-lg shadow-lg shadow-red-900/30 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2">
            <i class="fas fa-fire"></i> Começar Agora
        </a>
        <a href="#planos" class="px-8 py-4 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-2">
            Ver Planos
        </a>
      </div>

      <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8 border-t border-gray-800 pt-8 max-w-4xl mx-auto">
        <div>
            <p class="text-3xl font-bold text-white">2k+</p>
            <p class="text-gray-500 text-sm">Alunos Ativos</p>
        </div>
        <div>
            <p class="text-3xl font-bold text-white">50+</p>
            <p class="text-gray-500 text-sm">Equipamentos</p>
        </div>
        <div>
            <p class="text-3xl font-bold text-white">24h</p>
            <p class="text-gray-500 text-sm">Suporte Online</p>
        </div>
        <div>
            <p class="text-3xl font-bold text-white">4.9</p>
            <p class="text-gray-500 text-sm">Avaliação Média</p>
        </div>
      </div>
    </div>
  </section>

  <section id="servicos" class="py-20 bg-gray-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Por que escolher a <span class="text-red-500">TechFit</span>?</h2>
        <p class="text-gray-400">Tecnologia e performance unidas para o seu resultado.</p>
      </div>

      <div class="grid md:grid-cols-3 gap-8">
        <div class="group p-8 rounded-2xl bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-red-500/50 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
            <div class="w-14 h-14 bg-gray-700/50 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600/20 transition-colors">
                <i class="fas fa-dumbbell text-2xl text-red-500"></i>
            </div>
            <h3 class="text-xl font-bold mb-3">Musculação High-Tech</h3>
            <p class="text-gray-400 text-sm leading-relaxed">
                Equipamentos de última geração integrados ao nosso app para monitoramento de carga e repetições automático.
            </p>
        </div>

        <div class="group p-8 rounded-2xl bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-red-500/50 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
            <div class="w-14 h-14 bg-gray-700/50 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600/20 transition-colors">
                <i class="fas fa-mobile-alt text-2xl text-red-500"></i>
            </div>
            <h3 class="text-xl font-bold mb-3">Dashboard Exclusivo</h3>
            <p class="text-gray-400 text-sm leading-relaxed">
                Acompanhe sua evolução, agende treinos e veja suas métricas de saúde diretamente pelo painel do aluno.
            </p>
        </div>

        <div class="group p-8 rounded-2xl bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-red-500/50 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
            <div class="w-14 h-14 bg-gray-700/50 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600/20 transition-colors">
                <i class="fas fa-users text-2xl text-red-500"></i>
            </div>
            <h3 class="text-xl font-bold mb-3">Comunidade Ativa</h3>
            <p class="text-gray-400 text-sm leading-relaxed">
                Participe de desafios, rankings semanais e mantenha-se motivado com nossa comunidade de alunos.
            </p>
        </div>
      </div>
    </div>
  </section>

  <section id="planos" class="py-20 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Planos Flexíveis</h2>
        <p class="text-gray-400">Escolha o melhor caminho para sua jornada.</p>
      </div>

      <div class="grid md:grid-cols-3 gap-8 items-center">
        <div class="p-8 rounded-2xl bg-gray-900 border border-gray-800 hover:border-gray-600 transition-all">
            <h3 class="text-xl font-bold text-gray-300">Básico</h3>
            <div class="my-6">
                <span class="text-4xl font-bold">R$ 89</span>
                <span class="text-gray-500">/mês</span>
            </div>
            <ul class="space-y-4 mb-8 text-gray-400 text-sm">
                <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> Acesso à academia</li>
                <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> App básico</li>
                <li class="flex items-center text-gray-600"><i class="fas fa-times mr-2"></i> Aulas coletivas</li>
            </ul>
            <a href="Cadastro.php" class="block w-full py-3 border border-gray-700 rounded-xl text-center font-bold hover:bg-gray-800 transition-colors">Assinar Básico</a>
        </div>

        <div class="relative p-8 rounded-2xl bg-gradient-to-b from-gray-800 to-gray-900 border border-red-500 transform scale-105 shadow-2xl z-10">
            <div class="absolute top-0 right-0 bg-red-600 text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-lg">POPULAR</div>
            <h3 class="text-xl font-bold text-white">Premium</h3>
            <div class="my-6">
                <span class="text-5xl font-bold text-red-500">R$ 129</span>
                <span class="text-gray-400">/mês</span>
            </div>
            <ul class="space-y-4 mb-8 text-gray-300 text-sm">
                <li class="flex items-center"><i class="fas fa-check text-red-500 mr-2"></i> Acesso total ilimitado</li>
                <li class="flex items-center"><i class="fas fa-check text-red-500 mr-2"></i> Dashboard Completo</li>
                <li class="flex items-center"><i class="fas fa-check text-red-500 mr-2"></i> Aulas coletivas</li>
                <li class="flex items-center"><i class="fas fa-check text-red-500 mr-2"></i> Avaliação física trimestral</li>
            </ul>
            <a href="Cadastro.php" class="block w-full py-4 bg-red-600 hover:bg-red-700 rounded-xl text-center font-bold shadow-lg shadow-red-900/50 transition-colors">QUERO SER PREMIUM</a>
        </div>

        <div class="p-8 rounded-2xl bg-gray-900 border border-gray-800 hover:border-gray-600 transition-all">
            <h3 class="text-xl font-bold text-gray-300">VIP Personal</h3>
            <div class="my-6">
                <span class="text-4xl font-bold">R$ 299</span>
                <span class="text-gray-500">/mês</span>
            </div>
            <ul class="space-y-4 mb-8 text-gray-400 text-sm">
                <li class="flex items-center"><i class="fas fa-check text-purple-500 mr-2"></i> Tudo do plano Premium</li>
                <li class="flex items-center"><i class="fas fa-check text-purple-500 mr-2"></i> Personal Trainer dedicado</li>
                <li class="flex items-center"><i class="fas fa-check text-purple-500 mr-2"></i> Nutricionista mensal</li>
            </ul>
            <a href="Cadastro.php" class="block w-full py-3 border border-gray-700 rounded-xl text-center font-bold hover:bg-gray-800 transition-colors">Assinar VIP</a>
        </div>
      </div>
    </div>
  </section>

  <section class="py-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-red-900 to-gray-900 rounded-3xl p-10 md:p-16 text-center border border-red-800/30 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-red-600/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
            
            <h2 class="text-3xl md:text-4xl font-bold mb-6 relative z-10">Pronto para começar?</h2>
            <p class="text-gray-300 mb-8 max-w-xl mx-auto relative z-10">Não espere a segunda-feira. Sua transformação começa com um clique.</p>
            <a href="Cadastro.php" class="inline-block px-10 py-4 bg-white text-red-900 font-bold text-lg rounded-xl hover:bg-gray-100 transition-colors shadow-xl relative z-10">
                Criar Minha Conta Grátis
            </a>
        </div>
    </div>
  </section>

  <footer class="bg-gray-950 border-t border-gray-900 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <div>
                <div class="flex items-center space-x-2 mb-6">
                    <div class="w-8 h-8 bg-red-600 rounded flex items-center justify-center">
                        <i class="fas fa-dumbbell text-white text-sm"></i>
                    </div>
                    <span class="text-xl font-bold">TECHFIT</span>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed">
                    A academia do futuro, hoje. Transformando vidas através da tecnologia e do esporte.
                </p>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-6">Links Rápidos</h4>
                <ul class="space-y-3 text-sm text-gray-500">
                    <li><a href="Login.php" class="hover:text-red-500 transition-colors">Área do Aluno</a></li>
                    <li><a href="Cadastro.php" class="hover:text-red-500 transition-colors">Matrícula</a></li>
                    <li><a href="#" class="hover:text-red-500 transition-colors">Termos de Uso</a></li>
                </ul>
            </div>

            <div>
                <h4 id="contato" class="text-white font-bold mb-6">Contato</h4>
                <ul class="space-y-3 text-sm text-gray-500">
                    <li class="flex items-center"><i class="fas fa-envelope mr-3 text-gray-700"></i> contato@techfit.com</li>
                    <li class="flex items-center"><i class="fas fa-phone mr-3 text-gray-700"></i> (11) 99999-9999</li>
                    <li class="flex items-center"><i class="fas fa-map-marker-alt mr-3 text-gray-700"></i> Av. Tecnologia, 1000</li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6">Redes Sociais</h4>
                <div class="flex space-x-4">
                    <a href="#" class="w-10 h-10 rounded-lg bg-gray-900 flex items-center justify-center text-gray-400 hover:bg-red-600 hover:text-white transition-all">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-lg bg-gray-900 flex items-center justify-center text-gray-400 hover:bg-blue-600 hover:text-white transition-all">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-lg bg-gray-900 flex items-center justify-center text-gray-400 hover:bg-black hover:text-white transition-all">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-900 pt-8 text-center">
            <p class="text-gray-600 text-sm">© 2024 TechFit Academia. Todos os direitos reservados.</p>
        </div>
    </div>
  </footer>

</body>
</html>