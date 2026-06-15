import { useJoinSlideAnimation } from '../../../hooks/useJoinSlideAnimation.js';

const benefits = [
  { icon: '/images/带薪假期.webp', label: 'Annual Leave' },
  { icon: '/images/旅游奖励.webp', label: 'Travel Incentive' },
  { icon: '/images/汽车奖励.webp', label: 'Car Allowance' },
  { icon: '/images/房子奖励.webp', label: 'Housing Allowance' },
  { icon: '/images/年度绩效奖励.webp', label: 'Annual Bonus' },
  { icon: '/images/专业培训与学习机会.webp', label: 'Training & Learning' },
];

export default function JoinHeroBenefits() {
  const bannerRef = useJoinSlideAnimation('joinus-banner', 'joinus-loaded');
  const benefitsRef = useJoinSlideAnimation('benefits-wrapper', 'benefits-loaded');

  return (
    <section className="joinus-section">
      <div ref={bannerRef} className="joinus-banner">
        <img
          src="/images/加入我们bg2.jpg"
          alt=""
          className="background-image"
          style={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            position: 'absolute',
            top: 0,
            left: 0,
            zIndex: -1,
          }}
        />
        <div className="joinus-content">
          <h1>Join Us</h1>
          <p>Here, your effort shapes more than result - you help build the brand and grow alongside it. </p>
        </div>
      </div>

      <div ref={benefitsRef} className="benefits-wrapper" id="benefits">
        <h2>Benefits</h2>
        <div className="benefits-grid">
          {benefits.map((item) => (
            <div className="benefit-item" key={item.label}>
              <img src={item.icon} alt={item.label} />
              <p>{item.label}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
