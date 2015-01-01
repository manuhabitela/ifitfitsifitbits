var flo = require('fb-flo'),
  fs = require('fs'),
  path = require('path'),
  exec = require('child_process').exec;

var server = flo('./', {
  port: 8888,
  host: 'localhost',
  glob: ['public/css/style.css'],
  verbose: true
}, resolver);

server.once('ready', function() {
  console.log('fb-flo server ready!');
});

function resolver(filepath, callback) {
  console.log(filepath + ' changed');
  exec('pleeease compile', function(err) {
    if (err) throw err;
    callback({
      resourceURL: '/dist/style.css', //what the browser sees
      contents: fs.readFileSync('public/dist/style.css') //where the file actually is on the filesystem
    });
  });
}
