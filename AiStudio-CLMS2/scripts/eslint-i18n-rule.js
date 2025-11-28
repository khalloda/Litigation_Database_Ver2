// ESLint Rule to Prevent Hard-Coded Text in JSX
// Add this to your .eslintrc.js or eslint.config.js

module.exports = {
  rules: {
    // Prevent hard-coded strings in JSX
    'react/jsx-no-literals': [
      'warn',
      {
        noStrings: true,
        allowedStrings: ['/', '-', '—', '•', ':', ',', '.'],
        ignoreProps: true,
        noAttributeStrings: false,
      },
    ],
  },
};

// Alternative: Custom ESLint Rule for Specific Patterns
// This can be added as a custom rule in your ESLint configuration

const hardcodedTextRule = {
  meta: {
    type: 'suggestion',
    docs: {
      description: 'Disallow hard-coded "Loading..." and "Error:" text in JSX',
      category: 'Best Practices',
      recommended: true,
    },
    messages: {
      hardcodedLoading: 'Use t("common.loading") instead of hard-coded "Loading..."',
      hardcodedError: 'Use t("common.error") instead of hard-coded "Error:"',
    },
  },
  create(context) {
    return {
      JSXText(node) {
        const text = node.value.trim();
        if (text === 'Loading...' || text.includes('Loading')) {
          context.report({
            node,
            messageId: 'hardcodedLoading',
          });
        }
        if (text.startsWith('Error:')) {
          context.report({
            node,
            messageId: 'hardcodedError',
          });
        }
      },
    };
  },
};

// Usage Instructions:
// 1. Install eslint-plugin-react if not already installed:
//    npm install --save-dev eslint-plugin-react
//
// 2. Add the rule to your .eslintrc.js:
//    {
//      "extends": ["plugin:react/recommended"],
//      "rules": {
//        "react/jsx-no-literals": ["warn", {
//          "noStrings": true,
//          "allowedStrings": ["—", "•", ":", ",", "."]
//        }]
//      }
//    }
//
// 3. Run ESLint to check for violations:
//    npx eslint src/pages/**/*.tsx
