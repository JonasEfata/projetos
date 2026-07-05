import sqlite3
import os

# Caminho do arquivo do banco de dados
DB_PATH = os.path.join(os.path.dirname(__file__), 'moto.db')

def get_connection():
    """Abre uma conexão com o banco de dados."""
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row  # permite acessar colunas pelo nome
    return conn

def criar_tabelas():
    """Cria as tabelas se ainda não existirem."""
    conn = get_connection()
    cursor = conn.cursor()

    # Tabela de corridas calculadas
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS corridas (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            data          TEXT NOT NULL,
            distancia     REAL NOT NULL,
            retorno_vazio INTEGER NOT NULL,
            autonomia     REAL NOT NULL,
            preco_etanol  REAL NOT NULL,
            km_real       REAL NOT NULL,
            custo_total   REAL NOT NULL,
            valor_cobrado REAL,
            criado_em     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')

    # Tabela de registros diários
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS registros_diarios (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            data        TEXT NOT NULL UNIQUE,
            faturamento REAL NOT NULL,
            km_rodados  REAL NOT NULL,
            criado_em   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')

    conn.commit()
    conn.close()
    print("✅ Tabelas criadas com sucesso.")

# Cria as tabelas ao importar este arquivo
criar_tabelas()