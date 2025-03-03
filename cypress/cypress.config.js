const fs = require('fs');
const path = require('path');
const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    baseUrl: 'https://fakestoreapi.com', 
    setupNodeEvents(on, config) {
      on('task', {
        'fileSystem:writeFile'({ filename, content }) {
          return new Promise((resolve, reject) => {
            fs.writeFile(filename, content, (err) => {
              if (err) {
                return reject(err);
              }
              resolve(null);
            });
          });
        },
        'fileSystem:ensureDir'(dirPath) {
          return new Promise((resolve, reject) => {
            if (!fs.existsSync(dirPath)) {
              fs.mkdirSync(dirPath, { recursive: true });
            }
            resolve(null);
          });
        }
      });
    },
  },
});