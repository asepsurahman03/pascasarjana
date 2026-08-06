import pandas as pd
import json

# Read the excel file
df = pd.read_excel('Contoh Lampiran/Daftar_Dosen_Pascasarjana.xlsx')

# Clean column names (if necessary)
df.columns = df.columns.str.strip()

# Convert to a list of dicts and write to JSON
records = df.to_dict(orient='records')
with open('data_dosen.json', 'w', encoding='utf-8') as f:
    json.dump(records, f, ensure_ascii=False, indent=4)
print("Extracted to data_dosen.json successfully")
