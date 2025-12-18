const fs = require('fs');
const path = require('path');
const gettextParser = require('gettext-parser');

console.log('🔄 Compiling CodeWeber Forms translations...\n');

// Список языков для компиляции
const languages = ['ru_RU', 'pl_PL'];

let compiledCount = 0;

languages.forEach(locale => {
    const poPath = path.join(__dirname, `codeweber-forms-${locale}.po`);
    
    if (!fs.existsSync(poPath)) {
        console.log(`⚠️ PO file not found: ${poPath}`);
        return;
    }
    
    try {
        const poFile = fs.readFileSync(poPath);
        const po = gettextParser.po.parse(poFile, 'utf8');
        
        // Компилируем .mo файл для PHP
        const mo = gettextParser.mo.compile(po);
        const moPath = path.join(__dirname, `codeweber-forms-${locale}.mo`);
        fs.writeFileSync(moPath, mo);
        
        console.log(`✅ MO file compiled: codeweber-forms-${locale}.mo`);
        compiledCount++;
    } catch (error) {
        console.error(`❌ Error compiling ${locale}:`, error.message);
        console.error(error.stack);
    }
});

if (compiledCount > 0) {
    console.log(`\n✅ ${compiledCount} translation file(s) compiled successfully!\n`);
} else {
    console.error('❌ No translations were compiled.');
    process.exit(1);
}


