// Minimal static file server for the configurator tests (serves the plugin root).
const http = require('http');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const port = parseInt(process.argv[2] || '9401', 10);
const types = {
	'.html': 'text/html; charset=utf-8',
	'.js': 'text/javascript; charset=utf-8',
	'.css': 'text/css; charset=utf-8',
	'.json': 'application/json',
};

http.createServer((req, res) => {
	let file = path.join(root, decodeURIComponent(new URL(req.url, 'http://localhost').pathname));
	if (!file.startsWith(root)) {
		res.writeHead(403).end();
		return;
	}
	if (fs.existsSync(file) && fs.statSync(file).isDirectory()) {
		file = path.join(file, 'index.html');
	}
	fs.readFile(file, (err, body) => {
		if (err) {
			res.writeHead(404).end('Not found');
			return;
		}
		res.writeHead(200, { 'Content-Type': types[path.extname(file)] || 'application/octet-stream' }).end(body);
	});
}).listen(port, '127.0.0.1');
