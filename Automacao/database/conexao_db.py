from sqlalchemy import create_engine
import pandas as pd
import logging
from sqlalchemy.exc import SQLAlchemyError

class ConexaoBD:
    def __init__(self, usuario, senha, host, banco):
        self.usuario = usuario
        self.senha = senha
        self.host = host
        self.banco = banco
        self.engine = None
        self.logger = logging.getLogger(__name__)

    def conectar(self):
        try:
            connection_string = f'mysql+pymysql://{self.usuario}:{self.senha}@{self.host}/{self.banco}'
            self.engine = create_engine(connection_string)
            self.logger.info("Conexão com o banco de dados estabelecida com sucesso.")
            return True
        except SQLAlchemyError as e:
            self.logger.error(f"Erro ao conectar ao banco de dados: {str(e)}")
            return False

    def testar_conexao(self):
        if not self.engine:
            self.logger.warning("Engine não inicializada. Tentando conectar...")
            if not self.conectar():
                return False

        try:
            with self.engine.connect() as connection:
                connection.execute("SELECT 1")
            self.logger.info("Teste de conexão bem-sucedido.")
            return True
        except SQLAlchemyError as e:
            self.logger.error(f"Falha no teste de conexão: {str(e)}")
            return False

    def executar_query(self, query):
        if not self.engine:
            self.logger.warning("Engine não inicializada. Tentando conectar...")
            if not self.conectar():
                return None

        try:
            resultado = pd.read_sql(query, self.engine)
            self.logger.info(f"Query executada com sucesso. Retornadas {len(resultado)} linhas.")
            return resultado
        except SQLAlchemyError as e:
            self.logger.error(f"Erro ao executar a query: {str(e)}")
            return None

    def fechar_conexao(self):
        if self.engine:
            self.engine.dispose()
            self.logger.info("Conexão com o banco de dados fechada.")
        else:
            self.logger.warning("Tentativa de fechar uma conexão não inicializada.")

    def __enter__(self):
        self.conectar()
        return self

    def __exit__(self, exc_type, exc_val, exc_tb):
        self.fechar_conexao()
        if exc_type:
            self.logger.error(f"Exceção ocorreu: {exc_type}, {exc_val}")
            return False  # Propaga a exceção
        return True