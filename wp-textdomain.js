const wpTextdomain = require('wp-textdomain');

wpTextdomain(process.argv[2], {
    domain: 'give',
    // fix: true,
    'glob': {
        "ignore": ['vendor', 'languages', 'node_modules', 'assets', 'tests', 'sample-data', '.git', '.github']
    }
});
