const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    baseUrl: 'https://zenyvra-production-jumyzd.laravel.cloud',
    viewportWidth: 1280,
    viewportHeight: 800,
    setupNodeEvents(on, config) {
      config.browsers = config.browsers.concat({
        name: 'chrome-manual',
        family: 'chromium',
        displayName: 'Chrome (manual)',
        version: '1.0.0',
        path: process.env.LOCALAPPDATA + '\\Google\\Chrome\\Application\\chrome.exe',
        majorVersion: 1,
      });
      return config;
    },
  },
});