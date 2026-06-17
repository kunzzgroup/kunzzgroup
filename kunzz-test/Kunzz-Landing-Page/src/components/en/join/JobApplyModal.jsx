export default function JobApplyModal({ position, onClose }) {
  if (!position) return null;

  return (
    <div
      id="formModal"
      className="modal"
      style={{ display: 'flex' }}
      onClick={(e) => e.target.id === 'formModal' && onClose()}
      onKeyDown={() => {}}
      role="presentation"
    >
      <div className="job-modal-content">
        <button type="button" className="close-btn" onClick={onClose}>
          &times;
        </button>
        <form
          className="job-form"
          id="jobApplicationForm"
          action="https://api.web3forms.com/submit"
          method="POST"
          encType="multipart/form-data"
        >
          <input type="hidden" name="access_key" value="a18bc4c6-2f16-4861-8d10-a3de747cab50" />
          <input
            type="hidden"
            name="redirect"
            value="https://kunzzgroup.com/frontend/success.html"
          />
          <h2>Apply</h2>
          <label htmlFor="formPosition">Position Title:</label>
          <input type="text" id="formPosition" name="position" value={position} readOnly />

          <div className="job-form-row">
            <div className="job-half-width">
              <label htmlFor="chinese_name">Chinese Name:</label>
              <input
                type="text"
                id="chinese_name"
                name="chinese_name"
                required
                pattern="[\u4e00-\u9fa5]{2,}"
                title="Please Enter a Valid Chinese Name"
              />
            </div>
            <div className="job-half-width">
              <label htmlFor="gender">Gender:</label>
              <select id="gender" name="gender" required defaultValue="">
                <option value="">Please Select</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Others</option>
              </select>
            </div>
          </div>

          <label htmlFor="english_name">English Name:</label>
          <input
            type="text"
            id="english_name"
            name="english_name"
            required
            pattern="[A-Za-z ]{2,}"
            title="Please Enter a Valid English Name"
          />

          <label htmlFor="email">Email</label>
          <input type="email" id="email" name="email" required />

          <label htmlFor="phone">Phone Number</label>
          <div className="job-phone-group">
            <select name="country_code" required defaultValue="+60">
              <option value="+60">Malaysia (+60)</option>
              <option value="+65">Singapore (+65)</option>
              <option value="+86">China (+86)</option>
              <option value="+852">Hong Kong (+852)</option>
              <option value="+81">Japan (+81)</option>
            </select>
            <input
              type="tel"
              id="phone"
              name="phone"
              required
              pattern="\d{1,10}"
              maxLength={10}
              title="Please Enter a Valid Telephone Number"
            />
          </div>

          <label htmlFor="resume">Upload Resume (PDF, ≤3MB)：</label>
          <input type="file" name="resume" id="resume" accept=".pdf" required />

          <button type="submit" className="job-submit-btn">
            Submit
          </button>
        </form>
      </div>
    </div>
  );
}
