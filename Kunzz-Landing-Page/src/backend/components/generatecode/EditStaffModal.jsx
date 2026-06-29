import { useEffect, useState } from 'react';

import {
  ACCOUNT_TYPE_OPTIONS,
  BANK_OPTIONS,
  NATIONALITY_OPTIONS,
  POSITIONS_BY_ACCOUNT_TYPE,
  RACE_OPTIONS,
} from '../../config/generatecodeConstants.js';
import {
  editFormToPayload,
  formatFieldInput,
  staffToEditForm,
  validateEditForm,
} from '../../utils/generatecodeCalculations.js';
import BranchMultiSelect from './BranchMultiSelect.jsx';

export default function EditStaffModal({ staff, open, saving, onClose, onSave, onError }) {
  const [form, setForm] = useState(null);

  useEffect(() => {
    if (open && staff) {
      setForm(staffToEditForm(staff));
    }
  }, [open, staff]);

  if (!open || !staff || !form) return null;

  const positions = POSITIONS_BY_ACCOUNT_TYPE[form.account_type] || [];

  const updateField = (field, value) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleBlurFormat = (field) => {
    setForm((prev) => ({ ...prev, [field]: formatFieldInput(field, prev[field]) }));
  };

  const handleAccountTypeChange = (value) => {
    setForm((prev) => ({
      ...prev,
      account_type: value,
      position: '',
    }));
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    const validationError = validateEditForm(form);
    if (validationError) {
      onError(validationError);
      return;
    }
    await onSave(editFormToPayload(form));
  };

  return (
    <div
      id="editUserModal"
      className="modal"
      style={{ display: 'block' }}
      onClick={(event) => {
        if (event.target === event.currentTarget) onClose();
      }}
    >
      <div className="modal-content">
        <div className="modal-header" style={{ color: '#f59e0b' }}>
          <i className="fas fa-user-edit" /> 编辑职员信息
        </div>
        <div className="modal-body" style={{ textAlign: 'left' }}>
          <form id="editUserForm" onSubmit={handleSubmit}>
            <div className="form-section">
              <div className="form-section-header">基本信息</div>
              <div className="form-section-content">
                <div className="form-row-2col">
                  <div className="form-group">
                    <label htmlFor="edit_username">英文姓名</label>
                    <input
                      id="edit_username"
                      required
                      maxLength={50}
                      value={form.username}
                      onChange={(e) => updateField('username', e.target.value)}
                      onBlur={() => handleBlurFormat('username')}
                    />
                  </div>
                  <div className="form-group">
                    <label htmlFor="edit_username_cn">中文姓名</label>
                    <input
                      id="edit_username_cn"
                      maxLength={100}
                      value={form.username_cn}
                      onChange={(e) => updateField('username_cn', e.target.value)}
                      onBlur={() => handleBlurFormat('username_cn')}
                    />
                  </div>
                </div>
                <div className="form-row-2col">
                  <div className="form-group">
                    <label htmlFor="edit_nickname">昵称</label>
                    <input
                      id="edit_nickname"
                      maxLength={50}
                      value={form.nickname}
                      onChange={(e) => updateField('nickname', e.target.value)}
                    />
                  </div>
                  <div className="form-group">
                    <label htmlFor="edit_email">邮箱</label>
                    <input
                      id="edit_email"
                      type="email"
                      required
                      maxLength={100}
                      value={form.email}
                      onChange={(e) => updateField('email', e.target.value)}
                      onBlur={() => handleBlurFormat('email')}
                    />
                  </div>
                </div>
              </div>
            </div>

            <div className="form-section">
              <div className="form-section-header">个人资料</div>
              <div className="form-section-content">
                <div className="form-row-3col">
                  <div className="form-group">
                    <label htmlFor="edit_ic_number">身份证号码</label>
                    <input
                      id="edit_ic_number"
                      maxLength={20}
                      value={form.ic_number}
                      onChange={(e) => updateField('ic_number', e.target.value)}
                      onBlur={() => handleBlurFormat('ic_number')}
                    />
                  </div>
                  <div className="form-group">
                    <label htmlFor="edit_phone_number">联络号码</label>
                    <input
                      id="edit_phone_number"
                      maxLength={20}
                      value={form.phone_number}
                      onChange={(e) => updateField('phone_number', e.target.value)}
                      onBlur={() => handleBlurFormat('phone_number')}
                    />
                  </div>
                  <div className="form-group">
                    <label htmlFor="edit_date_of_birth">出生日期</label>
                    <input
                      id="edit_date_of_birth"
                      type="date"
                      value={form.date_of_birth}
                      onChange={(e) => updateField('date_of_birth', e.target.value)}
                    />
                  </div>
                </div>
                <div className="form-row-3col">
                  <div className="form-group">
                    <label htmlFor="edit_gender">性别</label>
                    <select
                      id="edit_gender"
                      value={form.gender}
                      onChange={(e) => updateField('gender', e.target.value)}
                    >
                      <option value="">请选择</option>
                      <option value="male">男</option>
                      <option value="female">女</option>
                      <option value="other">其他</option>
                    </select>
                  </div>
                  <div className="form-group">
                    <label htmlFor="edit_nationality">国籍</label>
                    <select
                      id="edit_nationality"
                      value={form.nationality}
                      onChange={(e) => updateField('nationality', e.target.value)}
                    >
                      <option value="">请选择国籍</option>
                      {NATIONALITY_OPTIONS.map((item) => (
                        <option key={item} value={item}>
                          {item}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div className="form-group">
                    <label htmlFor="edit_race">种族</label>
                    <select id="edit_race" value={form.race} onChange={(e) => updateField('race', e.target.value)}>
                      <option value="">请选择种族</option>
                      {RACE_OPTIONS.map((item) => (
                        <option key={item} value={item}>
                          {item}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>
                <div className="form-row-1col">
                  <div className="form-group">
                    <label htmlFor="edit_home_address">住址</label>
                    <textarea
                      id="edit_home_address"
                      rows={2}
                      maxLength={255}
                      value={form.home_address}
                      onChange={(e) => updateField('home_address', e.target.value)}
                      onBlur={() => handleBlurFormat('home_address')}
                    />
                  </div>
                </div>
              </div>
            </div>

            <div className="form-section">
              <div className="form-section-header">银行信息</div>
              <div className="form-section-content">
                <div className="form-row-2col">
                  <div className="form-group">
                    <label htmlFor="edit_bank_account_holder_en">银行账户持有人</label>
                    <input
                      id="edit_bank_account_holder_en"
                      maxLength={50}
                      value={form.bank_account_holder_en}
                      onChange={(e) => updateField('bank_account_holder_en', e.target.value)}
                      onBlur={() => handleBlurFormat('bank_account_holder_en')}
                    />
                  </div>
                  <div className="form-group">
                    <label htmlFor="edit_bank_account">银行账号</label>
                    <input
                      id="edit_bank_account"
                      maxLength={30}
                      value={form.bank_account}
                      onChange={(e) => updateField('bank_account', e.target.value)}
                      onBlur={() => handleBlurFormat('bank_account')}
                    />
                  </div>
                </div>
                <div className="form-row-1col">
                  <div className="form-group">
                    <label htmlFor="edit_bank_name">银行名称</label>
                    <select
                      id="edit_bank_name"
                      value={form.bank_name}
                      onChange={(e) => updateField('bank_name', e.target.value)}
                    >
                      <option value="">请选择银行</option>
                      {BANK_OPTIONS.map((item) => (
                        <option key={item} value={item}>
                          {item}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div className="form-section">
              <div className="form-section-header">紧急联络人</div>
              <div className="form-section-content">
                <div className="form-row-2col">
                  <div className="form-group">
                    <label htmlFor="edit_emergency_contact_name">紧急联系人</label>
                    <input
                      id="edit_emergency_contact_name"
                      maxLength={100}
                      value={form.emergency_contact_name}
                      onChange={(e) => updateField('emergency_contact_name', e.target.value)}
                      onBlur={() => handleBlurFormat('emergency_contact_name')}
                    />
                  </div>
                  <div className="form-group">
                    <label htmlFor="edit_emergency_phone_number">紧急联系人电话</label>
                    <input
                      id="edit_emergency_phone_number"
                      maxLength={20}
                      value={form.emergency_phone_number}
                      onChange={(e) => updateField('emergency_phone_number', e.target.value)}
                      onBlur={() => handleBlurFormat('emergency_phone_number')}
                    />
                  </div>
                </div>
              </div>
            </div>

            <div className="form-section">
              <div className="form-section-header">账号设置</div>
              <div className="form-section-content">
                <div className="form-row-2col">
                  <div className="form-group">
                    <label>所属公司</label>
                    <BranchMultiSelect selected={form.branch} onChange={(branch) => updateField('branch', branch)} />
                  </div>
                </div>
                <div className="form-row-2col">
                  <div className="form-group">
                    <label htmlFor="edit_account_type">账号类型</label>
                    <select
                      id="edit_account_type"
                      required
                      value={form.account_type}
                      onChange={(e) => handleAccountTypeChange(e.target.value)}
                    >
                      <option value="">请选择账号类型</option>
                      {ACCOUNT_TYPE_OPTIONS.map((option) => (
                        <option key={option.value} value={option.value}>
                          {option.label}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div className="form-group">
                    <label htmlFor="edit_position">职位</label>
                    <select
                      id="edit_position"
                      value={form.position}
                      disabled={!form.account_type}
                      onChange={(e) => updateField('position', e.target.value)}
                    >
                      <option value="">{form.account_type ? '请选择职位' : '请先选择账号类型'}</option>
                      {positions.map((position) => (
                        <option key={position} value={position}>
                          {position}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div className="modal-buttons">
              <button type="submit" className="btn-action btn-save" disabled={saving}>
                <i className="fas fa-save" /> {saving ? '保存中...' : '保存修改'}
              </button>
              <button type="button" className="btn-action btn-cancel" disabled={saving} onClick={onClose}>
                <i className="fas fa-times" /> 取消
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
