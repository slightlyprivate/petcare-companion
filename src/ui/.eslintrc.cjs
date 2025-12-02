module.exports = {
  root: true,
  env: { browser: true, es2022: true, node: true },
  parserOptions: { ecmaVersion: 'latest', sourceType: 'module', ecmaFeatures: { jsx: true } },
  extends: [],
  plugins: [],
  rules: {
    // Forbid hardcoded UI route strings. Use PATHS from src/routes/paths.ts instead.
    'no-restricted-syntax': [
      'error',
      {
        selector: "JSXAttribute[name.name='to'][value.type='Literal']",
        message: 'Use PATHS constants for route paths (no string literals in Link/Navigate).',
      },
      {
        selector: "JSXAttribute[name.name='to'] > Literal[value=/^\\//]",
        message: 'Use PATHS constants for route paths (no string literals in Link/Navigate).',
      },
      {
        selector: "JSXAttribute[name.name='to'] > TemplateLiteral[quasis.0.value.raw=/^\\//]",
        message: 'Use PATHS constants for route paths (no template literals starting with /).',
      },
      {
        selector: "Property[key.name='path'] > Literal[value=/^\\//]",
        message: 'Use PATHS constants for route config (no string literal paths).',
      },
      {
        selector: "CallExpression[callee.property.name='assign'] > Literal[value=/^\\//]",
        message: 'Use PATHS constants for routing (no window.location.assign hardcoded paths).',
      },
    ],
  },
  overrides: [
    {
      files: ['src/routes/paths.ts'],
      rules: { 'no-restricted-syntax': 'off' },
    },
  ],
};
