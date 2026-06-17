import { useEffect, useState } from 'react';
import { useAnimateOnScroll } from '../../hooks/useAnimateOnScroll.js';
import { mediaUrl } from '../../utils/media.js';

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
    <section class={`home${loaded ? ' gradient-loaded' : ''}`}>
      <video class="background-video" autoPlay muted loop playsInline preload="metadata">
        <source src={mediaUrl('home_background')} type="video/mp4" />
      </video>

      <div
        ref={animRef}
        class={`home-content animate-on-scroll${loaded ? ' visible' : ' hidden'}`}
      >
        <h1 className="scale-fade-in">
          Make The Space Warm. Let <span style={{ fontSize: '1.5em' }}></span> The Team Shine.
        </h1>
        <div className="decor-line scale-fade-in" />
        <p class="scale-fade-in">
          We build comfortable atmospheres with attention to detail, nurturing passion and focus within a positive culture.
          <br />
          We believe that efficiency comes from trust and innovation comes from freedom. A team with warmth,
          <br />
          can create sustained value and steadily forward in the direction of industry benchmarks. 
        </p>
      </div>
    </section>
  );
}
