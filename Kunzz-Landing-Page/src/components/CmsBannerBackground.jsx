import { useEffect, useState } from 'react';
import { mediaUrl } from '../utils/media.js';

export default function CmsBannerBackground({ mediaType, className = 'background-image' }) {
  const [kind, setKind] = useState(null);
  const src = mediaUrl(mediaType);

  useEffect(() => {
    let cancelled = false;

    fetch(src, { method: 'HEAD', cache: 'no-store' })
      .then((response) => {
        if (cancelled || !response.ok) {
          return;
        }

        const contentType = response.headers.get('content-type') || '';
        setKind(contentType.startsWith('video/') ? 'video' : 'image');
      })
      .catch(() => {
        if (!cancelled) {
          setKind('image');
        }
      });

    return () => {
      cancelled = true;
    };
  }, [src]);

  if (kind === 'video') {
    return (
      <video
        className={`background-video ${className}`}
        autoPlay
        muted
        loop
        playsInline
        preload="auto"
        src={src}
      />
    );
  }

  if (kind === 'image') {
    return <img src={src} alt="" className={className} />;
  }

  return null;
}
