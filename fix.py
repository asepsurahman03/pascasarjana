import os
import re

proposal_path = r'C:\xampp\htdocs\webdummy\pages\cetak_lampiran_proposal.php'
tesis_path = r'C:\xampp\htdocs\webdummy\pages\cetak_lampiran.php'

with open(tesis_path, 'r', encoding='utf-8') as f:
    tesis_content = f.read()
    
# Extract everything from <div class="toolbar no-print"> to the end from tesis
match = re.search(r'<div class="toolbar no-print">.*</html>', tesis_content, re.DOTALL)
if not match:
    print("Could not find toolbar in tesis")
    exit(1)
toolbar_and_rest = match.group(0)

# But we need to replace 'tesis' with 'proposal' in the JS keys if any?
# In tesis, the JS has: const pageName = window.location.search.includes('proposal') ? 'proposal' : 'tesis'; 
# So the JS is completely reusable!

with open(proposal_path, 'r', encoding='utf-8') as f:
    proposal_content = f.read()

# Replace everything in proposal from <!-- Indikator Zoom to the end with the extracted toolbar_and_rest
# Wait, proposal_content has <!-- Indikator Zoom right after the back button!
# Let's find the back button div
match_back = re.search(r'(<div style="position:fixed; top:16px; left:16px; z-index:999;" class="no-print">.*?</div>)', proposal_content, re.DOTALL)
if match_back:
    back_button = match_back.group(1)
    
    # The new content should be: everything up to back_button + back_button + \n\n + toolbar_and_rest
    pre_back = proposal_content[:match_back.start()]
    new_content = pre_back + back_button + '\n\n' + toolbar_and_rest
    
    with open(proposal_path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Fixed proposal file!")
else:
    print("Could not find back button in proposal")
