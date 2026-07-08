import { useEffect, useMemo, useState } from 'react';
import { getApiUrl } from '../config.js';
import { resolveAssetUrl } from '../utils/media.js';

export function useTimeline(lang = 'zh') {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let cancelled = false;

    fetch(`${getApiUrl('api/timeline_api.php')}?lang=${lang}`)
      .then((res) => {
        if (!res.ok) throw new Error(`Timeline API error: ${res.status}`);
        return res.json();
      })
      .then((data) => {
        if (cancelled) return;
        const rawItems = Array.isArray(data.items) ? data.items : [];
        setItems(
          rawItems.map((item) => ({
            ...item,
            image_url: resolveAssetUrl(item.image_url || item.image),
          })),
        );
        setLoading(false);
      })
      .catch((err) => {
        if (cancelled) return;
        setError(err.message);
        setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [lang]);

  const years = useMemo(() => items.map((item) => String(item.year ?? '')), [items]);

  const yearGroups = useMemo(() => {
    const groups = {};
    items.forEach((item, index) => {
      const year = String(item.year ?? '');
      if (!groups[year]) groups[year] = [];
      groups[year].push({ index, month: Number(item.month) || 0 });
    });
    return groups;
  }, [items]);

  const firstIndexByYear = useMemo(() => {
    const map = {};
    items.forEach((item, index) => {
      const year = String(item.year ?? '');
      if (map[year] === undefined) map[year] = index;
    });
    return map;
  }, [items]);

  return { items, years, yearGroups, firstIndexByYear, loading, error };
}
