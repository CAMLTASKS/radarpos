const fs = require('fs');
const content = fs.readFileSync('/var/www/html/heladeria/resources/js/Pages/POS.vue', 'utf8');
console.log(content.slice(6100, 6400));
