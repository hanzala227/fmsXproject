import os
import re

views_dir = '/home/muhammad-hanzala/Downloads/backups/fms2.0/resources/views'

def process_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()
        
    if 'data-col="color"' not in content and 'color-mark' not in content:
        return False
        
    print(f"Processing {filepath}")
    
    lines = content.split('\n')
    new_lines = []
    
    color_col_left = None
    color_col_width = 35
    
    for line in lines:
        if 'data-col="color"' in line or ('<td' in line and 'left:' in line and 'width:35px' in line):
            # heuristic to find the color column offset
            pass
            
    # Better to find exactly the color left offset by looking at lines containing 'color-mark'
    for i, line in enumerate(lines):
        if 'color-mark' in line or 'data-col="color"' in line:
            # check current line or previous 5 lines for `<td` or `<th` with left:
            for j in range(i, max(-1, i-5), -1):
                match_left = re.search(r'left:\s*(\d+)px', lines[j])
                match_width = re.search(r'width:\s*(\d+)px', lines[j])
                if match_left:
                    color_col_left = int(match_left.group(1))
                if match_width:
                    color_col_width = int(match_width.group(1))
                if match_left or match_width:
                    break
            if color_col_left is not None:
                break
                
    if color_col_left is not None:
        print(f"Found color column at left:{color_col_left}px, width:{color_col_width}px")
        
    inside_color_td = False
    
    for i, line in enumerate(lines):
        if '<th' in line and 'data-col="color"' in line:
            continue
            
        if '<!-- Color -->' in line or '{{-- Color --}}' in line:
            continue
            
        if '<td' in line and not inside_color_td:
            is_color = False
            for j in range(i, min(i+10, len(lines))):
                if '<td' in lines[j] and j > i: break
                if 'color-mark' in lines[j]:
                    is_color = True
                    break
            if is_color:
                inside_color_td = True
                continue
                
        if inside_color_td:
            if '</td>' in line:
                inside_color_td = False
            continue
            
        # Adjust sticky columns
        if 'class="sticky-col' in line or 'sticky-col-header' in line:
            def repl(m):
                val = int(m.group(1))
                if color_col_left is not None and val > color_col_left:
                    return f"left:{val - color_col_width}px"
                return m.group(0)
            line = re.sub(r'left:\s*(\d+)px', repl, line)
            
        new_lines.append(line)
        
    content = '\n'.join(new_lines)
    
    with open(filepath, 'w') as f:
        f.write(content)
        
    return True

processed = 0
for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.blade.php'):
            if process_file(os.path.join(root, file)):
                processed += 1
                
print(f"Total processed files: {processed}")
