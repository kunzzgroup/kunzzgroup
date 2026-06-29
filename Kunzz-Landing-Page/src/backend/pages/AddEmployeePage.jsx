import { Link } from 'react-router-dom';

import AddEmployeeForm from '../components/addemployee/AddEmployeeForm.jsx';
import BackendLayout from '../components/layout/BackendLayout.jsx';
import { useAddEmployee } from '../hooks/useAddEmployee.js';

function AddEmployeeToast({ toast }) {
  if (!toast) return null;

  const icon = {
    success: 'fa-check-circle',
    error: 'fa-times-circle',
    warning: 'fa-exclamation-triangle',
  }[toast.type] || 'fa-check-circle';

  return (
    <div id="toast-container">
      <div className={`toast toast-${toast.type}`}>
        <i className={`fas ${icon}`} />
        <span>{toast.message}</span>
      </div>
    </div>
  );
}

export default function AddEmployeePage() {
  const {
    loading,
    saving,
    form,
    fieldErrors,
    showPermWarning,
    toast,
    updateField,
    handleBlurFormat,
    handleAccountTypeChange,
    saveEmployee,
  } = useAddEmployee();

  if (loading) {
    return (
      <BackendLayout
        stylesheet="generatecode.css"
        extraStylesheets={['add-employee.css']}
        bodyClassName="add-employee-body"
        className="container add-employee-container"
      >
        <p style={{ padding: 24 }}>正在加载...</p>
      </BackendLayout>
    );
  }

  return (
    <BackendLayout
      stylesheet="generatecode.css"
      extraStylesheets={['add-employee.css']}
      bodyClassName="add-employee-body"
      className="container add-employee-container"
    >
      <div className="add-employee-page">
        <div className="page-header-bar">
          <Link to="/backend/generatecode-v2" className="back-btn">
            <i className="fas fa-arrow-left" /> 返回列表
          </Link>
          <h1>
            <i className="fas fa-user-plus" /> 添加新职员
          </h1>
        </div>

        <div className="form-scroll-area">
          <AddEmployeeForm
            form={form}
            fieldErrors={fieldErrors}
            showPermWarning={showPermWarning}
            onFieldChange={updateField}
            onBlurFormat={handleBlurFormat}
            onAccountTypeChange={handleAccountTypeChange}
          />
        </div>

        <div className="page-action-bar">
          <Link to="/backend/generatecode-v2" className="btn-back-action">
            <i className="fas fa-times" /> 取消
          </Link>
          <button type="button" id="btn-save" className="btn-save" disabled={saving} onClick={saveEmployee}>
            {saving ? (
              <>
                <i className="fas fa-spinner fa-spin" /> 保存中...
              </>
            ) : (
              <>
                <i className="fas fa-save" /> 保存职员
              </>
            )}
          </button>
        </div>
      </div>

      <AddEmployeeToast toast={toast} />
    </BackendLayout>
  );
}
