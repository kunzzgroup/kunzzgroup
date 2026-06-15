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
          <h2>申请职位</h2>
          <label htmlFor="formPosition">职位名称：</label>
          <input type="text" id="formPosition" name="position" value={position} readOnly />

          <div className="job-form-row">
            <div className="job-half-width">
              <label htmlFor="chinese_name">中文姓名：</label>
              <input
                type="text"
                id="chinese_name"
                name="chinese_name"
                required
                pattern="[\u4e00-\u9fa5]{2,}"
                title="请输入中文姓名（至少两个汉字）"
              />
            </div>
            <div className="job-half-width">
              <label htmlFor="gender">性别：</label>
              <select id="gender" name="gender" required defaultValue="">
                <option value="">请选择</option>
                <option value="male">男</option>
                <option value="female">女</option>
                <option value="other">其他</option>
              </select>
            </div>
          </div>

          <label htmlFor="english_name">英文姓名：</label>
          <input
            type="text"
            id="english_name"
            name="english_name"
            required
            pattern="[A-Za-z ]{2,}"
            title="请输入英文姓名（只限英文字母）"
          />

          <label htmlFor="email">电子邮箱：</label>
          <input type="email" id="email" name="email" required />

          <label htmlFor="phone">电话号码：</label>
          <div className="job-phone-group">
            <select name="country_code" required defaultValue="+60">
              <option value="+60">马来西亚 (+60)</option>
              <option value="+65">新加坡 (+65)</option>
              <option value="+86">中国 (+86)</option>
              <option value="+852">香港 (+852)</option>
              <option value="+81">日本 (+81)</option>
            </select>
            <input
              type="tel"
              id="phone"
              name="phone"
              required
              pattern="\d{1,10}"
              maxLength={10}
              title="请输入最多10位数字的电话号码"
            />
          </div>

          <label htmlFor="resume">上传简历（PDF，≤3MB）：</label>
          <input type="file" name="resume" id="resume" accept=".pdf" required />

          <button type="submit" className="job-submit-btn">
            提交申请
          </button>
        </form>
      </div>
    </div>
  );
}
