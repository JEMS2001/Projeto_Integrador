import sys
import os

# Adiciona o diretório raiz do projeto ao PYTHONPATH
project_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.append(project_root)

from config.config import *
from database.conexao_db import ConexaoBD
from reports.gerenciador_relatorio import GerenciadorRelatorios
from email_sender.enviador_email import EnviadorEmail


def main():
    # Inicialização
    conexao = ConexaoBD(DB_USER, DB_PASS, DB_HOST, DB_NAME)
    enviador = EnviadorEmail(SMTP_SERVER, SMTP_PORT, EMAIL_SENDER, EMAIL_PASS)
    gerenciador = GerenciadorRelatorios(conexao, enviador)

    # Gerar e enviar relatórios mensais
    gerenciador.gerar_e_enviar_relatorios(periodo='mensal')

    print("Relatórios gerados e enviados com sucesso.")

if __name__ == "__main__":
    main()
