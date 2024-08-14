import sys
import os
from config.config import *
from database.conexao_db import ConexaoBD
from reports.gerenciador_relatorio import GerenciadorRelatorios
from email_sender.enviador_email import EnviadorEmail

# Adiciona o diretório raiz do projeto ao PYTHONPATH
project_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.append(project_root)



def main():
    try:
        # Inicialização
        conexao = ConexaoBD(DB_USER, DB_PASS, DB_HOST, DB_NAME)
        enviador = EnviadorEmail(SMTP_SERVER, SMTP_PORT, EMAIL_SENDER, EMAIL_PASS)
        gerenciador = GerenciadorRelatorios(conexao, enviador)

        # Gerar e enviar relatórios mensais
        gerenciador.gerar_e_enviar_relatorios(periodo='mensal')

        gerenciador.gerar_e_enviar_relatorios(periodo='semanal')

        print("Relatórios gerados e enviados com sucesso.")
    except Exception as e:
        print("Ocorreu um erro durante a execução:", str(e))
    finally:
        # Finaliza a conexão
        if conexao:
            conexao.fechar_conexao()

if __name__ == "__main__":
    main()
