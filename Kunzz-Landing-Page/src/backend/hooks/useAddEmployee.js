import { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

import { addStaffUser, fetchGenerateCodeConfig, refreshSession } from '../api/generatecodeApi.js';
import {
  addFormToPayload,
  createEmptyAddEmployeeForm,
  formatFieldInput,
  validateAddEmployeeForm,
} from '../utils/generatecodeCalculations.js';
import {
  clearPermissionTreeInit,
  extractPermissionsData,
  initPermissionTreeEvents,
  resetPermissionTree,
} from '../utils/permissionTreeDom.js';

export function useAddEmployee() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState(createEmptyAddEmployeeForm);
  const [fieldErrors, setFieldErrors] = useState({});
  const [showPermWarning, setShowPermWarning] = useState(false);
  const [toast, setToast] = useState(null);

  const showToast = useCallback((message, type = 'success') => {
    setToast({ message, type });
    window.clearTimeout(showToast._timer);
    showToast._timer = window.setTimeout(() => setToast(null), type === 'success' ? 3000 : 4500);
  }, []);

  useEffect(() => {
    let active = true;
    (async () => {
      try {
        await fetchGenerateCodeConfig();
      } catch (error) {
        if (active) showToast(error.message || '加载配置失败', 'error');
      } finally {
        if (active) setLoading(false);
      }
    })();
    return () => {
      active = false;
    };
  }, [showToast]);

  useEffect(() => {
    if (loading) return undefined;

    let container = null;
    const timer = window.setTimeout(() => {
      container = document.querySelector('.editUserPermLayout');
      if (container) initPermissionTreeEvents(container);
    }, 0);

    return () => {
      window.clearTimeout(timer);
      container = container || document.querySelector('.editUserPermLayout');
      if (container) {
        resetPermissionTree(container);
        clearPermissionTreeInit(container);
      }
    };
  }, [loading]);

  useEffect(() => {
    const interval = window.setInterval(async () => {
      try {
        const result = await refreshSession();
        if (!result.success && result.code === 'SESSION_EXPIRED') {
          showToast('会话已过期，请重新登录', 'error');
        }
      } catch {
        // ignore
      }
    }, 5 * 60 * 1000);
    return () => window.clearInterval(interval);
  }, [showToast]);

  const updateField = useCallback((field, value) => {
    setForm((prev) => ({ ...prev, [field]: value }));
    setFieldErrors((prev) => {
      if (!prev[field]) return prev;
      const next = { ...prev };
      delete next[field];
      return next;
    });
  }, []);

  const handleBlurFormat = useCallback((field) => {
    setForm((prev) => ({ ...prev, [field]: formatFieldInput(field, prev[field]) }));
  }, []);

  const handleAccountTypeChange = useCallback((value) => {
    setForm((prev) => ({ ...prev, account_type: value, position: '' }));
    setFieldErrors((prev) => {
      if (!prev.account_type) return prev;
      const next = { ...prev };
      delete next.account_type;
      return next;
    });
  }, []);

  const saveEmployee = useCallback(async () => {
    const validation = validateAddEmployeeForm(form);
    if (!validation.valid) {
      setFieldErrors(validation.fieldErrors);
      showToast(validation.message, 'error');
      return;
    }

    const permContainer = document.querySelector('.editUserPermLayout');
    const permissionPayload = extractPermissionsData(permContainer);
    const checkedAny = permContainer
      ? permContainer.querySelectorAll('.perm-l1-check:checked, .perm-l2-check:checked').length
      : 0;

    if (checkedAny === 0) {
      setShowPermWarning(true);
      showToast('请至少选择一项权限', 'error');
      permContainer?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    setShowPermWarning(false);
    setSaving(true);

    try {
      const result = await addStaffUser({
        ...addFormToPayload(form),
        permissions: permissionPayload.perms,
        page_permissions: permissionPayload.pagePermissions,
        submenu_permissions: permissionPayload.submenuPermissions,
        report_permissions: permissionPayload.reportPermissions,
        restaurant_permissions: permissionPayload.restaurantPermissions,
        brand_permissions: permissionPayload.brandPermissions,
      });

      if (result.success) {
        let message = `职员 "${result.data?.username || form.username}" 添加成功！`;
        if (result.data?.email_sent) {
          message += ` 登录信息已发送至 ${result.data.email}`;
        } else {
          message += ` 临时密码：${result.data?.default_password || ''}`;
        }
        showToast(message, 'success');
        window.setTimeout(() => navigate('/backend/generatecode-v2'), 1500);
      } else {
        showToast(result.message || '添加失败，请重试！', 'error');
      }
    } catch (error) {
      showToast(`网络错误：${error.message}`, 'error');
    } finally {
      setSaving(false);
    }
  }, [form, navigate, showToast]);

  return {
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
  };
}
