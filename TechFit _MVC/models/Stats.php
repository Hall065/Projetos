<?php
class Stats {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Busca dados para o painel do Admin
    public function getAdminStats() {
        $stats = [];
        $stats['total_usuarios'] = $this->pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        $stats['agendamentos_ativos'] = $this->pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'confirmado'")->fetchColumn();
        $stats['planos_premium'] = $this->pdo->query("SELECT COUNT(*) FROM usuarios WHERE plano = 'Premium'")->fetchColumn();
        
        $stmt = $this->pdo->query("SELECT nome, data_cadastro FROM usuarios ORDER BY data_cadastro DESC LIMIT 5");
        $stats['ultimos_usuarios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }

    // Busca dados para o painel do Usuário
    public function getUserDashboardStats($user_id) {
        $stats = [];

        // Treinos este mês
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM treinos 
            WHERE id_usuario = ? AND MONTH(data_criacao) = MONTH(CURRENT_DATE()) AND YEAR(data_criacao) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([$user_id]);
        $stats['treinos_mes'] = $stmt->fetchColumn();

        // Próximo treino
        $stmt = $this->pdo->prepare("
            SELECT titulo, data_inicio FROM agenda_treinos 
            WHERE id_usuario = ? AND data_inicio > NOW() 
            ORDER BY data_inicio ASC LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $stats['proximo_treino'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Atividade Recente
        $stmt = $this->pdo->prepare("
            SELECT descricao, data, tipo FROM atividades 
            WHERE id_usuario = ? 
            ORDER BY data DESC LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $stats['atividades'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
}