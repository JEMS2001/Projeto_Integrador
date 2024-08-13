from sqlalchemy import create_engine
import pandas as pd

class ConexaoBD:
    def __init__(self, usuario, senha, host, banco):
        self.engine = create_engine(f'mysql+pymysql://{usuario}:{senha}@{host}/{banco}')

    def executar_query(self, query):
        try:
            return pd.read_sql(query, self.engine)
        except Exception as e:
            print(f"Erro ao executar a query: {e}")