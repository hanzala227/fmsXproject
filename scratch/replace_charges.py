import re
with open('/home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/air-import/index.blade.php', 'r') as f:
    content = f.read()

content = re.sub(r'\s+get filteredCharges\(\) \{[\s\S]*?return true;\s+\}\s+\},?', '', content)

with open('/home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views/air-import/index.blade.php', 'w') as f:
    f.write(content)
