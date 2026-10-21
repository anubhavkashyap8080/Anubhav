// Cloudflare Pages Function for /api/sync
interface Env {
  USERS_KV?: {
    get: (key: string) => Promise<string | null>;
    put: (key: string, value: string) => Promise<void>;
  };
}

let inMemorySync: string | null = null;

export const onRequestOptions = async () => {
  return new Response(null, {
    status: 204,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type'
    }
  });
};

export const onRequestGet = async (context: { env: Env }) => {
  let data: string | null = null;
  if (context.env?.USERS_KV) {
    data = await context.env.USERS_KV.get('ggh_platform_sync');
  } else {
    data = inMemorySync;
  }

  return new Response(data || '{}', {
    status: 200,
    headers: {
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'Cache-Control': 'no-cache'
    }
  });
};

export const onRequestPost = async (context: { request: Request; env: Env }) => {
  try {
    const body = await context.request.text();
    if (context.env?.USERS_KV) {
      await context.env.USERS_KV.put('ggh_platform_sync', body);
    } else {
      inMemorySync = body;
    }

    return new Response(JSON.stringify({ success: true }), {
      status: 200,
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*'
      }
    });
  } catch (err: any) {
    return new Response(JSON.stringify({ error: err.message }), {
      status: 500,
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*'
      }
    });
  }
};
