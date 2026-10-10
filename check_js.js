const fs = require('fs');
let content = fs.readFileSync('resources/views/ocean-export/index.blade.php', 'utf8');
let start = content.indexOf('function oceanExportModule() {');
let end = content.lastIndexOf('</script>');
let js = content.substring(start, end);
// Mock blade variables
js = js.replace(/@json\(.*?\)/g, 'null');
js = js.replace(/\{\{.*?\}\}/g, 'null');
js = js.replace(/@foreach.*?@endforeach/gs, '');
js = js.replace(/@if.*?@endif/gs, '');
fs.writeFileSync('temp.js', js);
