/** React routes served at site root (not a deploy folder name). */
const ROOT_ROUTES = new Set(['Home_en', 'about', 'joinus', 'about_en', 'join_en']);

/** e.g. /kunzzgroup when the app is served from a subdirectory on Apache. */
export function getDeployBasePath() {
  if (typeof window === 'undefined') return '';
  const segments = window.location.pathname.split('/').filter(Boolean);
  if (segments.length === 0 || ROOT_ROUTES.has(segments[0])) return '';
  if (segments[0] === 'backend') return '';
  return `/${segments[0]}`;
}

function joinPath(base, path) {
  const normalizedBase = base.replace(/\/$/, '');
  const normalizedPath = path.replace(/^\//, '');
  if (/^https?:\/\//i.test(normalizedBase)) {
    return `${normalizedBase}/${normalizedPath}`;
  }
  return `${normalizedBase}/${normalizedPath}`;
}

/** Base URL for PHP pages not yet migrated to React. */
export function getPhpBase() {
  if (typeof window !== 'undefined') {
    const deployBase = getDeployBasePath();
    if (deployBase) {
      return `${deployBase}/frontend`;
    }
    if (import.meta.env.DEV) {
      return 'http://localhost/kunzzgroup/frontend';
    }
  }
  if (import.meta.env.VITE_PHP_BASE) return import.meta.env.VITE_PHP_BASE;
  return '/frontend';
}

export function getLoginUrl() {
  if (typeof window !== 'undefined') {
    return joinPath(getPhpBase(), 'login.html');
  }
  if (import.meta.env.VITE_LOGIN_URL) return import.meta.env.VITE_LOGIN_URL;
  return joinPath(getPhpBase(), 'login.html');
}

/** @deprecated Use getPhpBase() so subdirectory deploy resolves at runtime. */
export const PHP_BASE = getPhpBase();

/** @deprecated Use getLoginUrl() so subdirectory deploy resolves at runtime. */
export const LOGIN_URL = getLoginUrl();

export const EN_SITE_URL =
  import.meta.env.VITE_EN_SITE_URL || '/Home_en';

/** Base URL for PHP backend pages and assets (e.g. /kunzzgroup/backend). */
export function getBackendBase() {
  if (typeof window !== 'undefined') {
    const deployBase = getDeployBasePath();
    return deployBase ? `${deployBase}/backend` : '/backend';
  }
  if (import.meta.env.VITE_BACKEND_BASE) return import.meta.env.VITE_BACKEND_BASE;
  return '/backend';
}

/** Full URL to a backend PHP API script. */
export function getBackendApiUrl(scriptName) {
  return joinPath(getBackendBase(), scriptName);
}
