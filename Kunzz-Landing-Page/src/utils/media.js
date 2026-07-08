import { getDeployBasePath } from '../config.js';

/** CMS-managed media served by serve_media.php via {deployBase}/media/{type} */
export function mediaUrl(type) {
  const base = getDeployBasePath();
  return `${base}/media/${type}`;
}

/** CMS-managed background music served by serve_media.php */
export function musicUrl() {
  return mediaUrl('background_music');
}

/** Resolve CMS/API relative asset paths for subdirectory deploys. */
export function resolveAssetUrl(url) {
  if (!url) return '';
  if (/^https?:\/\//i.test(url)) return url;

  const base = getDeployBasePath();
  const queryIndex = url.indexOf('?');
  const pathPart = queryIndex >= 0 ? url.slice(0, queryIndex) : url;
  const query = queryIndex >= 0 ? url.slice(queryIndex) : '';
  let path = pathPart.replace(/^(\.\.\/)+/, '').replace(/^\.\//, '');
  if (!path.startsWith('/')) path = `/${path}`;
  return `${base}${path}${query}`;
}
