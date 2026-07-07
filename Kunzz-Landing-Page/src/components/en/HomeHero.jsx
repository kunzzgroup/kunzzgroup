import { useEffect, useState } from 'react';
import { useAnimateOnScroll } from '../../hooks/useAnimateOnScroll.js';
import { getDeployBasePath } from '../../config.js';
import { mediaUrl } from '../../utils/media.js';

function HeroBackground() {
  const [videoFailed, setVideoFailed] = useState(false);
  const fallbackImage = `${getDeployBasePath()}/images/背景4.webp`;

  if (videoFailed) {
    return (
      <div
        className="background-video background-image-fallback"
        style={{ backgroundImage: `url('${fallbackImage}')` }}
        aria-hidden="true"
      />
    );
  }

  return (
    <video
      className="background-video"
      autoPlay
      muted
      loop
      playsInline
      preload="auto"
      src={mediaUrl('home_background')}
      onError={() => setVideoFailed(true)}
    />
  );
}

export default function HomeHero() {
  const animRef = useAnimateOnScroll();
  const [loaded, setLoaded] = useState(false);

  useEffect(() => {
    const onLoad = () => setLoaded(true);
    if (document.readyState === 'complete') {
      setLoaded(true);
    } else {
      window.addEventListener('load', onLoad);
      return () => window.removeEventListener('load', onLoad);
    }
  }, []);

  return (
    <section className={`home${loaded ? ' gradient-loaded' : ''}`}>
      <HeroBackground />

      <div
        ref={animRef}
        className={`home-content animate-on-scroll${loaded ? ' visible' : ' hidden'}`}
      >
        <h1 className="scale-fade-in">
          Make The Space Warm. Let <span style={{ fontSize: '1.5em' }}></span> The Team Shine.
        </h1>
        <div className="decor-line scale-fade-in" />
        <p className="scale-fade-in">
          We build a comfortable atmospheres with details and nourish every passion and dedication in a positive culture.
          <br />
          We believe that efficiency comes from trust and innovation comes from freedom. A team with warmth,
          <br />
          can create sustained value and move steadily forward in the direction of the industry benchmarks.
        </p>
      </div>
    </section>
  );
}
