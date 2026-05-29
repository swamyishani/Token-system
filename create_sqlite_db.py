import os
import sqlite3

cwd = os.path.dirname(os.path.abspath(__file__))
os.chdir(cwd)

sql_file = 'database.sql'
db_file = 'database.db'

if not os.path.exists(sql_file):
    raise FileNotFoundError(f'{sql_file} not found')

if os.path.exists(db_file):
    os.remove(db_file)

with open(sql_file, 'r', encoding='utf-8') as f:
    sql = f.read()

conn = sqlite3.connect(db_file)
cur = conn.cursor()
cur.executescript(sql)
conn.commit()
conn.close()
print(f'Created {db_file} from {sql_file}')
