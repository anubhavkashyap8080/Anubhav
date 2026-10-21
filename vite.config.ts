import { defineConfig, Plugin } from 'vite';
import react from '@vitejs/plugin-react';
import fs from 'fs';
import path from 'path';

function gghBackendSyncPlugin(): Plugin {
  const dataDir = path.resolve(__dirname, '.ggh_data');
  const usersFile = path.join(dataDir, 'users.json');
  const syncFile = path.join(dataDir, 'sync.json');

  const ensureDir = () => {
    if (!fs.existsSync(dataDir)) {
      fs.mkdirSync(dataDir, { recursive: true });
    }
  };

  const handleApi = (req: any, res: any, next: any) => {
    const rawUrl = req.url || '';
    const pathname = rawUrl.split('?')[0];

    if (pathname === '/api/users') {
      ensureDir();
      if (req.method === 'GET') {
        res.setHeader('Content-Type', 'application/json');
        res.setHeader('Access-Control-Allow-Origin', '*');
        if (fs.existsSync(usersFile)) {
          const data = fs.readFileSync(usersFile, 'utf-8');
          res.end(data || '[]');
        } else {
          res.end('[]');
        }
        return;
      } else if (req.method === 'POST') {
        let body = '';
        req.on('data', (chunk: any) => { body += chunk; });
        req.on('end', () => {
          try {
            ensureDir();
            fs.writeFileSync(usersFile, body, 'utf-8');
            res.setHeader('Content-Type', 'application/json');
            res.setHeader('Access-Control-Allow-Origin', '*');
            res.end(JSON.stringify({ success: true, count: JSON.parse(body).length }));
          } catch (err: any) {
            res.statusCode = 500;
            res.end(JSON.stringify({ error: err.message }));
          }
        });
        return;
      } else if (req.method === 'OPTIONS') {
        res.setHeader('Access-Control-Allow-Origin', '*');
        res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
        res.end();
        return;
      }
    }

    if (pathname === '/api/sync') {
      ensureDir();
      if (req.method === 'GET') {
        res.setHeader('Content-Type', 'application/json');
        res.setHeader('Access-Control-Allow-Origin', '*');
        if (fs.existsSync(syncFile)) {
          const data = fs.readFileSync(syncFile, 'utf-8');
          res.end(data || '{}');
        } else {
          res.end('{}');
        }
        return;
      } else if (req.method === 'POST') {
        let body = '';
        req.on('data', (chunk: any) => { body += chunk; });
        req.on('end', () => {
          try {
            ensureDir();
            fs.writeFileSync(syncFile, body, 'utf-8');
            res.setHeader('Content-Type', 'application/json');
            res.setHeader('Access-Control-Allow-Origin', '*');
            res.end(JSON.stringify({ success: true }));
          } catch (err: any) {
            res.statusCode = 500;
            res.end(JSON.stringify({ error: err.message }));
          }
        });
        return;
      }
    }

    next();
  };

  return {
    name: 'ggh-backend-sync',
    configureServer(server) {
      server.middlewares.use(handleApi);
    },
    configurePreviewServer(server) {
      server.middlewares.use(handleApi);
    }
  };
}

export default defineConfig({
  plugins: [react(), gghBackendSyncPlugin()],
  server: {
    port: 5173,
    host: true
  }
});
