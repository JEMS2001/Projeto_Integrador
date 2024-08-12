import pandas as pd
from sqlalchemy import create_engine
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.application import MIMEApplication
import smtplib
import io
from datetime import datetime

class ConexaoBD:
    def __init__(self, usuario, senha, host, banco):
        self.engine = create_engine(f'mysql+pymysql://{usuario}:{senha}@{host}/{banco}')

    def executar_query(self, query):
        try:
            return pd.read_sql(query, self.engine)
        except Exception as e:
            print(f"Erro ao executar a query: {e}")

class GeradorRelatorio:
    def __init__(self, conexao):
        self.conexao = conexao

    def relatorio_projetos(self, id_empresa):
        query = f"""
        SELECT p.nome, p.tipo, p.data_inicio, p.data_fim, p.status,
               COUNT(DISTINCT mp.id_membro) as num_membros,
               COUNT(DISTINCT t.id_tarefa) as num_tarefas,
               AVG(DATEDIFF(IFNULL(t.data_conclusao, CURDATE()), t.data_criacao)) as media_dias_conclusao
        FROM projeto p
        LEFT JOIN membro_projeto mp ON p.id_projeto = mp.id_projeto
        LEFT JOIN tarefa t ON p.id_projeto = t.id_projeto
        WHERE p.id_empresa = {id_empresa}
        GROUP BY p.id_projeto
        """
        return self.conexao.executar_query(query)

    def relatorio_desempenho_membros(self, id_empresa):
        query = f"""
        SELECT m.nome, COUNT(t.id_tarefa) as tarefas_concluidas,
               AVG(DATEDIFF(t.data_conclusao, t.data_criacao)) as media_dias_conclusao
        FROM membro m
        JOIN tarefa t ON m.id_membro = t.id_membro
        WHERE m.id_empresa = {id_empresa} AND t.status = 'Concluída'
        GROUP BY m.id_membro
        ORDER BY tarefas_concluidas DESC
        """
        return self.conexao.executar_query(query)

    def relatorio_uso_sistema(self, id_empresa):
        query = f"""
        SELECT m.nome, 
               COUNT(DISTINCT s.id_sessao) as num_sessoes,
               SUM(s.duracao_segundos) / 3600 as horas_total,
               AVG(s.duracao_segundos) / 60 as media_minutos_sessao
        FROM membro m
        JOIN sessao_usuario s ON m.id_membro = s.id_membro
        WHERE m.id_empresa = {id_empresa} AND s.data_inicio >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
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

class EnviadorEmail:
    def __init__(self, smtp_server, porta, remetente, senha):
        self.smtp_server = smtp_server
        self.porta = porta
        self.remetente = remetente
        self.senha = senha

    def enviar_email(self, destinatario, assunto, corpo, anexo=None):
        msg = MIMEMultipart()
        msg['From'] = self.remetente
        msg['To'] = destinatario
        msg['Subject'] = assunto
        
        msg.attach(MIMEText(corpo, 'html'))
        
        if anexo:
            part = MIMEApplication(anexo.getvalue())
            part.add_header('Content-Disposition', 'attachment', filename='relatorio.xlsx')
            msg.attach(part)
        
        try:
            with smtplib.SMTP(self.smtp_server, self.porta) as server:
                server.starttls()
                server.login(self.remetente, self.senha)
                server.send_message(msg)
            print(f"E-mail enviado com sucesso para {destinatario}")
        except Exception as e:
            print(f"Erro ao enviar o e-mail: {e}")

class GerenciadorRelatorios:
    def __init__(self, conexao_bd, enviador_email):
        self.gerador = GeradorRelatorio(conexao_bd)
        self.enviador = enviador_email

    def gerar_e_enviar_relatorios(self):
        empresas = self.gerador.obter_empresas()
        
        for _, empresa in empresas.iterrows():
            id_empresa = empresa['id_empresa']
            nome_empresa = empresa['nome']
            email_empresa = empresa['email']
            
            # Gerar relatórios específicos para a empresa
            df_projetos = self.gerador.relatorio_projetos(id_empresa)
            df_desempenho = self.gerador.relatorio_desempenho_membros(id_empresa)
            df_uso = self.gerador.relatorio_uso_sistema(id_empresa)
            
            # Criar planilha Excel com múltiplas abas
            excel_buffer = self._criar_excel([
                ('Projetos', df_projetos),
                ('Desempenho', df_desempenho),
                ('Uso do Sistema', df_uso)
            ])
            
            # Criar corpo do e-mail
            corpo_email = f"""
            <html>
            <body>
                <h2>Relatório da Empresa {nome_empresa} - {datetime.now().strftime('%d/%m/%Y')}</h2>
                <p>Segue em anexo o relatório completo da empresa contendo:</p>
                <ul>
                    <li>Resumo de Projetos</li>
                    <li>Desempenho dos Membros</li>
                    <li>Uso do Sistema nos últimos 30 dias</li>
                </ul>
                <p>Por favor, revise as informações e entre em contato se tiver alguma dúvida.</p>
            </body>
            </html>
            """
            
            # Enviar e-mail
            self.enviador.enviar_email(
                email_empresa,
                f"Relatório da Empresa {nome_empresa} - {datetime.now().strftime('%d/%m/%Y')}",
                corpo_email,
                excel_buffer
            )

    def _criar_excel(self, dataframes):
        buffer = io.BytesIO()
        with pd.ExcelWriter(buffer, engine='openpyxl') as writer:
            for nome, df in dataframes:
                df.to_excel(writer, sheet_name=nome, index=False)
        buffer.seek(0)
        return buffer

# Configuração e uso do script
if __name__ == "__main__":
    # Configurações
    DB_USER = 'root'
    DB_PASS = ''
    DB_HOST = 'localhost'
    DB_NAME = 'projeto'

    SMTP_SERVER = 'smtp.gmail.com'
    SMTP_PORT = 587
    EMAIL_SENDER = 'soaresfelipe396@gmail.com'
    EMAIL_PASS = ''

    # Inicialização
    conexao = ConexaoBD(DB_USER, DB_PASS, DB_HOST, DB_NAME)
    enviador = EnviadorEmail(SMTP_SERVER, SMTP_PORT, EMAIL_SENDER, EMAIL_PASS)
    gerenciador = GerenciadorRelatorios(conexao, enviador)

    # Gerar e enviar relatórios
    gerenciador.gerar_e_enviar_relatorios()

    print("Relatórios gerados e enviados com sucesso.")