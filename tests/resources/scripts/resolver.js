module.exports = (path, options) => {
    return options.defaultResolver(path, {
        ...options,
        packageFilter: (pkg) => {
            if (
                pkg.name === 'uuid' ||
                pkg.name === 'react-colorful' ||
                pkg.name === '@eslint/eslintrc' ||
                pkg.name === 'expect' ||
                pkg.name === 'nanoid'
            ) {
                delete pkg.exports;
                delete pkg.module;
            }
            return pkg;
        },
    });
};
