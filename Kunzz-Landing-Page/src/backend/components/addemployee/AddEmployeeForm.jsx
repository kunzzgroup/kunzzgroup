import {
  ACCOUNT_TYPE_OPTIONS,
  BANK_OPTIONS,
  NATIONALITY_OPTIONS,
  POSITIONS_BY_ACCOUNT_TYPE,
  RACE_OPTIONS,
} from '../../config/generatecodeConstants.js';
import { formatFieldInput } from '../../utils/generatecodeCalculations.js';
import BranchMultiSelect from '../generatecode/BranchMultiSelect.jsx';
import AddEmployeePermissionSection from './AddEmployeePermissionSection.jsx';

function FormGroup({ id, label, required, error, errorMsg, className, children }) {
  return (
    <div className={`form-group ${error ? 'has-error' : ''} ${className || ''}`.trim()} id={id ? `group-${id}` : undefined}>
      <label htmlFor={id}>
        {label}
        {required ? <span className="required-mark">*</span> : null}
      </label>
      {children}
      {errorMsg ? <div className="error-msg">{errorMsg}</div> : null}
    </div>
  );
}

export default function AddEmployeeForm({
  form,
  fieldErrors,
  showPermWarning,
  onFieldChange,
  onBlurFormat,
  onAccountTypeChange,
}) {
  const positions = POSITIONS_BY_ACCOUNT_TYPE[form.account_type] || [];

  return (
    <form id="addUserForm" style={{ animation: 'fadeIn .3s ease' }}>
      <div className="form-col">
        <div className="form-section left-card">
          <div className="form-section-header" style={{ textTransform: 'uppercase' }}>
            个人资料 PERSONAL DETAILS
          </div>
          <div className="form-section-content">
            <div className="form-grid-3">
              <FormGroup
                id="add_username"
                label="英语姓名 English Name"
                required
                error={fieldErrors.username}
                errorMsg="请填写英文姓名，至少包含两个单词"
              >
                <input
                  id="add_username"
                  required
                  maxLength={50}
                  placeholder="E.G. JOHN DOE"
                  style={{ textTransform: 'uppercase' }}
                  value={form.username}
                  onChange={(e) => onFieldChange('username', e.target.value.toUpperCase())}
                  onBlur={() => onBlurFormat('username')}
                />
              </FormGroup>

              <FormGroup
                id="add_username_cn"
                label="中文姓名 Chinese Name"
                error={fieldErrors.username_cn}
                errorMsg="中文姓名至少需要两个汉字"
              >
                <input
                  id="add_username_cn"
                  maxLength={100}
                  placeholder="E.G. 刘德华"
                  value={form.username_cn}
                  onChange={(e) => onFieldChange('username_cn', e.target.value)}
                  onBlur={() => onBlurFormat('username_cn')}
                />
              </FormGroup>

              <FormGroup id="add_nickname" label="昵称 Nickname">
                <input
                  id="add_nickname"
                  maxLength={50}
                  placeholder="E.G. JACKIE"
                  value={form.nickname}
                  onChange={(e) => onFieldChange('nickname', e.target.value)}
                />
              </FormGroup>

              <FormGroup
                id="add_email"
                label="邮箱 Email"
                required
                error={fieldErrors.email}
                errorMsg="请填写有效的邮箱地址"
              >
                <input
                  id="add_email"
                  type="email"
                  required
                  maxLength={100}
                  placeholder="e.g. user@example.com"
                  value={form.email}
                  onChange={(e) => onFieldChange('email', e.target.value)}
                  onBlur={() => onBlurFormat('email')}
                />
              </FormGroup>

              <FormGroup id="add_ic_number" label="身份证号码">
                <input
                  id="add_ic_number"
                  maxLength={20}
                  value={form.ic_number}
                  onChange={(e) => onFieldChange('ic_number', e.target.value)}
                  onBlur={() => onBlurFormat('ic_number')}
                />
              </FormGroup>

              <FormGroup id="add_phone_number" label="联络号码">
                <input
                  id="add_phone_number"
                  maxLength={20}
                  value={form.phone_number}
                  onChange={(e) => onFieldChange('phone_number', e.target.value)}
                  onBlur={() => onBlurFormat('phone_number')}
                />
              </FormGroup>

              <FormGroup id="add_gender" label="性别">
                <select id="add_gender" value={form.gender} onChange={(e) => onFieldChange('gender', e.target.value)}>
                  <option value="">请选择</option>
                  <option value="male">男</option>
                  <option value="female">女</option>
                  <option value="other">其他</option>
                </select>
              </FormGroup>

              <FormGroup id="add_nationality" label="国籍">
                <select
                  id="add_nationality"
                  value={form.nationality}
                  onChange={(e) => onFieldChange('nationality', e.target.value)}
                >
                  <option value="">请选择国籍</option>
                  {NATIONALITY_OPTIONS.map((item) => (
                    <option key={item} value={item}>
                      {item}
                    </option>
                  ))}
                </select>
              </FormGroup>

              <FormGroup id="add_race" label="种族">
                <select id="add_race" value={form.race} onChange={(e) => onFieldChange('race', e.target.value)}>
                  <option value="">请选择种族</option>
                  {RACE_OPTIONS.map((item) => (
                    <option key={item} value={item}>
                      {item}
                    </option>
                  ))}
                </select>
              </FormGroup>
            </div>

            <div className="form-grid-1" style={{ marginTop: 14 }}>
              <FormGroup id="add_home_address" label="地址">
                <textarea
                  id="add_home_address"
                  rows={2}
                  maxLength={255}
                  style={{ resize: 'none' }}
                  value={form.home_address}
                  onChange={(e) => onFieldChange('home_address', e.target.value)}
                  onBlur={() => onBlurFormat('home_address')}
                />
              </FormGroup>
            </div>

            <div className="form-row-2col" style={{ marginTop: 14 }}>
              <FormGroup id="add_emergency_contact_name" label="紧急联系人">
                <input
                  id="add_emergency_contact_name"
                  maxLength={100}
                  value={form.emergency_contact_name}
                  onChange={(e) => onFieldChange('emergency_contact_name', e.target.value)}
                  onBlur={() => onBlurFormat('emergency_contact_name')}
                />
              </FormGroup>
              <FormGroup id="add_emergency_phone_number" label="紧急联系人号码">
                <input
                  id="add_emergency_phone_number"
                  maxLength={20}
                  value={form.emergency_phone_number}
                  onChange={(e) => onFieldChange('emergency_phone_number', e.target.value)}
                  onBlur={() => onBlurFormat('emergency_phone_number')}
                />
              </FormGroup>
            </div>
          </div>

          <div className="form-section-header-bank" style={{ textTransform: 'uppercase' }}>
            银行信息 BANK INFORMATION
          </div>
          <div className="form-section-content" style={{ flexShrink: 0, paddingTop: 15 }}>
            <div className="form-grid-3">
              <FormGroup id="add_bank_account_holder_en" label="银行账户持有人">
                <input
                  id="add_bank_account_holder_en"
                  maxLength={50}
                  value={form.bank_account_holder_en}
                  onChange={(e) => onFieldChange('bank_account_holder_en', e.target.value)}
                  onBlur={() => onBlurFormat('bank_account_holder_en')}
                />
              </FormGroup>
              <FormGroup id="add_bank_account" label="银行账号">
                <input
                  id="add_bank_account"
                  maxLength={30}
                  value={form.bank_account}
                  onChange={(e) => onFieldChange('bank_account', e.target.value)}
                  onBlur={() => onBlurFormat('bank_account')}
                />
              </FormGroup>
              <FormGroup id="add_bank_name" label="银行名称">
                <select id="add_bank_name" value={form.bank_name} onChange={(e) => onFieldChange('bank_name', e.target.value)}>
                  <option value="">请选择银行</option>
                  {BANK_OPTIONS.map((item) => (
                    <option key={item} value={item}>
                      {item}
                    </option>
                  ))}
                </select>
              </FormGroup>
            </div>
          </div>
        </div>
      </div>

      <div className="form-col">
        <div className="form-section">
          <div className="form-section-header" style={{ textTransform: 'uppercase' }}>
            账号设置 ACCOUNT SETTINGS
          </div>
          <div className="form-section-content">
            <div className="form-grid-2">
              <FormGroup
                id="add_account_type"
                label="账号类型 Account Type"
                required
                error={fieldErrors.account_type}
                errorMsg="请选择账号类型"
              >
                <select
                  id="add_account_type"
                  required
                  value={form.account_type}
                  onChange={(e) => onAccountTypeChange(e.target.value)}
                >
                  <option value="">请选择账号类型</option>
                  {ACCOUNT_TYPE_OPTIONS.map((option) => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
              </FormGroup>

              <FormGroup id="add_position" label="职位 Position">
                <select
                  id="add_position"
                  value={form.position}
                  disabled={!form.account_type}
                  onChange={(e) => onFieldChange('position', e.target.value)}
                >
                  <option value="">{form.account_type ? '请选择职位' : '请先选择账号类型'}</option>
                  {positions.map((position) => (
                    <option key={position} value={position}>
                      {position}
                    </option>
                  ))}
                </select>
              </FormGroup>

              <FormGroup id="add_branch" label="所属公司">
                <BranchMultiSelect
                  id="add-branch-select"
                  selected={form.branch}
                  onChange={(branch) => onFieldChange('branch', branch)}
                />
              </FormGroup>
            </div>
          </div>
        </div>

        <AddEmployeePermissionSection showWarning={showPermWarning} />
      </div>
    </form>
  );
}
