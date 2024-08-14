class GeradorRelatorio:
    def __init__(self, conexao):
        self.conexao = conexao

    def relatorio_projetos(self, id_empresa, data_inicio, data_fim):
        query = f"""
        SELECT p.nome, p.tipo, p.data_inicio, p.data_fim, p.status,
               COUNT(DISTINCT mp.id_membro) as num_membros,
               COUNT(DISTINCT t.id_tarefa) as num_tarefas,
               AVG(DATEDIFF(IFNULL(t.data_conclusao, CURDATE()), t.data_criacao)) as media_dias_conclusao
        FROM projeto p
        LEFT JOIN membro_projeto mp ON p.id_projeto = mp.id_projeto
        LEFT JOIN tarefa t ON p.id_projeto = t.id_projeto
        WHERE p.id_empresa = {id_empresa}
        AND (p.data_inicio BETWEEN '{data_inicio}' AND '{data_fim}'
             OR p.data_fim BETWEEN '{data_inicio}' AND '{data_fim}'
             OR (p.data_inicio <= '{data_inicio}' AND p.data_fim >= '{data_fim}'))
        GROUP BY p.id_projeto
        """
        return self.conexao.executar_query(query)

    def relatorio_desempenho_membros(self, id_empresa, data_inicio, data_fim):
        query = f"""
        SELECT m.nome, COUNT(t.id_tarefa) as tarefas_concluidas,
               AVG(DATEDIFF(t.data_conclusao, t.data_criacao)) as media_dias_conclusao
        FROM membro m
        JOIN tarefa t ON m.id_membro = t.id_membro
        WHERE m.id_empresa = {id_empresa} AND t.status = 'Concluída'
        AND t.data_conclusao BETWEEN '{data_inicio}' AND '{data_fim}'
        GROUP BY m.id_membro
        ORDER BY tarefas_concluidas DESC
        """
        return self.conexao.executar_query(query)

    def relatorio_uso_sistema(self, id_empresa, data_inicio, data_fim):
        query = f"""
        SELECT m.nome, 
               COUNT(DISTINCT s.id_sessao) as num_sessoes,
               SUM(s.duracao_segundos) / 3600 as horas_total,
               AVG(s.duracao_segundos) / 60 as media_minutos_sessao
        FROM membro m
        JOIN sessao_usuario s ON m.id_membro = s.id_membro
        WHERE m.id_empresa = {id_empresa} 
        AND s.data_inicio BETWEEN '{data_inicio}' AND '{data_fim}'
        GROUP BY m.id_membro
        ORDER BY horas_total DESC
        """
        return self.conexao.executar_query(query)

    def obter_empresas(self):
        query = """
        SELECT id_empresa, nome, email
        FROM empresa
        """
        return self.conexao.executar_query(query)