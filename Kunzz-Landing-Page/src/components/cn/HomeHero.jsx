import { useEffect, useState } from 'react';
import { useAnimateOnScroll } from '../../hooks/useAnimateOnScroll.js';

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
      <video className="background-video" autoPlay muted loop playsInline preload="metadata">
        <source src="/media/home_background.webm" type="video/webm" />
      </video>

      <div
        ref={animRef}
        className={`home-content animate-on-scroll${loaded ? ' visible' : ' hidden'}`}
      >
        <h1 className="scale-fade-in">
          让空间温暖 <span style={{ fontSize: '1.5em' }}>.</span> 让团队闪光
        </h1>
        <div className="decor-line scale-fade-in" />
        <p className="scale-fade-in">
          我们用细节构建舒适的氛围，在积极的文化中滋养每一份热情与专注。
          <br />
          我们相信，高效源于信任，创新源于自由。一支有温度的团队，
          <br />
          才能创造持续的价值，向着行业标杆的方向，稳步前行。
        </p>
      </div>
    </section>
  );
}
