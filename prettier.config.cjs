module.exports = {
  arrowParens: 'always',
  bracketSpacing: true,
  endOfLine: 'lf',
  printWidth: 100,
  semi: true,
  singleQuote: true,
  tabWidth: 2,
  trailingComma: 'all',
  useTabs: false,
  overrides: [
    { files: ['*.md'], options: { proseWrap: 'always' } },
    { files: ['*.yml', '*.yaml'], options: { tabWidth: 2 } },
  ],
};

