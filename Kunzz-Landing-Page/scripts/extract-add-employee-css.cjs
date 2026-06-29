const fs = require('fs');
const path = require('path');

const src = fs.readFileSync(path.join(__dirname, '../../backend/add_employee.php'), 'utf8');
const start = src.indexOf('<style>') + '<style>'.length;
const end = src.indexOf('</style>', start);
const css = src.slice(start, end).trim();
const out = `body.add-employee-body {\n  background: #f3f4f6;\n}\n\n.add-employee-container {\n  padding: 0 !important;\n  height: 100vh;\n  max-width: 100% !important;\n}\n\n${css}\n`;
fs.writeFileSync(path.join(__dirname, '../../backend/css/add-employee.css'), out);
console.log('written', out.length);
