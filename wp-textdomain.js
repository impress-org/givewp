const wpTextdomain = require('wp-textdomain');

wpTextdomain(process.argv[2], {
    domain: 'give',
    fix: true,
});
