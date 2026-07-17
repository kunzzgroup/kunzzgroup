import { getLoginUrl } from '../../config.js';

function getReturnPath() {
  return `${window.location.pathname}${window.location.search}`.replace(/^\//, '');
}

export function redirectToBackendLogin(returnPath = getReturnPath()) {
  const loginUrl = new URL(getLoginUrl(), window.location.origin);
  if (returnPath) {
    loginUrl.searchParams.set('redirect', returnPath.replace(/^\//, ''));
  }
  window.location.replace(loginUrl.toString());
}

function looksLikeLoginPageHtml(html) {
  const sample = html.slice(0, 12000).toLowerCase();
  return (
    sample.includes('请登入您的账号')
    || (sample.includes('<form') && sample.includes('password') && sample.includes('登入'))
  );
}

async function readJsonSafe(response) {
  try {
    return await response.json();
  } catch {
    return null;
  }
}

/**
 * Fetch a PHP HTML fragment for React v2 pages.
 * On auth failure, redirects to login instead of injecting login HTML.
 */
export async function fetchBackendFragment(url) {
  const response = await fetch(url, {
    credentials: 'include',
    cache: 'no-store',
    headers: {
      'X-Kunzz-Backend-Fragment': '1',
      Accept: 'text/html, application/json;q=0.9, */*;q=0.8',
    },
  });

  const contentType = response.headers.get('content-type') || '';

  if (contentType.includes('application/json') || response.status === 401 || response.status === 403) {
    const payload = await readJsonSafe(response);
    if (payload?.redirect) {
      window.location.replace(payload.redirect);
    } else {
      redirectToBackendLogin();
    }
    throw new Error(payload?.message || '登录已过期，请重新登录');
  }

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  const html = await response.text();

  if (looksLikeLoginPageHtml(html)) {
    redirectToBackendLogin();
    throw new Error('登录已过期，请重新登录');
  }

  return html;
}
