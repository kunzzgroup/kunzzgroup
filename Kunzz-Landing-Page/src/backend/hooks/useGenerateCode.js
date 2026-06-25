import { useCallback, useEffect, useMemo, useState } from 'react';

import {
  deleteStaffRecord,
  fetchGenerateCodeConfig,
  fetchStaffList,
  refreshSession,
  saveUserPermissions,
  updateStaffRecord,
} from '../api/generatecodeApi.js';
import { downloadInterviewForm } from '../components/generatecode/GenerateCodeToolbar.jsx';
import { filterStaffList } from '../utils/generatecodeCalculations.js';

export function useGenerateCode() {
  const [config, setConfig] = useState(null);
  const [staffList, setStaffList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [branchL1, setBranchL1] = useState('all');
  const [branchL2, setBranchL2] = useState('all');
  const [toast, setToast] = useState(null);

  const [editStaff, setEditStaff] = useState(null);
  const [deleteStaff, setDeleteStaff] = useState(null);
  const [permStaff, setPermStaff] = useState(null);
  const [downloadOpen, setDownloadOpen] = useState(false);
  const [downloadCompany, setDownloadCompany] = useState('');

  const showToast = useCallback((message, type = 'success') => {
    setToast({ message, type });
    window.clearTimeout(showToast._timer);
    showToast._timer = window.setTimeout(() => setToast(null), 4000);
  }, []);

  const loadStaff = useCallback(async () => {
    setLoading(true);
    try {
      const data = await fetchStaffList();
      setStaffList(data);
    } catch (error) {
      if (error.code === 'SESSION_EXPIRED') {
        showToast('会话已过期，请重新登录', 'error');
      } else {
        showToast(error.message || '加载数据失败', 'error');
      }
      setStaffList([]);
    } finally {
      setLoading(false);
    }
  }, [showToast]);

  useEffect(() => {
    let active = true;
    (async () => {
      try {
        const nextConfig = await fetchGenerateCodeConfig();
        if (!active) return;
        setConfig(nextConfig);
        await loadStaff();
      } catch (error) {
        if (active) showToast(error.message || '加载配置失败', 'error');
      } finally {
        if (active) setLoading(false);
      }
    })();
    return () => {
      active = false;
    };
  }, [loadStaff, showToast]);

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

  const filteredStaff = useMemo(
    () => filterStaffList(staffList, { searchTerm, branchL1, branchL2 }),
    [staffList, searchTerm, branchL1, branchL2],
  );

  const changeBranchL1 = useCallback((value) => {
    setBranchL1(value);
    setBranchL2('all');
  }, []);

  const saveEdit = useCallback(
    async (payload) => {
      setSaving(true);
      try {
        const result = await updateStaffRecord(payload);
        if (result.success) {
          showToast('修改成功！', 'success');
          setEditStaff(null);
          await loadStaff();
        } else {
          showToast(result.message || '修改失败！', 'error');
        }
      } catch (error) {
        showToast(error.message || '网络错误，请检查连接！', 'error');
      } finally {
        setSaving(false);
      }
    },
    [loadStaff, showToast],
  );

  const confirmDelete = useCallback(async () => {
    if (!deleteStaff) return;
    setSaving(true);
    try {
      const result = await deleteStaffRecord(deleteStaff.id);
      if (result.success) {
        showToast('删除成功！', 'success');
        setDeleteStaff(null);
        await loadStaff();
      } else {
        showToast(result.message || '删除失败！', 'error');
      }
    } catch (error) {
      showToast(error.message || '网络错误，请检查连接！', 'error');
    } finally {
      setSaving(false);
    }
  }, [deleteStaff, loadStaff, showToast]);

  const confirmDownload = useCallback(() => {
    if (!downloadCompany) {
      showToast('请选择一个公司/店铺', 'warning');
      return;
    }
    const label = downloadInterviewForm(downloadCompany);
    if (label) {
      showToast(`正在下载 ${label} 的申请表...`, 'success');
      setDownloadOpen(false);
      setDownloadCompany('');
    } else {
      showToast('下载失败，文件不存在', 'error');
    }
  }, [downloadCompany, showToast]);

  const savePermissions = useCallback(
    async (payload) => {
      setSaving(true);
      try {
        const result = await saveUserPermissions(payload);
        if (result.success) {
          showToast('权限已保存', 'success');
          setPermStaff(null);
        } else {
          showToast(result.message || '保存失败', 'error');
        }
      } catch (error) {
        showToast(error.message || '网络错误，稍后重试', 'error');
      } finally {
        setSaving(false);
      }
    },
    [showToast],
  );

  return {
    config,
    loading,
    saving,
    toast,
    searchTerm,
    setSearchTerm,
    branchL1,
    branchL2,
    changeBranchL1,
    setBranchL2,
    filteredStaff,
    editStaff,
    setEditStaff,
    deleteStaff,
    setDeleteStaff,
    permStaff,
    setPermStaff,
    downloadOpen,
    setDownloadOpen,
    downloadCompany,
    setDownloadCompany,
    showToast,
    saveEdit,
    confirmDelete,
    confirmDownload,
    savePermissions,
  };
}
