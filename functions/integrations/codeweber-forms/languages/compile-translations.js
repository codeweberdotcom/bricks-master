const fs = require('fs');
const path = require('path');
const { gettextParser } = require('gettext-parser');

console.log('🔄 Compiling CodeWeber Forms translations...\n');

const poPath = path.join(__dirname, 'codeweber-forms-ru_RU.po');

if (!fs.existsSync(poPath)) {
    console.log('⚠️ PO file not found:', poPath);
    process.exit(1);
}

try {
    const poFile = fs.readFileSync(poPath);
    const po = gettextParser.po.parse(poFile);
    
    // Компилируем .mo файл для PHP
    const mo = gettextParser.mo.compile(po);
    const moPath = path.join(__dirname, 'codeweber-forms-ru_RU.mo');
    fs.writeFileSync(moPath, mo);
    
    console.log('✅ MO file compiled: codeweber-forms-ru_RU.mo');
    console.log('✅ Translations ready!\n');
} catch (error) {
    console.error('❌ Error compiling translations:', error.message);
    process.exit(1);
}


