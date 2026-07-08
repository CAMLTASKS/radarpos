const fs = require('fs');
const content = fs.readFileSync('/var/www/html/heladeria/resources/js/Pages/POS.vue', 'utf8');
const vue = require('/var/www/html/heladeria/node_modules/@vue/compiler-sfc');
const parsed = vue.parse(content);
console.log(JSON.stringify(parsed.errors, null, 2));
