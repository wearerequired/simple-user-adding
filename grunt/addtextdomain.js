module.exports = {
  dist: {
    options: {
      textdomain   : 'simple-user-adding',
      updateDomains: []
    },
    target : {
      files: {
        src: ['*.php', '**/*.php', '!node_modules/**', '!tests/**']
      }
    }
  }
};