module.exports = {
      readme_txt: {
        src: [ 'readme.txt' ],
        overwrite: true,
        replacements: [{
          from: /Stable tag: (.*)/,
          to: "Stable tag: <%= pkg.version %>"
        }]
      },
      main_php: {
        src: [ '<%= pkg.pot.src %>' ],
        overwrite: true,
        replacements: [{
          from: /define(.*)_VER'.*/,
          to: "define( '<%= pkg.constant.ver %>' , '<%= pkg.version %>' );"
        },{
          from: / Version:\s*(.*)/,
          to: " Version: <%= pkg.version %>"
        },{
          from: /EDD_(.*)_VER/,
          to: "<%= pkg.constant.ver %>"
        },{
           from: /EDD_(.*)_DIR/,
          to: "<%= pkg.constant.dir %>"
        },{
            from: /EDD_(.*)_URL/,
          to: "<%= pkg.constant.url %>"
        }]
      }
    };