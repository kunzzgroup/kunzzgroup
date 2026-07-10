import { useEffect, useMemo, useState } from 'react';

import { getApiUrl } from '../config.js';

const COMPANY_ORDER = ['KUNZZ HOLDINGS', 'TOKYO JAPANESE CUISINE', 'TOKYO IZAKAYA'];

const DEPT_ORDER_ZH = ['前台', '厨房', 'sushi bar'];
const DEPT_DISPLAY_ZH = { 前台: '前台', 厨房: '厨房', 'sushi bar': 'SUSHI BAR' };

export function useJobs(lang = 'zh') {
  const [companies, setCompanies] = useState({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let cancelled = false;

    fetch(`${getApiUrl('api/get_jobs_api.php')}?lang=${lang}`)
      .then((res) => {
        if (!res.ok) throw new Error(`Jobs API error: ${res.status}`);
        return res.json();
      })
      .then((data) => {
        if (cancelled) return;
        if (data.success) {
          setCompanies(data.companies && typeof data.companies === 'object' ? data.companies : {});
        } else {
          throw new Error(data.error || 'Failed to load jobs');
        }
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

  const jobsMap = useMemo(() => {
    const map = {};
    if (!companies || typeof companies !== 'object') return map;
    Object.values(companies).forEach((company) => {
      company.jobs?.forEach((job) => {
        map[job.id] = { ...job, company: company.name };
      });
    });
    return map;
  }, [companies]);

  const orderedCompanies = useMemo(() => {
    return COMPANY_ORDER.map((name) => ({
      name,
      jobs: companies[name]?.jobs || [],
    }));
  }, [companies]);

  return {
    companies: orderedCompanies,
    jobsMap,
    loading,
    error,
    deptOrder: DEPT_ORDER_ZH,
    deptDisplay: DEPT_DISPLAY_ZH,
  };
}
