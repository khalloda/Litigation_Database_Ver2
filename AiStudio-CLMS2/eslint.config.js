import js from '@eslint/js';
import react from 'eslint-plugin-react';
import tseslint from '@typescript-eslint/eslint-plugin';
import tsparser from '@typescript-eslint/parser';

export default [
  js.configs.recommended,
  {
    files: ['**/*.{ts,tsx}'],
    languageOptions: {
      parser: tsparser,
      parserOptions: {
        ecmaFeatures: {
          jsx: true,
        },
        ecmaVersion: 'latest',
        sourceType: 'module',
      },
      globals: {
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
        setTimeout: 'readonly',
        clearTimeout: 'readonly',
        setInterval: 'readonly',
        clearInterval: 'readonly',
      },
    },
    plugins: {
      react,
      '@typescript-eslint': tseslint,
    },
    rules: {
      // i18n rule to prevent hard-coded text in JSX
      'react/jsx-no-literals': [
        'warn',
        {
          noStrings: true,
          allowedStrings: ['/', '-', '—', '•', ':', ',', '.', '(', ')', '[', ']', '*', '+', '#', ' '],
          ignoreProps: true,
          noAttributeStrings: false,
        },
      ],
      // Disable prop-types since we're using TypeScript
      'react/prop-types': 'off',
      // TypeScript rules
      '@typescript-eslint/no-unused-vars': 'warn',
      '@typescript-eslint/no-explicit-any': 'warn',
    },
    settings: {
      react: {
        version: 'detect',
      },
    },
  },
];
