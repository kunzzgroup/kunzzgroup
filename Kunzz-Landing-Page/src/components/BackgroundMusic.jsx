import { useEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';
import { musicUrl } from '../utils/media.js';

export default function BackgroundMusic() {
  const location = useLocation();
  const audioRef = useRef(null);
  const isBackend = location.pathname.startsWith('/backend');

  useEffect(() => {
    if (isBackend) {
      return undefined;
    }

    const bgMusic = audioRef.current;
    if (!bgMusic) {
      return undefined;
    }

    bgMusic.volume = 0.3;

    const savedTime = localStorage.getItem('musicCurrentTime');
    const savedPlaying = localStorage.getItem('musicPlaying');
    const currentPage = window.location.pathname;

    if (savedTime) {
      bgMusic.currentTime = parseFloat(savedTime);
    }

    const tryPlay = () => {
      bgMusic.play()
        .then(() => {
          localStorage.setItem('musicPlaying', 'true');
          localStorage.setItem('musicPage', currentPage);
        })
        .catch(() => {});
    };

    if (savedPlaying === 'true') {
      setTimeout(tryPlay, 100);
    }

    const startEvents = ['click', 'keydown', 'touchstart'];
    const startPlay = () => {
      tryPlay();
      startEvents.forEach((event) => {
        document.removeEventListener(event, startPlay);
      });
    };

    startEvents.forEach((event) => {
      document.addEventListener(event, startPlay, { once: true });
    });

    const progressTimer = setInterval(() => {
      if (!bgMusic.paused && bgMusic.currentTime > 0) {
        localStorage.setItem('musicCurrentTime', bgMusic.currentTime.toString());
        localStorage.setItem('musicPlaying', 'true');
        localStorage.setItem('musicPage', currentPage);
      }
    }, 1000);

    const handleBeforeUnload = () => {
      localStorage.setItem('musicCurrentTime', bgMusic.currentTime.toString());
      localStorage.setItem('musicPlaying', bgMusic.paused ? 'false' : 'true');
      localStorage.setItem('musicPage', currentPage);
    };

    const handleVisibilityChange = () => {
      if (document.visibilityState === 'visible') {
        const shouldPlay = localStorage.getItem('musicPlaying') === 'true';
        if (shouldPlay && bgMusic.paused) {
          tryPlay();
        }
      }
    };

    window.addEventListener('beforeunload', handleBeforeUnload);
    document.addEventListener('visibilitychange', handleVisibilityChange);

    return () => {
      clearInterval(progressTimer);
      window.removeEventListener('beforeunload', handleBeforeUnload);
      document.removeEventListener('visibilitychange', handleVisibilityChange);
      startEvents.forEach((event) => {
        document.removeEventListener(event, startPlay);
      });
    };
  }, [isBackend, location.pathname]);

  if (isBackend) {
    return null;
  }

  return (
    <audio
      id="bgMusic"
      ref={audioRef}
      loop
      preload="none"
      src={musicUrl()}
    />
  );
}
