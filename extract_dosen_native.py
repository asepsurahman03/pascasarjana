import zipfile
import xml.etree.ElementTree as ET
import json
import re

def extract_xlsx(file_path):
    with zipfile.ZipFile(file_path, 'r') as archive:
        # Get shared strings
        shared_strings = []
        try:
            with archive.open('xl/sharedStrings.xml') as f:
                tree = ET.parse(f)
                root = tree.getroot()
                ns = {'main': root.tag.split('}')[0].strip('{')} if '}' in root.tag else {}
                for si in root.findall('.//main:si', ns) if ns else root.findall('.//si'):
                    t_tags = si.findall('.//main:t', ns) if ns else si.findall('.//t')
                    text = "".join([t.text for t in t_tags if t.text])
                    shared_strings.append(text)
        except KeyError:
            pass 

        # Get sheet mapping
        tree = ET.parse(archive.open('xl/workbook.xml'))
        root = tree.getroot()
        ns = {'m': root.tag.split('}')[0].strip('{')} if '}' in root.tag else {}
        sheets = root.findall('.//m:sheet', ns) if ns else root.findall('.//sheet')
        
        sheet_mapping = {}
        for sheet in sheets:
            # target r:id in rels
            rId = sheet.attrib.get('{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id')
            sheet_name = sheet.attrib.get('name')
            sheet_mapping[rId] = sheet_name

        # Get rels to find file paths
        tree = ET.parse(archive.open('xl/_rels/workbook.xml.rels'))
        root = tree.getroot()
        ns_rels = {'rel': root.tag.split('}')[0].strip('{')} if '}' in root.tag else {}
        rels = root.findall('.//rel:Relationship', ns_rels) if ns_rels else root.findall('.//Relationship')
        
        sheet_paths = {}
        for rel in rels:
            if 'worksheet' in rel.attrib.get('Type', ''):
                sheet_paths[rel.attrib.get('Id')] = 'xl/' + rel.attrib.get('Target')

        # Mapping names to prodi_id
        # 'Dosen S2 Pedagogi' -> 4
        # 'Dosen S2 Hukum' -> 3
        # 'Dosen S2 Informatika' -> 1
        # 'Dosen S2 Manajemen' -> 2
        # 'Dosen S3 Ilmu Komputer' -> 5
        prodi_map = {
            'Dosen S2 Pedagogi': 4,
            'Dosen S2 Hukum': 3,
            'Dosen S2 Informatika': 1,
            'Dosen S2 Manajemen': 2,
            'Dosen S3 Ilmu Komputer': 5
        }
        
        all_data = []

        for rId, sheet_name in sheet_mapping.items():
            path = sheet_paths.get(rId)
            if not path:
                continue
            prodi_id = prodi_map.get(sheet_name)
            if not prodi_id:
                continue
                
            with archive.open(path) as f:
                tree = ET.parse(f)
                root = tree.getroot()
                ns = {'main': root.tag.split('}')[0].strip('{')} if '}' in root.tag else {}
                
                sheet_data = []
                for row in root.findall('.//main:row', ns) if ns else root.findall('.//row'):
                    row_data = {}
                    for c in row.findall('.//main:c', ns) if ns else row.findall('.//c'):
                        r = c.attrib.get('r', '')
                        col_letter = re.sub(r'[0-9]+', '', r)
                        t = c.attrib.get('t', '')
                        v_tag = c.find('.//main:v', ns) if ns else c.find('.//v')
                        if v_tag is not None:
                            val = v_tag.text
                            if t == 's': 
                                val = shared_strings[int(val)]
                            row_data[col_letter] = val
                    if row_data:
                        sheet_data.append(row_data)
                
                # First 4 rows are usually title, starting at index 5 (6th row)
                if len(sheet_data) > 5:
                    for i in range(5, len(sheet_data) - 1):
                        row = sheet_data[i]
                        if row.get('C'): # Nama exists
                            all_data.append({
                                'prodi_id': prodi_id,
                                'nidn': row.get('B', ''),
                                'nama': row.get('C', ''),
                                'kualifikasi': row.get('D', '-'),
                                'email': row.get('E', '-'),
                                'jabatan': row.get('F', '-')
                            })

        return all_data

if __name__ == "__main__":
    file_path = 'Contoh Lampiran/Daftar_Dosen_Pascasarjana.xlsx'
    data = extract_xlsx(file_path)
    
    if data:
        with open('data_dosen.json', 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=4)
        print("Data extracted from all sheets to data_dosen.json successfully!")
