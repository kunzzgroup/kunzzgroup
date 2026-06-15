import { FeedbackForm } from './JoinContactFeedback.jsx';

export default function JoinFeedbackMobile() {
  return (
    <div className="contact-form-section contact-form-section--feedback-only">
      <div className="contact-form-container contact-form-container--feedback-only">
        <div className="feedback-card joinus-feedback joinus-feedback--stacked">
          <h2 className="feedback-title">Kindly Provide Your Feedback</h2>
          <p className="feedback-subtitle">We look forward to hearing from you and will get back to you shortly.</p>
          <FeedbackForm formId="feedbackFormMobile" compact />
        </div>
      </div>
    </div>
  );
}
