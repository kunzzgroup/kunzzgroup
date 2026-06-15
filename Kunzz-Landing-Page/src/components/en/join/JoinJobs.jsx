import { useJoinSlideAnimation } from '../../../hooks/useJoinSlideAnimation.js';
import { useJobs } from '../../../hooks/useJobs.js';

function TokyoJobs({ jobs, deptOrder, deptDisplay, onJobClick }) {
  const departmentJobs = {};
  jobs.forEach((job) => {
    const dept = job.department || '其他';
    if (!departmentJobs[dept]) departmentJobs[dept] = [];
    departmentJobs[dept].push(job);
  });

  return (
    <>
      {deptOrder.map((dept) => {
        const list = departmentJobs[dept];
        if (!list?.length) return null;
        const jobCount = list.length;
        const singleJobClass = jobCount === 1 ? ' single-job' : '';

        return (
          <div className="department-section" key={dept}>
            <div className="department-title">{deptDisplay[dept] || dept}</div>
            <div className={`department-jobs${singleJobClass}`}>
              {list.map((job, index) => {
                const isLastOdd =
                  jobCount > 2 && jobCount % 2 === 1 && index === jobCount - 1
                    ? ' last-odd-job'
                    : '';
                return (
                  <div
                    key={job.id}
                    className={`job-item${isLastOdd}`}
                    data-job-id={job.id}
                    onClick={() => onJobClick(job.id)}
                    onKeyDown={(e) => e.key === 'Enter' && onJobClick(job.id)}
                    role="button"
                    tabIndex={0}
                  >
                    <div className="job-item-title">{job.title}</div>
                  </div>
                );
              })}
            </div>
          </div>
        );
      })}
    </>
  );
}

export default function JoinJobs({ onJobClick }) {
  const tableRef = useJoinSlideAnimation('job-table-container', 'job-table-loaded');
  const { companies, loading, error, deptOrder, deptDisplay } = useJobs('zh');

  return (
    <div className="job-section">
      <div ref={tableRef} className="job-table-container">
        <h2 className="job-table-title">Career Opportunities</h2>
      </div>

      <div className="jobs-wrapper">
        <div className="jobs-container">
          {loading ? <div className="no-jobs">Loading…</div> : null}
          {error ? <div className="no-jobs">职位数据加载失败，请确认 XAMPP 与数据库已启动</div> : null}

          {!loading &&
            !error &&
            companies.map((company) => (
              <div className="company-job-container" key={company.name}>
                <h3 className="company-title">{company.name}</h3>
                <div className="company-jobs-list">
                  {company.jobs.length === 0 ? (
                    <div className="no-jobs-company">No Position Available</div>
                  ) : company.name === 'TOKYO JAPANESE CUISINE' ||
                    company.name === 'TOKYO IZAKAYA' ? (
                    <TokyoJobs
                      jobs={company.jobs}
                      deptOrder={deptOrder}
                      deptDisplay={deptDisplay}
                      onJobClick={onJobClick}
                    />
                  ) : (
                    company.jobs.map((job) => (
                      <div
                        key={job.id}
                        className="job-item"
                        data-job-id={job.id}
                        onClick={() => onJobClick(job.id)}
                        onKeyDown={(e) => e.key === 'Enter' && onJobClick(job.id)}
                        role="button"
                        tabIndex={0}
                      >
                        <div className="job-item-title">{job.title}</div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            ))}
        </div>
      </div>
    </div>
  );
}
