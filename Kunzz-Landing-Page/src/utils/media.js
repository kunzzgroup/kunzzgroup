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
